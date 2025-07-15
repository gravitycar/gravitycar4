<?php
namespace Gravitycar\tests\lib;
use Gravitycar\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private string $testConfigFile;

    protected function setUp(): void
    {
        $this->testConfigFile = __DIR__ . '/test_config.php';
        if (file_exists($this->testConfigFile)) {
            unlink($this->testConfigFile);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testConfigFile)) {
            unlink($this->testConfigFile);
        }
    }

    public function testGetAndSet(): void
    {
        $config = new Config($this->testConfigFile);
        $config->set('key1', 'value1');
        $this->assertSame('value1', $config->get('key1'));
        $this->assertNull($config->get('nonexistent_key'));
        $this->assertSame('default', $config->get('nonexistent_key', 'default'));
    }

    public function testSaveAndLoadConfig(): void
    {
        $config = new Config($this->testConfigFile);
        $config->set('key1', 'value1');
        $config->set('key2', 'value2');
        $this->assertTrue($config->save());

        $newConfig = new Config($this->testConfigFile);
        $this->assertSame('value1', $newConfig->get('key1'));
        $this->assertSame('value2', $newConfig->get('key2'));
    }

    public function testGetAll(): void
    {
        $config = new Config($this->testConfigFile);
        $config->set('key1', 'value1');
        $config->set('key2', 'value2');
        $this->assertSame(['key1' => 'value1', 'key2' => 'value2'], $config->getAll());
    }

    public function testHas(): void
    {
        $config = new Config($this->testConfigFile);
        $config->set('key1', 'value1');
        $this->assertTrue($config->has('key1'));
        $this->assertFalse($config->has('nonexistent_key'));
    }
}