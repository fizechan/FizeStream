<?php


use fize\stream\StreamWrapper;
use fize\stream\Stream;
use PHPUnit\Framework\TestCase;


class TestStreamWrapper extends TestCase
{

    public function testGet()
    {
        $resource = fopen(__DIR__ . '/../temp/stream3.txt', 'w+');
        $stream = new Stream($resource);
        $stream2 = StreamWrapper::get($stream);
        var_dump($stream2);
        self::assertIsObject($stream2);
    }

    public function testGetResource()
    {
        $resource = fopen(__DIR__ . '/../temp/stream3.txt', 'w+');
        $stream = new Stream($resource);
        $resource = StreamWrapper::getResource($stream);
        var_dump($resource);
        self::assertIsResource($resource);
    }

    public function testStream_open()
    {
        $resource = fopen(__DIR__ . '/../temp/stream3.txt', 'w+');
        $stream = new Stream($resource);
        $stream = StreamWrapper::get($stream);
        var_dump($stream);
        self::assertIsObject($stream);
    }

    public function testStream_read()
    {
        $resource = fopen(__DIR__ . '/../temp/stream3.txt', 'r');
        $stream = new Stream($resource);
        $stream = StreamWrapper::get($stream);
        $content = $stream->read(6);
        var_dump($content);
        self::assertEquals('123456', $content);
    }

    public function testStream_write()
    {
        $resource = fopen(__DIR__ . '/../temp/stream3.txt', 'w+');
        $stream = new Stream($resource);
        $stream = StreamWrapper::get($stream);
        $len = $stream->write('123');
        var_dump($len);
        self::assertEquals(3, $len);
    }

    public function testStream_tell()
    {
        $resource = fopen(__DIR__ . '/../temp/stream3.txt', 'w+');
        $stream = new Stream($resource);
        $stream = StreamWrapper::get($stream);
        $stream->write('123');
        $tell = $stream->tell();
        var_dump($tell);
        self::assertEquals(3, $tell);
    }

    public function testStream_eof()
    {
        $resource = fopen(__DIR__ . '/../temp/stream3.txt', 'r');
        $stream = new Stream($resource);
        $stream = StreamWrapper::get($stream);
        $eof = $stream->eof();
        var_dump($eof);
        self::assertFalse($eof);
    }

    public function testStream_seek()
    {
        $resource = fopen(__DIR__ . '/../temp/stream3.txt', 'r');
        $stream = new Stream($resource);
        $stream = StreamWrapper::get($stream);
        $stream->seek(2);
        self::assertIsObject($stream);
    }

    public function testStream_cast()
    {
        StreamWrapper::register();
        $resource = fopen(__DIR__ . '/../temp/stream3.txt', 'r');
        $stream = new Stream($resource);
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
        $resource = fopen(__DIR__ . '/../temp/stream3.txt', 'r');
        $stream = new Stream($resource);
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
