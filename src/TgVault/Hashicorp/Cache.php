<?php

namespace TgVault\Hashicorp;

require_once(__DIR__.'/../commons.php');

/**
  * A simple file cache mainly for the token.
  */
class Cache {

	/** the cache data */
	protected $data;
	/** where to find the cache file */
	protected $cacheFile;
	/** The logger */
	protected $logger;

	/**
	  * Creates the cache.
	  * @param string $cacheFile - where the cache is located in filesystem.
	  * @param object $logger    - a logger, either TgVault\Logger or Psr\Log\LoggerInterface
	  */
	public function __construct($cacheFile, $logger = NULL) {
		$this->cacheFile = $cacheFile;
		$this->logger    = $logger;
	}

	/**
	  * Loads the cache if required.
	  */
	protected function load() {
		if ($this->data == null) {
			if (($this->cacheFile) && file_exists($this->cacheFile)) {
				$contents = file_get_contents($this->cacheFile);
				if ($contents === FALSE) {
					if ($this->logger != null) $this->logger->error('[Cache] Cannot read cache');
				} else if (!empty($contents)) $this->data = json_decode($contents);
				else $this->data = new \stdClass;
			} else {
				$this->data = new \stdClass;
			}
		}
	}

	/**
	  * Saves the cache.
	  * @return TRUE when cache was written successfully, FALSE otherwise.
	  */
	protected function save() {
		if (($this->data != null) && $this->cacheFile) {
			$rc = file_put_contents($this->cacheFile, json_encode($this->data), LOCK_EX);
			if ($rc === FALSE) {
				if ($this->logger != NULL) $this->logger->error('[Cache] Cannot write cache');
				return false;
			}
			return true;
		}
		return false;
	}

	/**
	  * Return data from cache.
	  * @param string $key - the key in the cache.
	  * @return mixed - the data from the cache or NULL if not available.
	  */
	public function get($key) {
		$this->load();
		if (isset($this->data->$key)) {
			return $this->data->$key;
		}
		return null;
	}

	/**
	  * Sets a value in the cache.
	  * @param string $key   - the key in the cache.
	  * @param mixed  $value - the value to be stored.
	  */
	public function set($key, $value) {
		$this->load();
		$this->data->$key = $value;
		$this->save();
	}

	/**
	  * Deletes a value in the cache.
	  * @param string $key   - the key in the cache.
	  */
	public function delete($key) {
		$this->load();
		if (isset($this->data->$key)) {
			unset($this->data->$key);
			$this->save();
		}
	}

	/**
	  * Set the logger and log all information via this object.
	  * @param object $logger    - a logger, either TgVault\Logger or Psr\Log\LoggerInterface
	  */
	public function setLogger($logger) {
		$this->logger = $logger;
	}
}
