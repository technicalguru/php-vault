<?php

namespace TgVault\Hashicorp;

require_once(__DIR__.'/../commons.php');

use TgVault\Vault;
use TgVault\BaseVault;
use TgVault\Secret;
use TgVault\VaultException;

/**
  * A vault representing an existing (!) Hashicorp Vault in the background.
  * You need to setup your Hashicorp Vault backend yourself, including all
  * required configuration. This class HashicorpVault acts as a client to
  * your real vault using AppRole token authorization. It takes the role ID
  * and the secret ID to request token which can be used to retrieve the secrets.
  */
class HashicorpVault extends BaseVault implements Vault {

	protected $isTls;
	protected $config;
	protected $lastResult;
	private   $loggedToken;
	private   $cache;
	private   $token;
	private   $secrets;

	/**
	  * Constructor.
	  * @param mixed  $config - the configuration, see Vaul\Hashicorp\Config for details.
	  * @param object $logger - the logger, e.g. a TgVault\Logger or a Psr\Log\LoggerInterface.
	  */
	public function __construct($config, $logger = NULL) {
		parent::__construct($logger);
		if ($config == NULL) throw new VaultException('Configuration must be set', VAULT_ERR_CONFIG_EMPTY);
		$this->config      = new Config($config);
		$this->isTls       = substr($this->config->uri, 0, 5) == 'https';
		$this->cache       = new Cache($this->config->cacheFile, $logger);
		$this->loggedToken = FALSE;
	}

	/**
	  * Get rid of the current token. This can be necessary when the policies changed.
	  */
	public function removeToken() {
		$this->cache->delete('token');
		$this->token = NULL;
	}

	/**
	  * Returns the secret at the given path.
	  * @param string $path - an arbitrary path that uniquely identifies a secret in the vault.
	  * @return Secret
	  * @throws VaultException when the secret cannot be found or retrieved.
	  */
	public function getSecret($path) {
		if (!isset($this->secrets[$path])) {
			$this->getToken();
			$rc = $this->GET($path);
			if (($rc->error == 0) && ($rc->http_code == 200) && is_object($rc->data->data)) {
				// It's unclear why some vaults do answer with one level less (without metadata)
				if (isset($rc->data->data->data)) {
					$this->secrets[$path] = new Secret($rc->data->data);
				} else {
					$this->secrets[$path] = new Secret($rc->data);
				}
			} else {
				$this->secrets[$path] = $rc;
			}
		}

		if (get_class($this->secrets[$path]) != 'TgVault\\Secret') {
			$ex = new VaultException('Secret not available', VAULT_ERR_SECRET);
			$ex->setDetails($this->secrets[$path]);
			throw $ex;
		}
		return $this->secrets[$path];
	}

	/**
	  * Set the logger and log all information via this object.
	  * @param object $logger - the logging object, either TgVault\Logger or Psr\Log\LoggerInterface
	  */
	public function setLogger($logger) {
		parent::setLogger($logger);
		$this->cache->setLogger($logger);
	}

	public function getTokenStatus($asText = true) {
		if ($this->token == NULL) {
			return $asText ? 'no token available' : VAULT_ERR_NO_TOKEN;
		}
		if ($this->token->isExpired()) {
			return $asText ? 'token expired at '.$this->token->expiryString.' (current time is '.date(DATE_ATOM).')' : VAULT_ERR_TOKEN_EXPIRED;
		}
		if ($this->shallRenewToken()) {
			return $asText ? 'token valid but needs renewal (expires at '.$this->token->expiryString.', current time is '.date(DATE_ATOM).')' : VAULT_ERR_RENEWAL;
		}
		return $asText ? 'token valid (expires at '.$this->token->expiryString.', current time is '.date(DATE_ATOM).')' : VAULT_OK;
	}

	public function getSecretError($path, $asText = true) {
		$response = $this->secrets[$path];
		if ($response instanceof Secret) {
			return $asText ? 'Secret successfully retrieved' : VAULT_SECRET_OK;
		}
		if ($response->error > 0) {
			return $asText ? $response->errorMessage : VAULT_ERR_CURL_BASE+$response->error;
		}
		if (is_object($response->data) && is_array($response->data->errors)) {
			return $asText ? implode(' / ', $response->data->errors) : VAULT_ERR_HTTP_BASE+$response->http_code;
		}
		return $asText ? 'Unknown internal error' : VAULT_ERR_SECRET_INTERNAL;
	}

