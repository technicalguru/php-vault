<?php

namespace TgVault\Hashicorp;

require_once(__DIR__.'/../commons.php');

use TgVault\VaultException;

/**
  * A configuration for the Hashicorp Vault.
  */
class Config {

	/** The complete URI to the vault, e.g. https://127.0.0.1:8200/v1 */
	public $uri;
	/** the application role ID in vault. */
	public $roleId;
	/** the client secret in vault */
	public $secretId;
	/** The HTTP request timeout in seconds to be used (5) */
	public $timeout;
	/** Verify the server SSL certificate? (false) */
	public $verifyCertificate;
	/** Collect debug information from HTTP calls (false) */
	public $debug;
	/** Where is the cache file located to cache tokens (/tmp/valut_client.cache) */
	public $cacheFile;
	/** Shall tokens be renewed? (true) */
	public $renewTokens;
	/** What time in seconds before expiry shall a token be renewed? (300) */
	public $renewalPeriod;
	/** What shall be the maximum lifetime of a token? (0 - unlimited) */
	public $maxTtl;

	/**
	  * Constructs the config object.
	  * @param data - the configuration data.
	  */
	public function __construct($data) {
		if ($data == NULL) throw new VaultException('Configuration must not be empty', VAULT_ERR_CONFIG_EMPTY);

		$this->timeout           = 5;
		$this->verifyCertificate = false;
		$this->debug             = false;
		$this->cacheFile         = '/tmp/vault_client.cache';
		$this->renewTokens       = true;
		$this->renewalPeriod     = 300;
		$this->maxTtl            = 0;

		if (is_object($data)) $data = get_object_vars($data);
		if (is_array($data)) {
			foreach ($data AS $key => $value) {
				$this->$key = $value;
			}
		}

		// Check for any errors
		$this->check('uri',      'Vault URI not set');
		$this->check('roleId',   'Vault AppRole ID not set');
		$this->check('secretId', 'Vault Secret ID not set');
	}

	/**
	  * check that the given value exists and is not empty.
	  * @param string $valueKey     - the config key to be verified
	  * @param string $errorMessage - exception message to be thrown in case or error.
	  * @throws VaultException when the given key was not set.
	  */
	private function check($valueKey, $errorMessage) {
		if (!isset($this->$valueKey)    || ($this->$valueKey == NULL) ||
		    !is_string($this->$valueKey) || (trim($this->$valueKey) == '')) {
			throw new VaultException($errorMessage. ' ('.$valueKey.')', VAULT_ERR_CONFIG);
		}
	}

	/**
	  * Set the vault login credentials.
	  * @param string $roleId   - the role ID in vault
	  * @param string $secretId - the secret ID of the client
	  */
	public function setVaultCredentials($roleId, $secretId) {
		$this->roleId   = $roleId;
		$this->secretId = $secretId;
		$this->check('roleId',   'Vault AppRole ID not set');
		$this->check('secretId', 'Vault Secret ID not set');
	}

}
