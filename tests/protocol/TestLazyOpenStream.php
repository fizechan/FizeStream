<?php

namespace protocol;

use fize\stream\protocol\LazyOpenStream;
use PHPUnit\Framework\TestCase;

class TestLazyOpenStream extends TestCase
{

    public function test__construct()
    {
        $stream = new LazyOpenStream(__DIR__ . '/../../temp/stream3.txt', 'w+');
        var_dump($stream);
        self::assertIsObject($stream);
    }
}
