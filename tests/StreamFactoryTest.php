<?php


use fize\stream\StreamFactory;
use PHPUnit\Framework\TestCase;

class StreamFactoryTest extends TestCase
{

    public function testCreateStream()
    {
        $factory = new StreamFactory();
        $str = '0123456789';
        $stream = $factory->createStream($str);
        var_dump($stream);
        $content = $stream->getContents();
        var_dump($content);
        self::assertEquals($content, '0123456789');
        $stream->write('9876543210');
        $stream->rewind();
        $content = $stream->getContents();
        var_dump($content);
        self::assertEquals($content, '01234567899876543210');
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
