<?php

namespace protocol;

use fize\stream\protocol\NoSeekStream;
use fize\stream\Stream;
use PHPUnit\Framework\TestCase;

class TestNoSeekStream extends TestCase
{

    public function test__construct()
    {
        $resource = fopen('php://temp', 'r+');
        $stream = new Stream($resource);
        $stream = new NoSeekStream($stream);
        self::assertIsObject($stream);
    }

    public function testSeek()
    {
        $resource = fopen('php://temp', 'r+');
        $stream = new Stream($resource);
        $stream = new NoSeekStream($stream);
        //开启以下注释可以看到出错效果
        //$stream->seek(5);
        self::assertIsObject($stream);
    }

    public function testIsSeekable()
    {
        $resource = fopen('php://temp', 'r+');
        $stream = new Stream($resource);
        $stream = new NoSeekStream($stream);
        $seek = $stream->isSeekable();
        self::assertFalse($seek);
    }
}
