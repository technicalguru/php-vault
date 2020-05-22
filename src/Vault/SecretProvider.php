<?php

namespace Vault;

/**
  * A Helper class that has a vault as a backend and a defined secret path to request.
  * An application can use this to fetch the required secret on request only. The
  * SecretProvider will ask the vault in the backend transparently.
  */
class SecretProvider {

	private $vault;
	private $path;
	private $secret;

	public function __construct(Vault $vault, string $path) {
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
	  * @return the value or NULL if not set.
	  * @throws an exception when the secret does not exist.
	  */
	public function get(string $key) {
		if ($this->secret == NULL) {
			$this->loadSecret();
		}
		if (($this->secret != NULL) && is_a($this->secret, 'Vault\\Secret')) {
			return $this->secret->get($key);
		}
		throw new VaultException('No such secret: '.$this->path, VAULT_ERR_NOT_FOUND);
	}

	/**
	  * Loads the secret.
	  * @throws an exception when loading fails.
	  */
	protected function loadSecret() {
		if ($this->secret == NULL) {
			try {
				$this->secret = $this->vault->getSecret($this->path);
			} catch (VaultException $e) {
				$this->secret = $e;
				\WLog::error('thrown in vault::getSecret - '.$e->getMessage(), $e);
				throw $e;
			}
		}
	} 
}

