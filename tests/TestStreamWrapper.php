<?php

namespace Tests;

use Fize\Stream\Stream;
use Fize\Stream\StreamWrapper;
use PHPUnit\Framework\TestCase;

class TestStreamWrapper extends TestCase
{

    public function testGet()
    {
        $stream = new Stream();
        $stream->open(__DIR__ . '/../temp/stream3.txt', 'w+');
        $stream2 = StreamWrapper::get($stream);
        var_dump($stream2);
        self::assertIsObject($stream2);
    }

    public function testGetResource()
    {
        $stream = new Stream();
        $stream->open(__DIR__ . '/../temp/stream3.txt', 'w+');
        $resource = StreamWrapper::getResource($stream);
        var_dump($resource);
        self::assertIsResource($resource);
    }

    public function testStream_open()
    {
        $stream = new Stream();
        $stream->open(__DIR__ . '/../temp/stream3.txt', 'w+');
        $stream = StreamWrapper::get($stream);
        var_dump($stream);
        self::assertIsObject($stream);
    }

    public function testStream_read()
    {
        $stream = new Stream();
        $stream->open(__DIR__ . '/../temp/stream3.txt', 'r');
        $stream = StreamWrapper::get($stream);
        $content = $stream->read(6);
        var_dump($content);
        self::assertEquals('123456', $content);
    }

    public function testStream_write()
    {
        $stream = new Stream();
        $stream->open(__DIR__ . '/../temp/stream3.txt', 'w+');
        $stream = StreamWrapper::get($stream);
        $len = $stream->write('123');
        var_dump($len);
        self::assertEquals(3, $len);
    }

    public function testStream_tell()
    {
        $stream = new Stream();
        $stream->open(__DIR__ . '/../temp/stream3.txt', 'w+');
        $stream = StreamWrapper::get($stream);
        $stream->write('123');
        $tell = $stream->tell();
        var_dump($tell);
        self::assertEquals(3, $tell);
    }

    public function testStream_eof()
    {
        $stream = new Stream();
        $stream->open(__DIR__ . '/../temp/stream3.txt', 'r');
        $stream = StreamWrapper::get($stream);
        $eof = $stream->eof();
        var_dump($eof);
        self::assertFalse($eof);
    }

    public function testStream_seek()
    {
        $stream = new Stream();
        $stream->open(__DIR__ . '/../temp/stream3.txt', 'r');
        $stream = StreamWrapper::get($stream);
        $stream->seek(2);
        self::assertIsObject($stream);
    }

    public function testStream_cast()
    {
        StreamWrapper::register();
        $stream = new Stream();
        $stream->open(__DIR__ . '/../temp/stream3.txt', 'r');
        $stream = fopen('fize://stream', 'r', null, stream_context_create(['fize' => ['stream' => $stream]]));

        //开启以下注释可以看到效果
        //$read   = [$stream];
        //$write  = NULL;
        //$except = NULL;
        //$result = stream_select($read, $write, $except, 0);
        //var_dump($result);

        self::assertIsResource($stream);
    }

    public function testStream_stat()
    {
        StreamWrapper::register();
        $stream = new Stream();
        $stream->open(__DIR__ . '/../temp/stream3.txt', 'r');
        $stream = fopen('fize://stream', 'r', null, stream_context_create(['fize' => ['stream' => $stream]]));
        $stat = fstat($stream);
        var_dump($stat);
        self::assertIsArray($stat);
    }

    public function testUrl_stat()
    {
        StreamWrapper::register();
        $stat = stat('fize://stream');
        var_dump($stat);
        self::assertIsArray($stat);
    }
}
