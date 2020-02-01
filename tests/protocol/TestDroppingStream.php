<?php

namespace protocol;

use fize\stream\protocol\DroppingStream;
use fize\stream\Stream;
use PHPUnit\Framework\TestCase;

class TestDroppingStream extends TestCase
{

    public function test__construct()
    {
        $stream = new DroppingStream(new Stream('php://temp', 'r+'), 5);
        self::assertIsObject($stream);
    }

    public function testWrite()
    {
        $stream = new DroppingStream(new Stream('php://temp', 'r+'), 5);
        $stream->write('123456789');
        $stream->rewind();
        $content = $stream->getContents();
        var_dump($content);
        self::assertEquals('12345', $content);
    }
}
