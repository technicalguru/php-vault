<?php

namespace TgVault;

require_once(__DIR__.'/commons.php');

/**
  * A Helper class that has a vault as a backend and a defined secret path to request.
  * An application can use this to fetch username and password on request only. The
  * CredentialsProvider will ask the vault in the backend transparently.
  */
class CredentialsProvider extends SecretProvider implements \TgUtils\Auth\CredentialsProvider {

	/** The key in the secret holding the username (default is 'username') */
	private $usernameKey;
	/** The key in the secret holding the password (default is 'password') */
	private $passwordKey;

	/**
	  * Construct the provider.
	  * @param Vault  $vault       - the backend vault instance
	  * @param string $path        - the path of the secret in the vault
	  * @param string $usernameKey - the key in the secret holding the username (default is 'username')
	  * @param string $passwordKey - the key in the secret holding the password (default is 'password')
	  * @throws VaultException when vault or path are NULL
	  */
	public function __construct($vault, $path, $usernameKey = NULL, $passwordKey = NULL) {
		parent::__construct($vault, $path);
		if (($usernameKey == NULL) || (trim($usernameKey) == '')) $usernameKey = 'username';
		if (($passwordKey == NULL) || (trim($passwordKey) == '')) $passwordKey = 'password';
		$this->usernameKey = $usernameKey;
		$this->passwordKey = $passwordKey;
	}

	/**
	  * Returns the username in the secret.
	  * @return string the username as given in the secret
	  * @throws VaultException when the secret cannot be retrieved or does not exist
	  */
	public function getUsername() {
		return $this->get($this->usernameKey);
	}

	/**
	  * Returns the password in the secret.
	  * @return string the password as given in the secret
	  * @throws VaultException when the secret cannot be retrieved or does not exist
	  */
	public function getPassword() {
		return $this->get($this->passwordKey);
	}

}

