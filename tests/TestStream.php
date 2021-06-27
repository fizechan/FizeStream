<?php


use fize\stream\Stream;
use PHPUnit\Framework\TestCase;

class TestStream extends TestCase
{

    public function test__construct()
    {
        $resource = fopen(__DIR__ . '/../temp/stream.txt', 'w+');
        $stream1 = new Stream($resource);
        self::assertIsObject($stream1);
    }

    public function test__destruct()
    {
        $resource = fopen(__DIR__ . '/../temp/stream.txt', 'w+');
        $stream = new Stream($resource);
        unset($stream);
        self::assertTrue(true);
    }

    public function test__toString()
    {
        $resource = fopen(__DIR__ . '/../temp/stream.txt', 'r');
        $stream = new Stream($resource);
        $string = (string)$stream;
        var_dump($string);
        self::assertIsString($string);
    }

    public function testClose()
    {
        $resource = fopen(__DIR__ . '/../temp/stream.txt', 'w+');
        $stream = new Stream($resource);
        $stream->close();
        self::assertTrue(true);
    }

    public function testDetach()
    {
        $resource = fopen(__DIR__ . '/../temp/stream.txt', 'w+');
        $stream = new Stream($resource);
        $resource = $stream->detach();
        var_dump($resource);
        self::assertTrue(true);
    }

    public function testGetSize()
    {
        $resource = fopen(__DIR__ . '/../temp/stream.txt', 'r');
        $stream = new Stream($resource);
        $size = $stream->getSize();
        var_dump($size);
        self::assertIsInt($size);
    }

    public function testTell()
    {
        $resource = fopen(__DIR__ . '/../temp/stream.txt', 'r');
        $stream = new Stream($resource);
        $pos1 = $stream->tell();
        self::assertEquals(0, $pos1);
        $stream->read(100);
        $pos2 = $stream->tell();
        var_dump($pos2);
        self::assertNotEquals(0, $pos2);
    }

    public function testEof()
    {
        $resource = fopen(__DIR__ . '/../temp/stream.txt', 'r');
        $stream = new Stream($resource);
        $eof1 = $stream->eof();
        self::assertFalse($eof1);
        $stream->getContents();
        $eof2 = $stream->eof();
        self::assertTrue($eof2);
    }

    public function testIsSeekable()
    {
        $resource = fopen(__DIR__ . '/../temp/stream.txt', 'r');
        $stream1 = new Stream($resource);
        $seek1 = $stream1->isSeekable();
        self::assertTrue($seek1);
    }

    public function testSeek()
    {
        $resource = fopen(__DIR__ . '/../temp/stream.txt', 'r');
        $stream = new Stream($resource);
        $stream->seek(100);
        $string = $stream->read(10);
        var_dump($string);
        self::assertTrue(true);
    }

    public function testRewind()
    {
        $resource = fopen(__DIR__ . '/../temp/stream.txt', 'r');
        $stream = new Stream($resource);
        $stream->seek(100);
        $string1 = $stream->read(10);
        $stream->rewind();
        $string2 = $stream->read(10);
        var_dump($string1);
        var_dump($string2);
        self::assertNotEquals($string1, $string2);
    }

    public function testIsWritable()
    {
        $resource1 = fopen(__DIR__ . '/../temp/stream1.txt', 'w');
        $stream1 = new Stream($resource1);
        $seek1 = $stream1->isWritable();
        self::assertTrue($seek1);

        $resource2 = fopen(__DIR__ . '/../temp/stream.txt', 'r');
        $stream2 = new Stream($resource2);
        $seek2 = $stream2->isWritable();
        self::assertFalse($seek2);
    }

    public function testWrite()
    {
        $resource1 = fopen(__DIR__ . '/../temp/stream1.txt', 'w');
        $stream1 = new Stream($resource1);
        $ink1 = $stream1->write('哈哈哈哈！~！~！');
        self::assertIsInt($ink1);
    }

    public function testIsReadable()
    {
        $resource1 = fopen(__DIR__ . '/../temp/stream1.txt', 'w');
        $stream1 = new Stream($resource1);
        $result1 = $stream1->isReadable();
        self::assertFalse($result1);

        $resource2 = fopen(__DIR__ . '/../temp/stream.txt', 'r');
        $stream2 = new Stream($resource2);
        $result2 = $stream2->isReadable();
        self::assertTrue($result2);
    }

    public function testRead()
    {
        $resource2 = fopen(__DIR__ . '/../temp/stream.txt', 'r');
        $stream2 = new Stream($resource2);
        $string2 = $stream2->read(100);
        var_dump($string2);
        self::assertIsString($string2);
    }

    public function testGetContents()
    {
        $resource = fopen(__DIR__ . '/../temp/stream.txt', 'r');
        $stream = new Stream($resource);
        $contents = $stream->getContents();
        var_dump($contents);
        self::assertIsString($contents);
    }

    public function testGetMetadata()
    {
        $resource = fopen(__DIR__ . '/../temp/stream.txt', 'r');
        $stream = new Stream($resource);
        $metas = $stream->getMetadata();
        var_dump($metas);
        self::assertIsArray($metas);
        $size = $stream->getMetadata('size');
        var_dump($size);
        self::assertNull($size);
        $seekable = $stream->getMetadata('seekable');
        self::assertIsBool($seekable);
    }

    public function testCopyToString()
    {
        $resource = fopen(__DIR__ . '/../temp/stream.txt', 'r');
        $stream = new Stream($resource);
        $string = Stream::copyToString($stream);
        var_dump($string);
        self::assertIsString($string);
    }

    public function testCopyToStream()
    {
        $resource1 = fopen(__DIR__ . '/../temp/stream.txt', 'r');
        $stream1 = new Stream($resource1);
        $resource2 = fopen(__DIR__ . '/../temp/stream2.txt', 'w+');
        $stream2 = new Stream($resource2);
        Stream::copyToStream($stream1, $stream2);
        self::assertTrue(true);
    }
}
