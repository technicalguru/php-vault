<?php

namespace Vault;

interface Logger {

	/**
	  * Log the given string (and $object) in DEBUG level.
	  * @param string $s      - the string to be logged
	  * @param mixed  $object - an optional object that can be inspected (usually json-encoded in log)
	  */
	public function debug(string $s, $object = NULL);

	/**
	  * Log the given string (and $object) in DEBUG level.
	  * @param string $s      - the string to be logged
	  * @param mixed  $object - an optional object that can be inspected (usually json-encoded in log)
	  */
	public function info(string $s, $object = NULL);

	/**
	  * Log the given string (and $object) in WARN level.
	  * @param string $s      - the string to be logged
	  * @param mixed  $object - an optional object that can be inspected (usually json-encoded in log)
	  */
	public function warn(string $s, $object = NULL);

	/**
	  * Log the given string (and $object) in DEBUG level.
	  * @param string $s      - the string to be logged
	  * @param mixed  $object - an optional object that can be inspected (usually json-encoded in log)
	  */
	public function error(string $s, $object = NULL);

}
