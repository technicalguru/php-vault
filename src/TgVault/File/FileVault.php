<?php

namespace TgVault\File;

require_once(__DIR__.'/../commons.php');

use TgVault\Vault;
use TgVault\BaseVault;
use TgVault\Secret;
use TgVault\VaultException;
use TgVault\Logger;

/**
  * A file based vault. The secrets are stored in JSON format in a file
  * and appear in key 'secrets'. The configuration requires the only
  * key 'filename' which points to an existing path on disk.
  */
class FileVault extends BaseVault implements Vault {

	/** The secrets file */
	protected $filename;
	/** The secrets */
	private   $secrets;

	/**
	  * Creates the file vault with secrets.
	  * @param mixed $config  - array or object with key/attribute 'filename'.
	  * @param Logger $logger - the logger to be used (not used in this implementation)
	  */
	public function __construct($config, $logger = NULL) {
		parent::__construct($logger);
		if ($config == NULL) throw new VaultException('Configuration must be set', VAULT_ERR_CONFIG_EMPTY);
		$this->secrets  = NULL;
		if (is_object($config)) $this->filename = $config->filename;
		else if (is_array($config))  $this->filename = $config['filename'];
		else throw new VaultException('Configuration must contain filename', VAULT_ERR_CONFIG);
	}

	/**
	  * Loads the secrets file from disk if required.
	  */
	protected function load() {
		if ($this->secrets == NULL) {
			$this->secrets = array();
			$json = file_get_contents($this->filename);
			if ($json === FALSE) {
				throw new VaultException('Cannot find secrets file.', VAULT_ERR_FILE_NOT_FOUND);
			} else {
				$config  = json_decode($json);
				$secrets = array();
				if (is_object($config) && isset($config->secrets)) $secrets = $config->secrets;
				foreach ($secrets AS $path => $data) {
					$this->secrets[$path] = new Secret(array('data' => $data));
				}
			}
		}
	}

	/**
	  * Returns the secret at the given path.
	  * @param string $path - an arbitrary path that uniquely identifies a secret in the vault.
	  * @return Secret
	  * @throws VaultException when the secret cannot be found or retrieved.
	  */
	public function getSecret($path) {
		$this->load();
		if (!isset($this->secrets[$path])) {
			throw new VaultException('Secret not available', VAULT_ERR_NOT_FOUND);
		}
		return $this->secrets[$path];
	}

}

