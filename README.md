# technicalguru/php-vault
A flexible PHP-based vault to provide secrets dynamically

# License
This project is licensed under [GNU LGPL 3.0](LICENSE.md). 

# Installation

## By Composer

```sh
composer install technicalguru/vault
```

## By Package Download
You can download the source code packages from [GitHub Release Page](https://github.com/technicalguru/php-vault/releases)

# Hashicorp Setup
The procedure is best described at [Hashicorp Blog](https://www.hashicorp.com/blog/authenticating-applications-with-vault-approle). It describes
how to create an `approle`. Here is the essence of it:

```sh
# Enable the auth method for approle
vault auth enable approle

# Create a renewal policy
echo 'path "auth/token/*" { capabilities = [ "create", "read", "update", "delete", "list", "sudo" ] }' >renewal-policy.hcl
vault policy write renewal-policy renewal-policy.hcl

# Create a file with your policy on the respective secret path:
cat 'path "secret/my-secret" { capabilities = ["read", "list"] }' >app-policy.hcl

# Create the policy
vault policy write my-app-policy app-policy.hcl

# Create the approle with renewal-policy and your application policy
vault write auth/approle/role/my-approle token_policies=renewal-policy,my-app-policy token_period=30m token_ttl=30m token_max_ttl=1h token_explicit_max_ttl=2h

# Get the role ID printed
vault read auth/approle/role/my-approle/role-id

# Create the secret ID and print it
vault write -f auth/approle/role/my-approle/secret-id
```

Please notice that you need to recreate the secret ID whenever you change the application role or a policy.

# Examples
## Create a HashicorpVault
Please note that this vault is actually a client to an existing Hashicorp Vault.

```php
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
	$vault = \TgVault\VaultFactory::create($config);
} catch (\TgVault\VaultException $e) {
	// Vault could not be created
}

```

## Create a MemoryVault

```php
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
	$vault = \TgVault\VaultFactory::create($config);
} catch (\TgVault\VaultException $e) {
	// Vault could not be created
}
```

## Create a FileVault

```php
// Create configuration
$config = array(
	'type'   => 'file',
	'config' => array(
		'filename' => 'path-to-json-secret-file'
	)
);

// Create the vault instance
try {
	$vault = \TgVault\VaultFactory::create($config);
} catch (\TgVault\VaultException $e) {
	// Vault could not be created
}
```

The secrets file (JSON) shall look like this:

```json
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

```php
try {
	$mySecret1 = $vault->getSecret('my/secret/number/1');
	$mySecret2 = $vault->getSecret('my/secret/number/2');
} catch (\TgVault\VaultException $e) {
	// secret was not found
}

$username1 = $mySecret1->get('username');
$password1 = $mySecret1->get('password');
$username2 = $mySecret2->get('username');
$password2 = $mySecret2->get('password');
```

A value in a secret is `NULL` when the key does not exists whereas an exception will be thrown when the secret itself cannot be found
or an error occurred while retrieval.

## Using lazy callback credentials
You can use the `SecretProvider` or `CredentialsProvider` helper classes to pass them credentials without knowing where they come from
or how to use a vault.

```php
$callback1 = new \TgVault\SecretProvider($vault, 'my/secret/number/1');
$callback2 = new \TgVault\CredentialsProvider($vault, 'my/secret/number/2');

try {
	$username1 = $callback1->get('username');
	$password1 = $callback1->get('password');

	$username2 = $callback2->getUsername();
	$password2 = $callback2->getPassword();
} catch (\TgVault\VaultException $e) {
	// Secret cannot be retrieved or does not exist
}
```

The `CredentialsProvider` takes additional constructor arguments that define, which keys in the secret provide username and password. The 
defaults are as given above for the `SecretProvider`.


# Contribution
Report a bug, request an enhancement or pull request at the [GitHub Issue Tracker](https://github.com/technicalguru/php-vault/issues).

