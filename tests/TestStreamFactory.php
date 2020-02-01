<?php


use fize\stream\StreamFactory;
use PHPUnit\Framework\TestCase;

class TestStreamFactory extends TestCase
{

    public function testCreateStream()
    {
        $factory = new StreamFactory();
        $str = '0123456789';
        $stream = $factory->createStream($str);
        var_dump($stream);
        $content = $stream->getContents();
        var_dump($content);
        self::assertEquals('0123456789', $content);
        $stream->write('9876543210');
        $stream->rewind();
        $content = $stream->getContents();
        var_dump($content);
        self::assertEquals('01234567899876543210', $content);
        $writable = $stream->isWritable();
        self::assertTrue($writable);
        $readble = $stream->isReadable();
        self::assertTrue($readble);
    }

    public function testCreateStreamFromFile()
    {
        $factory = new StreamFactory();
        $stream = $factory->createStreamFromFile(__DIR__ . '/../temp/stream.txt');
        $writable = $stream->isWritable();
        self::assertFalse($writable);
        $readble = $stream->isReadable();
        self::assertTrue($readble);
        $content = $stream->getContents();
        var_dump($content);
    }

    public function testCreateStreamFromResource()
    {
        $resource = fopen(__DIR__ . '/../temp/stream.txt', 'r');
        $factory = new StreamFactory();
        $stream = $factory->createStreamFromResource($resource);
        $writable = $stream->isWritable();
        self::assertFalse($writable);
        $readble = $stream->isReadable();
        self::assertTrue($readble);
        $content = $stream->getContents();
        var_dump($content);
    }
}
