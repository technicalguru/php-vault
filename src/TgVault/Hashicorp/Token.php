<?php

namespace TgVault\Hashicorp;

require_once(__DIR__.'/../commons.php');

class Token {

	// Vault attributes
	public $client_token;
	public $accessor;
	public $policies;
	public $token_policies;
	public $metadata;
	public $lease_duration;
	public $renewable;
	public $entity_id;
	public $token_type;
	public $orphan;

	// custom attributes
	/** Time this token was created */
	public $creationTime;
	/** Time of expiration */
	public $expiryTime;
	/** user-readable string of expiration time */
	public $expiryString;
	/** the current time (for comparison) */
	public $now;

	public function __construct($data) {
		foreach (get_object_vars($data) AS $key => $value) {
			$this->$key = $value;
		}
		if (!isset($this->creationTime)) {
			$this->creationTime = time();
		}
		if (!isset($this->expiryTime) && isset($this->lease_duration)) {
			$this->expiryTime   = time() + $this->lease_duration;
		}
		if (!isset($this->expiryString) && isset($this->expiryTime)) {
			$this->expiryString = date(DATE_ATOM, $this->expiryTime);
		}
		$this->now = time();
	}

	/**
	  * Is the token expired?
	  * @return TRUE when the token has expired, FALSE otherwise.
	  */
	public function isExpired() {
		return time() >= $this->expiryTime-5;
	}

	/**
	  * Returns a basic info string for logging purposes.
	  * @return string info string about the token.
	  */
	public function getInfo() {
		return substr($this->client_token, 0, 3).'**********'.substr($this->client_token, -3).' ['.
			'created='.date(DATE_ATOM, $this->creationTime).
			',expires='.$this->expiryString.
			',renewable='.($this->renewable ? 'true' : 'false').
			',policies='.json_encode($this->policies).
			']';
	}

}