	public function dieOn($path) {
		// Error log message
		$status1 = $this->getTokenStatus();
		$this->error('Vault Token Status: '.$status1);
		$status2 = $this->getSecretError($path);
		$this->error('Database Secret Status: '.$status2);

		// User message
		$status1 = $this->getTokenStatus(false);
		$status2 = $this->getSecretError($path, false);
		$renewal = false;
		switch ($status1) {
		case VAULT_OK:
			// It's a secret problem
			break;
		case VAULT_ERR_RENEWAL:
			// Token actually ok but renewal required / So it might be a secret issue
			$renewal = true;
			break;
		case VAULT_ERR_TOKEN_EXPIRED:
			exit('No valid access token');
			break;
		case VAULT_ERR_NO_TOKEN:
			exit('No access token');
			break;
		}

		switch ($status2) {
		case VAULT_SECRET_OK:
			exit($renewal ? 'Token renewal warning' : 'OK');
			break;
		case VAULT_ERR_SECRET_INTERNAL:
			exit($renewal ? 'Internal error on renewable token' : 'Internal error on secret');
			break;
		default:
			if ($status2 >= VAULT_ERR_CURL_BASE) {
				exit($renewal ? 'Cannot reach vault with renewable token: '.$status2 : 'Cannot reach vault: '.$status2);
			}
			exit($renewal ? 'Vault declines renewable token: '.$status2 : 'Vault declines: '.$status2);
		}
		exit($renewal ? 'Token renewal warning' : 'OK');
	}

	/**
	  * Returns the last result from a vault call.
	  */
	public function getLastResult() {
		return $this->lastResult;
	}

	/**
	  * Returns a token either from object cache, file cache or
	  * triggers a token request.
	  */
	protected function getToken() {
		if ($this->token == NULL) {
			$this->setTokenFromCache();
		}
		if ($this->token != NULL) {
			if ($this->isTokenExpired()) {
				$this->removeToken();
			} else if ($this->shallRenewToken()) {
				$this->renewToken();
			}
		}

		if ($this->token == NULL) {
			$this->requestNewToken();
		}

		if (($this->token != NULL) && !$this->loggedToken) {
			$this->info('Using token: '.$this->token->getInfo());
			$this->loggedToken = TRUE;
		}

		return $this->token;
	}

	protected function isTokenExpired() {
		// Token itself must exist and not be expired
		if (($this->token != NULL) && !$this->token->isExpired()) {
			// Get expiry time
			$expiry = $this->getTokenExpiryTime();
			if (time() > $expiry) return true;
			return false;
		}
		return true;
	}

	protected function shallRenewToken() {
		// Token must exist and not be expired
		if (($this->token != NULL) && !$this->token->isExpired()) {
			// Token renewable and renewal permitted
			if ($this->token->renewable && $this->config->renewTokens) {
				// Get expiry time
				$expiry = $this->getTokenExpiryTime();
				if ($expiry > 0) {
					$renewalTime = $expiry - 300;	// Default renewal
					$renewalPeriod = $this->config->renewalPeriod;
					if ($renewalPeriod > 10) $renewalTime = $expiry - $renewalPeriod;
					// So, is it time?
					if (time() > $renewalTime) return true;
				}

			}
		}
		return false;
	}

	/**
	  * Compute the current token's expiry time using the token and our config.
	  * @return int - the Unix epoch expiry time, -1 if token is NULL
	  */
	protected function getTokenExpiryTime() {
		$rc = -1;
		if ($this->token != NULL) {
			$rc = $this->token->expiryTime;
			if ($this->config->maxTtl > 0) {
				$expiry = $this->token->creationTime + $this->config->maxTtl;
				if ($expiry < $rc) $rc = $expiry;
			}
		}
		return $rc;
	}

	/**
	  * Load the token from the file cache.
	  */
	protected function setTokenFromCache() {
		$token = $this->cache->get('token');
		if ($token != NULL) {
			$this->token = new Token($token);
		}
	}

	/**
	  * Request a token from Vault
	  */
	protected function requestNewToken() {
		$data            = new \stdClass;
		$data->role_id   = $this->config->roleId;
		$data->secret_id = $this->config->secretId;
		$data->renewable = true;
		$rc = $this->POST('/auth/approle/login', $data);
		if (($rc->error == 0) && is_object($rc->data)) {
			if (isset($rc->data->auth)) {
				$this->token = new Token($rc->data->auth);
				$this->cache->set('token', $this->token);
				$this->info('Token replaced');
			}
		}
	}

	/**
	  * Renew the token
	  */
	protected function renewToken() {
		if ($this->token != NULL) {
			if (!$this->token->isExpired()) {
				$data         = new \stdClass;
				$data->token  = $this->token->client_token;
				$rc = $this->POST('auth/token/renew', $data);
				if (($rc->error == 0) && is_object($rc->data)) {
					if (isset($rc->data->auth)) {
						$this->token = new Token($rc->data->auth);
						$this->cache->set('token', $this->token);
						$this->info('Token renewed');
					}
				}
			} else {
				$this->removeToken();
			}
		}
	}

