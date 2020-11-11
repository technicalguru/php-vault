<?php declare(strict_types=1);

namespace TgVault\Memory;

use PHPUnit\Framework\TestCase;

/**
 * Tests the MemoryVault.
 * @author ralph
 *
 */
final class MemoryVaultTest extends TestCase {

	public function testConstruct(): void {
	    $vault = new MemoryVault($this->getConfig());
	    for ($i = 1; $i<3; $i++) {
	        $this->assertNotNull($vault->getSecret('path/to/secret'.$i));
	        $this->assertEquals('value-'.$i.'-1', $vault->getSecret('path/to/secret'.$i)->get('key-1'));
	        $this->assertEquals('value-'.$i.'-2', $vault->getSecret('path/to/secret'.$i)->get('key-2'));
	    }
	}
	
	protected function getConfig(): array {
	    return $config = array(
	        'secrets' => array(
	            'path/to/secret1' => $this->getSecret(1),
	            'path/to/secret2' => $this->getSecret(2),
	        ),
	    );
	}
	
	protected function getSecret(int $init): array {
	    return array(
	        'key-1' => 'value-'.$init.'-1',
	        'key-2' => 'value-'.$init.'-2',
	    );
	}
}