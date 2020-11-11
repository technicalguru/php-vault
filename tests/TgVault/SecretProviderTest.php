<?php declare(strict_types=1);

namespace TgVault;

use PHPUnit\Framework\TestCase;

/**
 * Tests the SecretProvider.
 * @author ralph
 *
 */
final class SecretProviderTest extends TestCase {

	public function testGet(): void {
	    $vault = $this->getTestVault();
	    $provider = new SecretProvider($vault, 'my/secret/number/2');
        $this->assertEquals('my-username2', $provider->get('username'));
        $this->assertEquals('my-password2', $provider->get('password'));
	}
	
	protected function getTestVault(): Vault {
	    return new File\FileVault(array('filename' => __DIR__.'/File/test-config.json'));    
	}
}