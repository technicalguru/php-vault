<?php

namespace TgVault;

require_once(__DIR__.'/commons.php');

/**
  * Base class for all vault implementations here.
  * It provides the logging mechanism only.
  */
class BaseVault implements Vault {

	protected $logger;
	private   $prefix;

	/**
	  * Default constructor that only takes an optiona logger object.
	  * @param object $logger - the logger object, can be a Psr\Log\LoggerInterface or a \TgVault\Logger.
	  */
	public function __construct($logger = NULL) {
		$this->logger   = $logger;
	}

	/**
	  * Returns the secret at the given path.
	  * Must be overridden by subclasses.
	  * @param string $path - an arbitrary path that uniquely identifies a secret in the vault.
	  * @return Secret
	  * @throws VaultException when the secret cannot be found or retrieved.
	  */
	public function getSecret($path) {
		throw new VaultException(get_class().'::getSecret() must be implemented.', VAULT_ERR_INTERNAL);
	}

	/**
	  * Set the logger and log all information via this object.
	  * This can be a Psr\Log\LoggerInterface or a \TgVault\Logger.
	  * @param Logger - the logging object.
	  */
	public function setLogger($logger) {
		$this->logger = $logger;
	}

	/**
	  * Log in debug level.
	  * @see Logger interface
	  * @param $s      - the string to be logged
	  * @param $object - the object to be logged
	  */
	protected function debug($s, $object = NULL) {
		if ($this->logger != NULL) {
			$object = self::cleanObject($object);
			$psrInterface = '\\Psr\\Log\\LoggerInterface';
			if ($this->logger instanceof $psrInterface) {
			} else if ($this->logger instanceof Logger) {
				$this->logger->debug($this->getLoggerPrefix().$s, $object);
			}
		}
	}

	/**
	  * Log in warn level.
	  * @see Logger interface
	  * @param $s      - the string to be logged
	  * @param $object - the object to be logged
	  */
	protected function warn($s, $object = NULL) {
		if ($this->logger != NULL) {
			$object = self::cleanObject($object);
			$psrInterface = '\\Psr\\Log\\LoggerInterface';
			if ($this->logger instanceof $psrInterface) {
			} else if ($this->logger instanceof Logger) {
				$this->logger->warn($this->getLoggerPrefix().$s, $object);
			}
		}
	}

	/**
	  * Log in info level.
	  * @see Logger interface
	  * @param $s      - the string to be logged
	  * @param $object - the object to be logged
	  */
	protected function info($s, $object = NULL) {
		if ($this->logger != NULL) {
			$object = self::cleanObject($object);
			$psrInterface = '\\Psr\\Log\\LoggerInterface';
			if ($this->logger instanceof $psrInterface) {
			} else if ($this->logger instanceof Logger) {
				$this->logger->info($this->getLoggerPrefix().$s, $object);
			}
		}
	}

	/**
	  * Log in error level.
	  * @see Logger interface
	  * @param $s      - the string to be logged
	  * @param $object - the object to be logged
	  */
	protected function error($s, $object = NULL) {
		if ($this->logger != NULL) {
			$object = self::cleanObject($object);
			$psrInterface = '\\Psr\\Log\\LoggerInterface';
			if ($this->logger instanceof $psrInterface) {
			} else if ($this->logger instanceof Logger) {
				$this->logger->error($this->getLoggerPrefix().$s, $object);
			}
		}
	}

	/**
	  * Returns a prefix for the logging string.
	  * Default implementation uses the short class name.
	  * @return string for prefixing logging strings.
	  */
	protected function getLoggerPrefix() {
		if ($this->prefix == NULL) {
			$helper = new \ReflectionClass($this);
			$this->prefix = '['.$helper->getShortName().'] ';
		}
		return $this->prefix;
	}

	/**
	  * Copies the given object and redacts any sensitive strings, such as
	  * passwords, usernames and tokens. The decision is based on object
	  * attribute names. Arrays are not redacted (!).
	  * @param object $o - the object to clean
	  * @return \stdClass a cleaned object
	  */
	public static function cleanObject($o) {
		if ($o == NULL) return NULL;
		if (!is_object($o)) return $o;
		$copy = new \stdClass;
		foreach (get_object_vars($o) AS $key => $value) {
			if (is_object($value)) {
				$copy->$key = self::cleanObject($value);
			} else if (is_string($key)) {
				switch ($key) {
				case 'username':
				case 'password':
				case 'passwd':
				case 'client_token':
				case 'accessor':
					$copy->$key = '***REDACTED***';
					break;
				default:
					if (strpos($key, 'pass') !== FALSE) {
						$copy->$key = '***REDACTED***';
					} else {
						$copy->$key = $value;
					}
				}
			} else {
				$copy->$key = $value;
			}
		}
		return $copy;
	}
}

