<?php

namespace TgVault;

require_once(__DIR__.'/commons.php');

/**
  * A vault exception for any errors that need to be brought to attention.
  */
class VaultException extends \Exception {

	private $details;

	/**
	  * Default constructor from PHP exception.
	  */
	public function __construct($message = null, $code = 0, \Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}

	/**
	  * Sets some debug information if available.
	  * @param mixed $details - some debug info
	  */
	public function setDetails($details) {
		$this->details = $details;
	}

	/**
	  * Returns some debug information if available.
	  * @return mixed $details - some debug info
	  */
	public function getDetails() {
		return $this->details;
	}
}
