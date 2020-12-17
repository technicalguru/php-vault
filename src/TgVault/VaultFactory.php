<?php

namespace TgVault;

require_once(__DIR__.'/commons.php');

/**
  * An easy way to create a vault based on a configuration.
  */
class VaultFactory {


	/**
	  * Creates the vault based on the 'type' key in the config and
	  * pass on the subkey 'config' value to this vault.
	  * Please see the appropriate vault implementations
	  * for configuration details.
	  * @param mixed $config  - the configuration (array or object).
	  * @param Logger $logger - the logger (optional)
	  * @return Vault created, otherwise it will throw an exception.
	  * @throws VaultException when the vault could not be created.
	  */
	public static function create($config, $logger = NULL) {
		if ($config == NULL) throw new VaultException('Vault configuration cannot be empty', VAULT_ERR_CONFIG_EMPTY);
		if (is_object($config)) return self::createVault($config->type,   $config->config,   $logger);
		if (is_array($config)) return  self::createVault($config['type'], $config['config'], $logger);
		throw new VaultException('Vault configuration must be object or array', VAULT_ERR_CONFIG_TYPE);
	}

	/**
	  * Creates the vault according to type and passes the config object.
	  * The vault class must be defined as "TgVault\Type\TypeVault".
	  * @param string $type   - the type of the vault
	  * @param mixed  $config - the configuration to pass on.
	  * @param Logger $logger - the logger (optional)
	  * @return Vault created and configured
	  * @throws VaultException when the vault could not be created.
	  */
	public static function createVault($type, $config = NULL, $logger = NULL) {
		if (($type == NULL) || (trim($type) == '')) throw new VaultException('Vault type cannot be empty', VAULT_ERR_TYPE_EMPTY);
		$type = ucfirst(trim($type));
		$className = 'TgVault\\'.$type.'\\'.$type.'Vault';
		if (class_exists($className)) {
			return new $className($config, $logger);
		}
		throw new VaultException('Cannot find Vault instance: '.$className, VAULT_ERR_TYPE_NOT_FOUND);
	}
}
