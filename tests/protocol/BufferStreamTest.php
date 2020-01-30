<?php

namespace protocol;

use fize\stream\protocol\BufferStream;
use PHPUnit\Framework\TestCase;

class BufferStreamTest extends TestCase
{

    public function test__construct()
    {
        $stream = new BufferStream(100);
        var_dump($stream);
        self::assertIsObject($stream);
    }

    public function test__toString()
    {
        $stream = new BufferStream(100);
        $stream->write('123456789');
        $stream->write('123456789');
        $str = (string)$stream;
        self::assertEquals($str, '123456789123456789');
    }

    public function testClose()
    {
        $stream = new BufferStream(100);
        $stream->write('123456789');
        $stream->write('123456789');
        $stream->close();
        $str = (string)$stream;
        self::assertEquals($str, '');
    }

    public function testDetach()
    {
        $stream = new BufferStream(100);
        $stream->write('123456789');
        $stream->write('123456789');
        $stream->detach();
        $str = (string)$stream;
        self::assertEquals($str, '');
    }

    public function testGetSize()
    {
        $stream = new BufferStream(100);
        $stream->write('123456789');
        $size = $stream->getSize();
        var_dump($size);
        self::assertEquals($size, 9);
    }

    public function testTell()
    {
        $stream = new BufferStream(100);
        //BufferStream不能随机读
        //$stream->tell();
        self::assertIsObject($stream);
    }

    public function testEof()
    {
        $stream = new BufferStream(100);
        $eof1 = $stream->eof();
        self::assertTrue($eof1);
        $stream->write('123456');
        $eof2 = $stream->eof();
        self::assertFalse($eof2);
    }

    public function testIsSeekable()
    {
        $stream = new BufferStream(100);
        $seekable = $stream->isSeekable();
        self::assertFalse($seekable);
    }

    public function testSeek()
    {
        $stream = new BufferStream(100);
        //BufferStream不能随机读
        //$stream->seek(10);
        self::assertIsObject($stream);
    }

    public function testRewind()
    {
        $stream = new BufferStream(100);
        //BufferStream不能随机读
        //$stream->rewind();
        self::assertIsObject($stream);
    }

    public function testIsWritable()
    {
        $stream = new BufferStream(100);
        $writable = $stream->isWritable();
        self::assertTrue($writable);
    }

    public function testWrite()
    {
        $stream = new BufferStream(10);
        $bint1 = $stream->write('0123456789');
        self::assertEquals(10, $bint1);
        $bint2 = $stream->write('1234567890');
        self::assertEquals(0, $bint2);
    }

    public function testIsReadable()
    {
        $stream = new BufferStream(100);
        $readable = $stream->isReadable();
        self::assertTrue($readable);
    }

    public function testRead()
    {
        $stream = new BufferStream(10);
        $stream->write('0123456789');
        $str1 = $stream->read(5);
        self::assertEquals($str1, '01234');
        $str2 = $stream->read(5);
        self::assertEquals($str2, '56789');
    }

    public function testGetContents()
    {
        $stream = new BufferStream(10);
        $stream->write('0123456789');
        $stream->read(5);
        $str1 = $stream->getContents();
        self::assertEquals($str1, '56789');
    }

    public function testGetMetadata()
    {
        $stream = new BufferStream(10);
        $metas = $stream->getMetadata();
        var_dump($metas);
        self::assertIsArray($metas);
        $hwm = $stream->getMetadata('hwm');
        self::assertIsInt($hwm);
        $und = $stream->getMetadata('und');
        self::assertNull($und);
    }
}
