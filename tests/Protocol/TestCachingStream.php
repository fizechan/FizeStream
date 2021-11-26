<?php

namespace Tests\Protocol;

use Fize\Stream\Protocol\CachingStream;
use Fize\Stream\Protocol\LazyOpenStream;
use Fize\Stream\Protocol\PumpStream;
use Fize\Stream\Stream;
use PHPUnit\Framework\TestCase;

class TestCachingStream extends TestCase
{

    public function test__construct()
    {
        $stream = new CachingStream(new LazyOpenStream('php://input', 'r+'));
        var_dump($stream);
        self::assertIsObject($stream);
    }

    public function testClose()
    {
        $stream = new CachingStream(new LazyOpenStream('php://input', 'r+'));
        $stream->close();
        var_dump($stream);
        self::assertIsObject($stream);
    }

    public function testGetSize()
    {
        $resource = fopen('php://temp', 'r+');
        $stream = new CachingStream(new Stream($resource));
        $stream->write('0123456789');
        $size = $stream->getSize();
        var_dump($size);
        self::assertEquals(10, $size);
    }

    public function testEof()
    {
        $resource = fopen('php://temp', 'r+');
        $stream = new CachingStream(new Stream($resource));
        $stream->write('0123456789');
        $stream->seek(11);
        $iseof = $stream->eof();
        var_dump($iseof);
        self::assertTrue($iseof);
    }

    public function testSeek()
    {
        $stream = new PumpStream(function ($length) {
            return "$length";
        });
        $stream->close();
        $stream = new CachingStream($stream);
        $stream->write('0123456789');
        $stream->seek(20, SEEK_END);
        self::assertIsObject($stream);
    }

    public function testWrite()
    {
        $stream = new PumpStream(function ($length) {
            return "$length";
        });
        $stream->close();
        $stream = new CachingStream($stream);
        $bytes = $stream->write('0123456789');
        self::assertEquals(10, $bytes);

    }

    public function testRead()
    {
        $stream = new PumpStream(function ($length) {
            return "$length";
        });
        $stream->close();
        $stream = new CachingStream($stream);
        $stream->write('0123456789');
        $stream->rewind();
        $str = $stream->read(20);
        var_dump($str);
        self::assertEquals('0123456789', $str);
    }

    public function test__toString()
    {
        $stream = new PumpStream(function ($length) {
            return "$length";
        });
        $stream->close();
        $stream = new CachingStream($stream);
        $stream->write('0123456789');
        $str = (string)$stream;
        var_dump($str);
        self::assertEquals('0123456789', $str);
    }

    public function testDetach()
    {
        $stream = new CachingStream(new LazyOpenStream('php://input', 'r+'));
        $stream->detach();
        var_dump($stream);
        self::assertIsObject($stream);
    }

    public function testTell()
    {
        $stream = new PumpStream(function ($length) {
            return "$length";
        });
        $stream->close();
        $stream = new CachingStream($stream);
        $stream->write('0123456789');
        $tell = $stream->tell();
        var_dump($tell);
        self::assertIsInt($tell);
    }

    public function testIsSeekable()
    {
        $stream = new PumpStream(function ($length) {
            return "$length";
        });
        $stream->close();
        $stream = new CachingStream($stream);
        $stream->write('0123456789');
        $seekable = $stream->isSeekable();
        var_dump($seekable);
        self::assertTrue($seekable);
    }

    public function testRewind()
    {
        $stream = new PumpStream(function ($length) {
            return "$length";
        });
        $stream->close();
        $stream = new CachingStream($stream);
        $stream->write('0123456789');
        $stream->rewind();
        $tell = $stream->tell();
        var_dump($tell);
        self::assertEquals(0, $tell);
    }

    public function testIsWritable()
    {
        $stream = new PumpStream(function ($length) {
            return "$length";
        });
        $stream->close();
        $stream = new CachingStream($stream);
        $writable = $stream->isWritable();
        self::assertTrue($writable);
    }

    public function testIsReadable()
    {
        $stream = new PumpStream(function ($length) {
            return "$length";
        });
        $stream->close();
        $stream = new CachingStream($stream);
        $readable = $stream->isReadable();
        self::assertTrue($readable);
    }

    public function testGetContents()
    {
        $stream = new CachingStream(new LazyOpenStream('php://temp', 'r+'));
        $stream->write('0123456789');
        $stream->rewind();
        $contents = $stream->getContents();
        var_dump($contents);
        self::assertEquals('0123456789', $contents);
    }

    public function testGetMetadata()
    {
        $stream = new CachingStream(new LazyOpenStream('php://temp', 'r+'));
        $metas = $stream->getMetadata();
        var_dump($metas);
        self::assertIsArray($metas);
    }
}
