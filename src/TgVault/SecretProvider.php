<?php

namespace TgVault;

require_once(__DIR__.'/commons.php');

/**
  * A Helper class that has a vault as a backend and a defined secret path to request.
  * An application can use this to fetch the required secret on request only. The
  * SecretProvider will ask the vault in the backend transparently.
  */
class SecretProvider {

	/** The backend vault instance */
	private $vault;
	/** The path of the secret in the vault */
	private $path;
	/** The secret fetched from the vault */
	private $secret;

	/**
	  * Construct the provider.
	  * @param Vault  $vault       - the backend vault instance
	  * @param string $path        - the path of the secret in the vault
	  * @param string $usernameKey - the key in the secret holding the username (default is 'username')
	  * @param string $passwordKey - the key in the secret holding the password (default is 'password')
	  * @throws VaultException when vault or path are NULL
	  */
	public function __construct($vault, $path) {
		if ($vault == NULL) throw new VaultException('Vault cannot be NULL.', VAULT_ERR_NULL);
		if ($path  == NULL) throw new VaultException('Path cannot be NULL.', VAULT_ERR_NULL);
		$this->vault  = $vault;
		$this->path   = $path;
		$this->secret = NULL;
	}

	/**
	  * Returns a value from the secret.
	  * The provider will load the secret if not done yet.
	  * @param string $key - the key of the value to be retrieved.
	  * @return string the value or NULL if not set.
	  * @throws VaultException when the secret does not exist.
	  */
	public function get($key) {
		if ($this->secret == NULL) {
			$this->loadSecret();
		}
		if (($this->secret != NULL) && ($this->secret instanceof Secret)) {
			return $this->secret->get($key);
		}
		throw new VaultException('No such secret: '.$this->path, VAULT_ERR_NOT_FOUND);
	}

	/**
	  * Loads the secret.
	  * @throws VaultException when loading fails.
	  */
	protected function loadSecret() {
		if ($this->secret == NULL) {
			try {
				$this->secret = $this->vault->getSecret($this->path);
			} catch (VaultException $e) {
				$this->secret = $e;
				throw $e;
			}
		}
	} 
}

