<?php


use fize\stream\Stream;
use PHPUnit\Framework\TestCase;

class TestStream extends TestCase
{

    public function test__construct()
    {
        $stream1 = new Stream(__DIR__ . '/../temp/stream.txt', 'w+');
        self::assertIsObject($stream1);
    }

    public function test__destruct()
    {
        $stream = new Stream(__DIR__ . '/../temp/stream.txt', 'w+');
        unset($stream);
        self::assertTrue(true);
    }

    public function test__toString()
    {
        $stream = new Stream(__DIR__ . '/../temp/stream.txt', 'r');
        $string = (string)$stream;
        var_dump($string);
        self::assertIsString($string);
    }

    public function testClose()
    {
        $stream = new Stream(__DIR__ . '/../temp/stream.txt', 'w+');
        $stream->close();
        self::assertTrue(true);
    }

    public function testDetach()
    {
        $stream = new Stream(__DIR__ . '/../temp/stream.txt', 'w+');
        $resource = $stream->detach();
        var_dump($resource);
        self::assertTrue(true);
    }

    public function testGetSize()
    {
        $stream = new Stream(__DIR__ . '/../temp/stream.txt', 'r');
        $size = $stream->getSize();
        var_dump($size);
        self::assertIsInt($size);
    }

    public function testTell()
    {
        $stream = new Stream(__DIR__ . '/../temp/stream.txt', 'r');
        $pos1 = $stream->tell();
        self::assertEquals(0, $pos1);
        $stream->read(100);
        $pos2 = $stream->tell();
        var_dump($pos2);
        self::assertNotEquals(0, $pos2);
    }

    public function testEof()
    {
        $stream = new Stream(__DIR__ . '/../temp/stream.txt', 'r');
        $eof1 = $stream->eof();
        self::assertFalse($eof1);
        $stream->getContents();
        $eof2 = $stream->eof();
        self::assertTrue($eof2);
    }

    public function testIsSeekable()
    {
        $stream1 = new Stream(__DIR__ . '/../temp/stream.txt', 'r');
        $seek1 = $stream1->isSeekable();
        self::assertTrue($seek1);
    }

    public function testSeek()
    {
        $stream = new Stream(__DIR__ . '/../temp/stream.txt', 'r');
        $stream->seek(100);
        $string = $stream->read(10);
        var_dump($string);
        self::assertTrue(true);
    }

    public function testRewind()
    {
        $stream = new Stream(__DIR__ . '/../temp/stream.txt', 'r');
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
        $stream1 = new Stream(__DIR__ . '/../temp/stream1.txt', 'w');
        $seek1 = $stream1->isWritable();
        self::assertTrue($seek1);

        $stream2 = new Stream(__DIR__ . '/../temp/stream.txt', 'r');
        $seek2 = $stream2->isWritable();
        self::assertFalse($seek2);
    }

    public function testWrite()
    {
        $stream1 = new Stream(__DIR__ . '/../temp/stream1.txt', 'w');
        $ink1 = $stream1->write('哈哈哈哈！~！~！');
        self::assertIsInt($ink1);
    }

    public function testIsReadable()
    {
        $stream1 = new Stream(__DIR__ . '/../temp/stream1.txt', 'w');
        $result1 = $stream1->isReadable();
        self::assertFalse($result1);

        $stream2 = new Stream(__DIR__ . '/../temp/stream.txt', 'r');
        $result2 = $stream2->isReadable();
        self::assertTrue($result2);
    }

    public function testRead()
    {
        $stream2 = new Stream(__DIR__ . '/../temp/stream.txt', 'r');
        $string2 = $stream2->read(100);
        var_dump($string2);
        self::assertIsString($string2);
    }

    public function testGetContents()
    {
        $stream = new Stream(__DIR__ . '/../temp/stream.txt', 'r');
        $contents = $stream->getContents();
        var_dump($contents);
        self::assertIsString($contents);
    }

    public function testGetMetadata()
    {
        $stream = new Stream(__DIR__ . '/../temp/stream.txt', 'r');
        $metas = $stream->getMetadata();
        var_dump($metas);
        self::assertIsArray($metas);
        $size = $stream->getMetadata('size');
        var_dump($size);
        self::assertNull($size);
        $seekable = $stream->getMetadata('seekable');
        self::assertIsBool($seekable);
    }

    public function testCreate()
    {
        $file = fopen(__DIR__ . '/../temp/stream2.txt', 'w+');
        $stream = Stream::create($file);
        var_dump($stream);
        $stream->write('abc123');
        $stream->close();
        self::assertIsObject($stream);
    }

    public function testCopyToString()
    {
        $file = fopen(__DIR__ . '/../temp/stream.txt', 'r');
        $stream = Stream::create($file);
        $string = Stream::copyToString($stream);
        var_dump($string);
        self::assertIsString($string);
    }

    public function testCopyToStream()
    {
        $file1 = fopen(__DIR__ . '/../temp/stream.txt', 'r');
        $stream1 = Stream::create($file1);
        $file2 = fopen(__DIR__ . '/../temp/stream2.txt', 'w+');
        $stream2 = Stream::create($file2);
        Stream::copyToStream($stream1, $stream2);
        self::assertTrue(true);
    }
}
