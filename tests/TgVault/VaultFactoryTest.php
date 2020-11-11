<?php declare(strict_types=1);

namespace TgVault;

use PHPUnit\Framework\TestCase;

/**
 * Tests the VaultFactory.
 * @author ralph
 *
 */
final class VaultFactoryTest extends TestCase {
    
    public function testConstructFileVault(): void {
        $config = $this->getConfig();
        $vault  = VaultFactory::create($config['file']);
        $this->assertInstanceOf(File\FileVault::class, $vault);
        for ($i = 1; $i<3; $i++) {
            $this->assertNotNull($vault->getSecret('my/secret/number/'.$i));
            $this->assertEquals('my-username'.$i, $vault->getSecret('my/secret/number/'.$i)->get('username'));
            $this->assertEquals('my-password'.$i, $vault->getSecret('my/secret/number/'.$i)->get('password'));
        }
    }
    
    public function testConstructMemoryVault(): void {
        $config = $this->getConfig();
        $vault  = VaultFactory::create($config['memory']);
        $this->assertInstanceOf(Memory\MemoryVault::class, $vault);
        for ($i = 1; $i<3; $i++) {
            $this->assertNotNull($vault->getSecret('path/to/secret'.$i));
            $this->assertEquals('value-'.$i.'-1', $vault->getSecret('path/to/secret'.$i)->get('key-1'));
            $this->assertEquals('value-'.$i.'-2', $vault->getSecret('path/to/secret'.$i)->get('key-2'));
        }
    }
    
    protected function getConfig(): array {
        return array(
            'file'   => array(
                'type'   => 'file',
                'config' => array(
                    'filename' => __DIR__.'/File/test-config.json'
                ),
            ),
            'memory' => array(
                'type'   => 'memory',
                'config' => array(
                    'secrets' => array(
                        'path/to/secret1' => array(
                            'key-1' => 'value-1-1',
                            'key-2' => 'value-1-2',
                        ),
                        'path/to/secret2' => array(
                            'key-1' => 'value-2-1',
                            'key-2' => 'value-2-2',
                        ),
                    ),
                ),
            ),
        );
    }
}