<?php

namespace TgVault;

require_once(__DIR__.'/commons.php');

/**
  * A secret that holds the values from the vault.
  * This calss is usually created by vaults only.
  */
class Secret {

	private $metadata;
	private $data;

	/**
	  * Constructs the secret from the vault data.
	  * @param $data - the data from the vault
	  */
	public function __construct($data) {
		if (is_object($data)) $data = get_object_vars($data);
		if (is_array($data)) {
			foreach ($data AS $key => $value) {
			    if (is_array($value)) {
			        $this->$key = json_decode(json_encode($value));
			    } else {
				    $this->$key = $value;
			    }
			}
		} else {
			$this->data = $data;
		}
	}

	/**
	  * Returns a value from the secret.
	  * @param string $key - the key of the value to be retrieved.
	  * @return string the value or NULL if not set.
	  */
	public function get($key) {
		if (isset($this->data->$key)) return $this->data->$key;
		return NULL;
	}

	/**
	 * Returns the keys that are available in this secret.
	 * @return list of keys.
	 */
	public function keys() {
		return array_keys(get_object_vars($this->data));
	}

	/**
	  * Returns any metadata - if set - from the vault for this secret
	  * @return mixed the metadata or NULL if not set
	  */
	public function getMeta() {
		return $this->metadata;
	}
}
