<?php

namespace Tests\Protocol;

use Fize\Stream\Protocol\MultipartStream;
use PHPUnit\Framework\TestCase;

class TestMultipartStream extends TestCase
{

    public function test__construct()
    {
        $elements = [
            [
                'contents' => 'for test1',
                'name'     => 'test1'
            ],
            [
                'contents' => 'for test2',
                'name'     => 'test2'
            ]
        ];
        $boundary = "\r\n\r\n";
        $stream = new MultipartStream($elements, $boundary);
        var_dump($stream);
        self::assertIsObject($stream);
    }

    public function testGetBoundary()
    {
        $elements = [
            [
                'contents' => 'for test1',
                'name'     => 'test1'
            ],
            [
                'contents' => 'for test2',
                'name'     => 'test2'
            ]
        ];
        $boundary = "|";
        $stream = new MultipartStream($elements, $boundary);
        $boundary = $stream->getBoundary();
        self::assertEquals('|', $boundary);
    }

    public function testIsWritable()
    {
        $elements = [
            [
                'contents' => 'for test1',
                'name'     => 'test1'
            ],
            [
                'contents' => 'for test2',
                'name'     => 'test2'
            ]
        ];
        $boundary = "|";
        $stream = new MultipartStream($elements, $boundary);
        $writable = $stream->isWritable();
        self::assertFalse($writable);
    }
}
