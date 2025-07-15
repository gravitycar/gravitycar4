<?php
namespace Gravitycar\tests\exceptions;
use Gravitycar\GCException;
use PHPUnit\Framework\TestCase;

class GCExceptionTest extends TestCase
{
    private string $logFile;

    protected function setUp(): void
    {
        $this->logFile = __DIR__ . '/exceptions.log';
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }

    public function testExceptionLogging(): void
    {
        $exception = new GCException('Test message', 123, true);

        $this->assertFileExists($this->logFile);
        $logContents = file_get_contents($this->logFile);
        $this->assertStringContainsString('Test message', $logContents);
        $this->assertStringContainsString('123', $logContents);
    }

    public function testExceptionWithoutLogging(): void
    {
        $exception = new GCException('No logging', 456, false);

        $this->assertFileDoesNotExist($this->logFile);
    }

    public function testConvertMethod(): void
    {
        $originalException = new \Exception('Original exception', 789);
        $convertedException = GCException::convert($originalException);

        $this->assertInstanceOf(GCException::class, $convertedException);
        $this->assertSame('Original exception', $convertedException->getMessage());
        $this->assertSame(789, $convertedException->getCode());
    }

    public function testConvertWithInvalidClassName(): void
    {
        $originalException = new \Exception('Invalid class', 101);
        $convertedException = GCException::convert($originalException, 'InvalidClassName');

        $this->assertInstanceOf(GCException::class, $convertedException);
        $this->assertSame('Invalid class', $convertedException->getMessage());
        $this->assertSame(101, $convertedException->getCode());
    }
}