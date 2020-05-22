# vault-php
A flexible PHP-based vault to provide secrets dynamically

# License
This project is licensed under [GNU LGPL 3.0](LICENSE.md). 

# Examples
## Create a HashicorpVault
```
// Create configuration
$config = array(
	'type'   => 'hashicorp',
	'config' => array(
		'uri'      => 'https://127.0.0.1:8200/v1',
		'roleId'   => '123456-12345-12345-123456',
		'secretId' => 'abcdef-abcde-abcde-abcdef'
	)
);

// Create the vault instance
try {
	$vault = \Vault\VaultFactory::create($config);
} catch (\Vault\VaultException $e) {
	// Vault could not be created
}

```

## Create a MemoryVault
```
// Create configuration
$config = array(
	'type'   => 'memory',
	'config' => array(
		'secrets' => array(
			'my/secret/number/1' => array(
				'username' => 'my-username1',
				'password' => 'my-password1',
			),
			'my/secret/number/2' => array(
				'username' => 'my-username2',
				'password' => 'my-password2',
			),
		)
	)
);

// Create the vault instance
try {
	$vault = \Vault\VaultFactory::create($config);
} catch (\Vault\VaultException $e) {
	// Vault could not be created
}
```

## Create a FileVault
```
// Create configuration
$config = array(
	'type'   => 'file',
	'config' => array(
		'filename' => 'path-to-json-secret-file'
	)
);

// Create the vault instance
try {
	$vault = \Vault\VaultFactory::create($config);
} catch (\Vault\VaultException $e) {
	// Vault could not be created
}
```

The secrets file (JSON) shall look like this:
```
{
	"secrets": {
		"my/secret/number/1" : {
			"username" : "my-username1",
			"password" : "my-password1"
		},
		"my/secret/number/2" : {
			"username" : "my-username2",
			"password" : "my-password2"
		}
	}
}
```

## Retrieving a secret
```
try {
	$mySecret1 = $vault->get('my/secret/number/1');
	$mySecret2 = $vault->get('my/secret/number/2');
} catch (\Vault\VaultException $e) {
	// secret was not found
}

$username1 = $mySecret1->get('username');
$password1 = $mySecret1->get('password');
$username2 = $mySecret2->get('username');
$password2 = $mySecret2->get('password');
```

A value in a key is `NULL` when the key does not exists whereas an exception will be thrown when the secret itself cannot be found
or an error occurred while retrieval.

# Contribution
Report a bug, request an enhancement or pull request at the [GitHub Issue Tracker](https://github.com/technicalguru/vault-php/issues).

