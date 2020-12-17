<?php

namespace TgVault\Memory;

require_once(__DIR__.'/../commons.php');

use TgVault\Vault;
use TgVault\BaseVault;
use TgVault\Secret;
use TgVault\VaultException;


/**
  * A memory based vault. The secrets are directly given in the config
  * key 'secrets'. No further configuration is required.
  */
class MemoryVault extends BaseVault implements Vault {

	/** The secrets */
	private   $secrets;

	/**
	  * Creates the memory vault with secrets.
	  * @param mixed $config  - array or object with only key/attribute 'secrets' that is an array(path => data).
	  * @param object $logger - the logger to be used (not used in this implementation)
	  */
	public function __construct($config, $logger = NULL) {
		parent::__construct($logger);
		if ($config == NULL) throw new VaultException('Configuration must be set', VAULT_ERR_CONFIG_EMPTY);
		$this->secrets  = array();
		$secrets        = array();
		if (is_object($config) && isset($config->secrets)) $secrets = $config->secrets;
		else if (is_array($config) && isset($config['secrets'])) $secrets = $config['secrets'];
		foreach ($secrets AS $path => $data) {
			$this->secrets[$path] = new Secret(array('data' => $data));
		}
	}

	/**
	  * Returns the secret at the given path.
	  * @param string $path - an arbitrary path that uniquely identifies a secret in the vault.
	  * @return Secret
	  * @throws VaultException when the secret cannot be found or retrieved.
	  */
	public function getSecret($path) {
		if (!isset($this->secrets[$path])) {
			throw new VaultException('Secret not available', VAULT_ERR_NOT_FOUND);
		}
		return $this->secrets[$path];
	}

}

