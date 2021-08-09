<?php

namespace protocol;

use fize\stream\protocol\AppendStream;
use fize\stream\protocol\NoSeekStream;
use fize\stream\StreamFactory;
use PHPUnit\Framework\TestCase;

class TestAppendStream extends TestCase
{

    public function test__construct()
    {
        $factory = new StreamFactory();
        $str1 = '123';
        $stream1 = $factory->createStream($str1);
        $str2 = '456';
        $stream2 = $factory->createStream($str2);
        $stream = new AppendStream([$stream1, $stream2]);
        var_dump($stream);
        self::assertIsObject($stream);
    }

    public function test__toString()
    {
        $factory = new StreamFactory();
        $str1 = '123';
        $stream1 = $factory->createStream($str1);
        $str2 = '456';
        $stream2 = $factory->createStream($str2);
        $stream = new AppendStream([$stream1, $stream2]);
        $str = (string)$stream;
        var_dump($str);
        self::assertEquals('123456', $str);
    }

    public function testClose()
    {
        $factory = new StreamFactory();
        $str1 = '123';
        $stream1 = $factory->createStream($str1);
        $str2 = '456';
        $stream2 = $factory->createStream($str2);
        $stream = new AppendStream([$stream1, $stream2]);
        $stream->close();
        var_dump($stream);
        self::assertIsObject($stream);
    }

    public function testDetach()
    {
        $factory = new StreamFactory();
        $str1 = '123';
        $stream1 = $factory->createStream($str1);
        $str2 = '456';
        $stream2 = $factory->createStream($str2);
        $stream = new AppendStream([$stream1, $stream2]);
        $result = $stream->detach();
        self::assertNull($result);
    }

    public function testGetSize()
    {
        $factory = new StreamFactory();
        $str1 = '123';
        $stream1 = $factory->createStream($str1);
        $str2 = '456';
        $stream2 = $factory->createStream($str2);
        $stream = new AppendStream([$stream1, $stream2]);
        $size = $stream->getSize();
        var_dump($size);
        self::assertEquals(6, $size);
    }

    public function testTell()
    {
        $factory = new StreamFactory();
        $str1 = '123';
        $stream1 = $factory->createStream($str1);
        $str2 = '456';
        $stream2 = $factory->createStream($str2);
        $stream = new AppendStream([$stream1, $stream2]);
        $stream->rewind();
        $pos1 = $stream->tell();
        var_dump($pos1);
        self::assertEquals(0, $pos1);
        $stream->read(1);
        $pos2 = $stream->tell();
        var_dump($pos2);
        self::assertEquals(1, $pos2);
        $stream->read(4);
        $pos3 = $stream->tell();
        var_dump($pos3);
        self::assertEquals(5, $pos3);
    }

    public function testEof()
    {
        $factory = new StreamFactory();
        $str1 = '123';
        $stream1 = $factory->createStream($str1);
        $str2 = '456';
        $stream2 = $factory->createStream($str2);
        $stream = new AppendStream([$stream1, $stream2]);
        $stream->read(2);
        $eof1 = $stream->eof();
        self::assertFalse($eof1);
        $stream->read(5);
        $eof2 = $stream->eof();
        self::assertTrue($eof2);
    }

    public function testIsSeekable()
    {
        $factory = new StreamFactory();
        $str1 = '123';
        $stream1 = $factory->createStream($str1);
        $str2 = '456';
        $stream2 = $factory->createStream($str2);
        $stream = new AppendStream([$stream1, $stream2]);
        $seekable = $stream->isSeekable();
        self::assertTrue($seekable);

        $factory = new StreamFactory();
        $str1 = '123';
        $stream1 = $factory->createStream($str1);
        $str2 = '456';
        $stream2 = $factory->createStream($str2);
        $stream2 = new NoSeekStream($stream2);
        $stream = new AppendStream([$stream1, $stream2]);
        $seekable = $stream->isSeekable();
        self::assertFalse($seekable);
    }

    public function testSeek()
    {
        $factory = new StreamFactory();
        $str1 = '123';
        $stream1 = $factory->createStream($str1);
        $str2 = '456';
        $stream2 = $factory->createStream($str2);
        $stream = new AppendStream([$stream1, $stream2]);
        $stream->seek(3);
        $str = $stream->read(1);
        self::assertEquals('4', $str);
    }

    public function testRewind()
    {
        $factory = new StreamFactory();
        $str1 = '123';
        $stream1 = $factory->createStream($str1);
        $str2 = '456';
        $stream2 = $factory->createStream($str2);
        $stream = new AppendStream([$stream1, $stream2]);
        $stream->seek(3);
        $stream->rewind();
        $str = $stream->read(1);
        self::assertEquals('1', $str);
    }

    public function testIsWritable()
    {
        $factory = new StreamFactory();
        $str1 = '123';
        $stream1 = $factory->createStream($str1);
        $str2 = '456';
        $stream2 = $factory->createStream($str2);
        $stream = new AppendStream([$stream1, $stream2]);
        $writable = $stream->isWritable();
        self::assertFalse($writable);
    }

    public function testWrite()
    {
        $factory = new StreamFactory();
        $str1 = '123';
        $stream1 = $factory->createStream($str1);
        $str2 = '456';
        $stream2 = $factory->createStream($str2);
        $stream = new AppendStream([$stream1, $stream2]);
        //AppendStream 不允许写入
        //$stream->write('123456');
        self::assertIsObject($stream);
    }

    public function testIsReadable()
    {
        $factory = new StreamFactory();
        $str1 = '123';
        $stream1 = $factory->createStream($str1);
        $str2 = '456';
        $stream2 = $factory->createStream($str2);
        $stream = new AppendStream([$stream1, $stream2]);
        $readable = $stream->isReadable();
        self::assertTrue($readable);
    }

    public function testRead()
    {
        $factory = new StreamFactory();
        $str1 = '123';
        $stream1 = $factory->createStream($str1);
        $str2 = '456';
        $stream2 = $factory->createStream($str2);
        $stream = new AppendStream([$stream1, $stream2]);
        $str = $stream->read(6);
        self::assertEquals('123456', $str);
    }

    public function testGetContents()
    {
        $factory = new StreamFactory();
        $str1 = '123';
        $stream1 = $factory->createStream($str1);
        $str2 = '456';
        $stream2 = $factory->createStream($str2);
        $stream = new AppendStream([$stream1, $stream2]);
        $contents = $stream->getContents();
        self::assertEquals('123456', $contents);
    }

    public function testGetMetadata()
    {
        $factory = new StreamFactory();
        $str1 = '123';
        $stream1 = $factory->createStream($str1);
        $str2 = '456';
        $stream2 = $factory->createStream($str2);
        $stream = new AppendStream([$stream1, $stream2]);
        $metas = $stream->getMetadata();
        self::assertEquals([], $metas);
        $und = $stream->getMetadata('und');
        self::assertNull($und);
    }

    public function testAddStream()
    {
        $factory = new StreamFactory();
        $str1 = '123';
        $stream1 = $factory->createStream($str1);
        $str2 = '456';
        $stream2 = $factory->createStream($str2);
        $stream = new AppendStream([$stream1, $stream2]);
        $str3 = '789';
        $stream3 = $factory->createStream($str3);
        $stream->addStream($stream3);
        $contents = $stream->getContents();
        self::assertEquals('123456789', $contents);
    }
}
