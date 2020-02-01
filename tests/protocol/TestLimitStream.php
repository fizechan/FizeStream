<?php

namespace protocol;

use fize\stream\protocol\LimitStream;
use fize\stream\Stream;
use PHPUnit\Framework\TestCase;

class TestLimitStream extends TestCase
{

    public function test__construct()
    {
        $stream = new Stream('php://temp', 'r+');
        $stream->write('123456789');
        $stream = new LimitStream($stream, 2);
        var_dump($stream);
        self::assertIsObject($stream);
    }

    public function testEof()
    {
        $stream = new Stream('php://temp', 'r+');
        $stream->write('123456789');
        $stream = new LimitStream($stream, 2);
        $stream->seek(3);
        $bool = $stream->eof();
        self::assertTrue($bool);
    }

    public function testGetSize()
    {
        $stream = new Stream('php://temp', 'r+');
        $stream->write('123456789');
        $stream = new LimitStream($stream, 2);
        $size = $stream->getSize();
        self::assertEquals(2, $size);
    }

    public function testSeek()
    {
        $stream = new Stream('php://temp', 'r+');
        $stream->write('123456789');
        $stream = new LimitStream($stream, 2);
        $stream->seek(5);
        $str = $stream->read(1);
        var_dump($str);
        self::assertEquals('', $str);
    }

    public function testTell()
    {
        $stream = new Stream('php://temp', 'r+');
        $stream->write('123456789');
        $stream = new LimitStream($stream, 2);
        $stream->seek(5);
        $stream->read(1);
        $tell = $stream->tell();
        var_dump($tell);
        self::assertEquals(2, $tell);
    }

    public function testRead()
    {
        $stream = new Stream('php://temp', 'r+');
        $stream->write('123456789');
        $stream = new LimitStream($stream, 2);
        $str = $stream->read(1);
        var_dump($str);
        self::assertEquals('1', $str);
    }
}
