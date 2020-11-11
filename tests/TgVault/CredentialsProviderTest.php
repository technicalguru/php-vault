<?php declare(strict_types=1);

namespace TgVault;

use PHPUnit\Framework\TestCase;

/**
 * Tests the CredentialsProvider.
 * @author ralph
 *
 */
final class CredentialsProviderTest extends TestCase {

    public function testWithDefaultKeys(): void {
        $vault = $this->getTestVault();
        $provider = new CredentialsProvider($vault, 'path/to/secret1');
        $this->assertEquals('username1', $provider->getUsername());
        $this->assertEquals('password1', $provider->getPassword());
    }
    
    public function testWithCustomKeys(): void {
        $vault = $this->getTestVault();
        $provider = new CredentialsProvider($vault, 'path/to/secret2', 'username-2', 'password-2');
        $this->assertEquals('username2', $provider->getUsername());
        $this->assertEquals('password2', $provider->getPassword());
    }
    
    protected function getTestVault(): Vault {
        return new Memory\MemoryVault(array(
            'secrets' => array(
                'path/to/secret1' => array(
                    'username' => 'username1',
                    'password' => 'password1',
                ),
                'path/to/secret2' => array(
                    'username-2' => 'username2',
                    'password-2' => 'password2',
                ),
            ),
        ));
    }
}