	/**
	  * Performs a GET request on the given path
	  * @param string $path - the relative path at Vault URI to request
	  * @return \stdClass see #request($curl, $path) method.
	  */
	protected function GET($path) {
		$curl = curl_init();
		return $this->request($curl, $path);
	}

	/**
	  * Performs a POST request on the given path
	  * @param string $path - the relative path at Vault URI to request
	  * @param object $data - the data to be posted (will be json-encoded)
	  * @return mixed see #request($curl, $path) method.
	  */
	protected function POST($path, $data) {
		$body = json_encode($data);
		$headers = array(
			'Content-Length: '.strlen($body),
			'Content-Type: application/json',
		);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_POST,       true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
		return $this->request($curl, $path, $headers);
	}

	/**
	  * Performs the actual curl request.
	  * @param resource $curl - cURL handle as returned from curl_init()
	  * @param string   $path - the relative path at Vault URI to request
	  * @return \stdClass see documentation below
	  */
	protected function request($curl, $path, $additionalHeaders = array()) {
		/**********************************
		 Return is an object of:
		{ 
			"request_id":         string,  // request ID given / not needed
			"lease_id":           string,  // lease created for this request / not needed
			"renewable":          bool,    // lease for current request is renewable / not needed
			"lease_duration":     int,     // duration of least for current request / not needed
			"data":               object,  // specific to request
			"wrap_info":          object,  // unknown / not needed
			"warnings":           array,   // unknown / string messages
			"errors":             array,   // unknown / string messages
			"auth": {             object   // authentication information
				"client_token":   string,  // token that was assigned
				"accessor":       string,  // accessor token / not needed
				"policies":       array,   // policies / info only
				"token_policies": array,   // token policies / info only
				"metadata":       object,  // authorization meta data, such as "role_name"
				"lease_duration": int,     // lease duration in seconds (remaining)
				"renewable":      bool,    // is token renewable
				"entity_id":      string,  // entity ID / not needed
				"token_type":     string,  // type of token / not needed
				"orphan":         bool,    // is orphaned token / not needed
			}
		}
		***********************************/
		$additionalHeaders[] = 'X-Vault-Request: true';
		if (($this->token != NULL) && isset($this->token->client_token)) {
			$additionalHeaders[] = 'X-Vault-Token: '.$this->token->client_token;
		}

		// Fix path issues here
		if ((substr($this->config->uri, -1) != '/') && (substr($path, 0, 1) != '/')) {
			$path = '/'.$path;
		} else if ((substr($this->config->uri, -1) == '/') && (substr($path, 0, 1) == '/')) {
			$path = substr($path, 1);
		}
		curl_setopt($curl, CURLOPT_URL,            $this->config->uri.$path);
		curl_setopt($curl, CURLOPT_TIMEOUT,        $this->config->timeout);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERAGENT,      'HashicorpVaultClient/1.0');
		if (count($additionalHeaders) > 0) {
			curl_setopt($curl, CURLOPT_HTTPHEADER, $additionalHeaders);
		}

		// We might need this: CURLOPT_PORT
		if ($this->isTls) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,   $this->config->verifyCertificate);
			curl_setopt($curl, CURLOPT_SSL_VERIFYSTATUS, $this->config->verifyCertificate);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,   $this->config->verifyCertificate);
		}
		if ($this->config->debug) {
			curl_setopt($curl, CURLINFO_HEADER_OUT, true);
		}

		$rc     = $this->createCurlResult($curl);
		curl_close($curl);
		return $rc;
	}

	protected function createCurlResult($curl) {
		$data = curl_exec($curl);

		$rc = new \stdClass;
		$rc->url             = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
		$rc->error           = curl_errno($curl);
		$rc->errorMessage    = curl_error($curl);
		if (!$rc->error) {
			$rc->http_code   = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
			$rc->contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
			if ($rc->contentType == 'application/json') {
				$rc->data      = json_decode($data);
				if (isset($rc->data->errors)) {
					$this->error('Error while calling '.$rc->url.': ', $rc->data->errors);
				}
			} else {
				$rc->data      = $data;
			}
		} else {
			$this->error('Error while calling '.$rc->url.': ', $rc->errorMessage);
		}
		if ($this->config->debug) {
			$rc->debug          = new \stdClass;
			$rc->debug->headers = curl_getinfo($curl, CURLINFO_HEADER_OUT);
		}
		$this->lastResult = $rc;
		return $rc;
	}

}

