<?php


use fize\stream\Stream;
use fize\io\File;
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{

    public function test__construct()
    {
        $file1 = new File(__DIR__ . '/../temp/stream.txt', 'w+');
        $stream1 = new Stream($file1);
        self::assertIsObject($stream1);

        $file = new File(__DIR__ . '/../temp/stream.txt', 'w+');
        $file->open();
        var_dump($file->getStream());
        $stream = new Stream($file->getStream());
        self::assertIsObject($stream);
    }

    public function test__destruct()
    {
        $file = new File(__DIR__ . '/../temp/stream.txt', 'w+');
        $file->open();
        $stream = new Stream($file->getStream());
        unset($stream);
        self::assertTrue(true);
    }

    public function test__toString()
    {
        $file = new File(__DIR__ . '/../temp/stream.txt', 'r');
        $stream = new Stream($file);
        $string = (string)$stream;
        var_dump($string);
        self::assertIsString($string);
    }

    public function testClose()
    {
        $file = new File(__DIR__ . '/../temp/stream.txt', 'w+');
        $file->open();
        $stream = new Stream($file->getStream());
        $stream->close();
        self::assertTrue(true);
    }

    public function testDetach()
    {
        $file = new File(__DIR__ . '/../temp/stream.txt', 'w+');
        $file->open();
        $stream = new Stream($file->getStream());
        $resource = $stream->detach();
        var_dump($resource);
        var_dump($file->getStream());
        self::assertTrue(true);
    }

    public function testGetSize()
    {
        $file = new File(__DIR__ . '/../temp/stream.txt', 'r');
        $file->open();
        $stream = new Stream($file->getStream());
        $size = $stream->getSize();
        var_dump($size);
        self::assertIsInt($size);
    }

    public function testTell()
    {
        $file = new File(__DIR__ . '/../temp/stream.txt', 'r');
        $stream = new Stream($file);
        $pos1 = $stream->tell();
        self::assertEquals($pos1, 0);
        $stream->read(100);
        $pos2 = $stream->tell();
        var_dump($pos2);
        self::assertNotEquals(0, $pos2);
    }

    public function testEof()
    {
        $file = new File(__DIR__ . '/../temp/stream.txt', 'r');
        $stream = new Stream($file);
        $eof1 = $stream->eof();
        self::assertFalse($eof1);
        $stream->getContents();
        $eof2 = $stream->eof();
        self::assertTrue($eof2);
    }

    public function testIsSeekable()
    {
        $file1 = new File(__DIR__ . '/../temp/stream.txt', 'r');
        $stream1 = new Stream($file1);
        $seek1 = $stream1->isSeekable();
        self::assertTrue($seek1);
    }

    public function testSeek()
    {
        $file = new File(__DIR__ . '/../temp/stream.txt', 'r');
        $stream = new Stream($file);
        $stream->seek(100);
        $string = $stream->read(10);
        var_dump($string);
        self::assertTrue(true);
    }

    public function testRewind()
    {
        $file = new File(__DIR__ . '/../temp/stream.txt', 'r');
        $stream = new Stream($file);
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
        $file1 = new File(__DIR__ . '/../temp/stream1.txt', 'w');
        $stream1 = new Stream($file1);
        $seek1 = $stream1->isWritable();
        self::assertTrue($seek1);

        $file2 = new File(__DIR__ . '/../temp/stream.txt', 'r');
        $stream2 = new Stream($file2);
        $seek2 = $stream2->isWritable();
        self::assertFalse($seek2);
    }

    public function testWrite()
    {
        $file1 = new File(__DIR__ . '/../temp/stream1.txt', 'w');
        $stream1 = new Stream($file1);
        $ink1 = $stream1->write('哈哈哈哈！~！~！');
        self::assertIsInt($ink1);
    }

    public function testIsReadable()
    {
        $file1 = new File(__DIR__ . '/../temp/stream1.txt', 'w');
        $stream1 = new Stream($file1);
        $result1 = $stream1->isReadable();
        self::assertFalse($result1);

        $file2 = new File(__DIR__ . '/../temp/stream.txt', 'r');
        $stream2 = new Stream($file2);
        $result2 = $stream2->isReadable();
        self::assertTrue($result2);
    }

    public function testRead()
    {
        $file2 = new File(__DIR__ . '/../temp/stream.txt', 'r');
        $stream2 = new Stream($file2);
        $string2 = $stream2->read(100);
        var_dump($string2);
        self::assertIsString($string2);
    }

    public function testGetContents()
    {
        $file = new File(__DIR__ . '/../temp/stream.txt', 'r');
        $stream = new Stream($file);
        $contents = $stream->getContents();
        var_dump($contents);
        self::assertIsString($contents);
    }

    public function testGetMetadata()
    {
        $file = new File(__DIR__ . '/../temp/stream.txt', 'r');
        $stream = new Stream($file);
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
        //$stream = Stream::create(fopen('php://temp', 'r+'));

        //$file = new File(__DIR__ . '/../temp/stream2.txt', 'w+');
        //$stream = Stream::create($file);

        $stream = Stream::create(new File(__DIR__ . '/../temp/stream2.txt', 'w+'));
        //var_dump($stream);
        $stream->write('abc123');
        $stream->close();
        self::assertIsObject($stream);
    }

    public function testCopyToString()
    {
        $stream = Stream::create(new File(__DIR__ . '/../temp/stream.txt', 'r'));
        $string = Stream::copyToString($stream);
        var_dump($string);
        self::assertIsString($string);
    }

    public function testCopyToStream()
    {
        $stream1 = Stream::create(new File(__DIR__ . '/../temp/stream.txt', 'r'));
        $stream2 = Stream::create(new File(__DIR__ . '/../temp/stream2.txt', 'w+'));
        Stream::copyToStream($stream1, $stream2);
        self::assertTrue(true);
    }


}
