<?php declare(strict_types=1);

namespace TgVault;

use PHPUnit\Framework\TestCase;

/**
 * Tests the Secret.
 * @author ralph
 *
 */
final class SecretTest extends TestCase {

	public function testConstructFromObject(): void {
	    $data = new \stdClass;
	    $data->metadata = new \stdClass;
	    $data->metadata->time = time();
	    $data->data = new \stdClass;
	    $data->data->key1 = "value1";
	    $data->data->key2 = "value2";
	    $secret = new Secret($data);
	    $this->assertInstanceOf(Secret::class, $secret);
	    $this->assertSame('value1', $secret->get('key1'));
	    $this->assertSame('value2', $secret->get('key2'));
	}

	public function testConstructFromArray(): void {
	    $data = array(
	       'metadata' => array(
	           'time' => time(),
           ),
           'data' => array(
	           'key1' => "value1",
	           'key2' => "value2",
           ),
	    );
	    $secret = new Secret($data);
	    $this->assertInstanceOf(Secret::class, $secret);
	    $this->assertSame('value1', $secret->get('key1'));
	    $this->assertSame('value2', $secret->get('key2'));
	}
	
}