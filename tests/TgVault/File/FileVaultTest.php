<?php declare(strict_types=1);

namespace TgVault\File;

use PHPUnit\Framework\TestCase;

/**
 * Tests the MemoryVault.
 * @author ralph
 *
 */
final class FileVaultTest extends TestCase {

	public function testConstruct(): void {
	    $vault = new FileVault(array('filename' => __DIR__.'/test-config.json'));
	    for ($i = 1; $i<3; $i++) {
	        $this->assertNotNull($vault->getSecret('my/secret/number/'.$i));
	        $this->assertEquals('my-username'.$i, $vault->getSecret('my/secret/number/'.$i)->get('username'));
	        $this->assertEquals('my-password'.$i, $vault->getSecret('my/secret/number/'.$i)->get('password'));
	    }
	}
	
}