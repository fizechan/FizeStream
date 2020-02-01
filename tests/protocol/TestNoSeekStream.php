<?php

namespace protocol;

use fize\stream\protocol\NoSeekStream;
use fize\stream\Stream;
use PHPUnit\Framework\TestCase;

class TestNoSeekStream extends TestCase
{

    public function test__construct()
    {
        $stream = new Stream('php://temp', 'r+');
        $stream = new NoSeekStream($stream);
        self::assertIsObject($stream);
    }

    public function testSeek()
    {
        $stream = new Stream('php://temp', 'r+');
        $stream = new NoSeekStream($stream);
        //开启以下注释可以看到出错效果
        //$stream->seek(5);
        self::assertIsObject($stream);
    }

    public function testIsSeekable()
    {
        $stream = new Stream('php://temp', 'r+');
        $stream = new NoSeekStream($stream);
        $seek = $stream->isSeekable();
        self::assertFalse($seek);
    }
}
