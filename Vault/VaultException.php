<?php

namespace Vault;

/**
  * A vault exception for any errors that need to be brought to attention.
  */
class VaultException extends \Exception {

	/**
	  * Default constructor from PHP exception.
	  */
	public function __construct($message = null, $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}

}
