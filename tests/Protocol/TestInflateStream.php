<?php

namespace Tests\Protocol;

use Fize\Stream\Protocol\InflateStream;
use Fize\Stream\Stream;
use PHPUnit\Framework\TestCase;

class TestInflateStream extends TestCase
{

    public function test__construct()
    {
        $resource = fopen(__DIR__ . '/../../temp/stream3.txt', 'w+');
        $stream = new Stream($resource);
        $stream = new InflateStream($stream);
        var_dump($stream);
        self::assertIsObject($stream);
    }
}
