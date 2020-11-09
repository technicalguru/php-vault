<?php

namespace Vault;

require_once(__DIR__.'/commons.php');

/**
  * All vault types must implement this interface.
  */
interface Vault {

	/**
	  * Returns the secret at the given path.
	  * @param string $path - an arbitrary path that uniquely identifies a secret in the vault.
	  * @return the Secret
	  * @throws an exception when the secret cannot be found or retrieved.
	  */
	public function getSecret(string $path);

	/**
	  * Set the logger and log all information via this object.
	  * It is up to the vault whether it uses the logger and what it logs there.
	  * @param Logger - the logging object.
	  */
	public function setLogger(Logger $logger);
}

