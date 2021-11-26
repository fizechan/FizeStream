<?php

namespace Tests\Protocol;

use Fize\Stream\Protocol\DroppingStream;
use Fize\Stream\Stream;
use PHPUnit\Framework\TestCase;

class TestDroppingStream extends TestCase
{

    public function test__construct()
    {
        $resource = fopen('php://temp', 'r+');
        $stream = new DroppingStream(new Stream($resource), 5);
        self::assertIsObject($stream);
    }

    public function testWrite()
    {
        $resource = fopen('php://temp', 'r+');
        $stream = new DroppingStream(new Stream($resource), 5);
        $stream->write('123456789');
        $stream->rewind();
        $content = $stream->getContents();
        var_dump($content);
        self::assertEquals('12345', $content);
    }
}
