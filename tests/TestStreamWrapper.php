<?php


use fize\stream\StreamWrapper;
use fize\stream\Stream;
use PHPUnit\Framework\TestCase;


class TestStreamWrapper extends TestCase
{

    public function testGetResource()
    {
        $stream = new Stream(__DIR__ . '/../temp/stream.txt', 'r');
        $resource = StreamWrapper::getResource($stream);
        var_dump($resource);
        self::assertIsResource($resource);
    }

    public function testRegister()
    {
        StreamWrapper::register();
        self::assertTrue(true);
    }

    public function testCreateStreamContext()
    {
        $stream = new Stream(__DIR__ . '/../temp/stream.txt', 'r');
        $resource = StreamWrapper::createStreamContext($stream);
        var_dump($resource);
        self::assertIsResource($resource);
    }

    public function testStream_open()
    {
        $stream = new Stream(__DIR__ . '/../temp/stream3.txt', 'w+');
        $resource = StreamWrapper::getResource($stream);
        $stream = new Stream($resource);
        var_dump($stream);
        self::assertIsObject($stream);
    }

    public function testStream_read()
    {
        $stream = new Stream(__DIR__ . '/../temp/stream3.txt', 'r');
        $resource = StreamWrapper::getResource($stream);
        $stream = new Stream($resource);
        $content = $stream->read(6);
        var_dump($content);
        self::assertEquals('123456', $content);
    }

    public function testStream_write()
    {
        $stream = new Stream(__DIR__ . '/../temp/stream3.txt', 'w+');
        $resource = StreamWrapper::getResource($stream);
        $stream = new Stream($resource);
        $len = $stream->write('123');
        var_dump($len);
        self::assertEquals(3, $len);
    }

    public function testStream_tell()
    {
        $stream = new Stream(__DIR__ . '/../temp/stream3.txt', 'w+');
        $resource = StreamWrapper::getResource($stream);
        $stream = new Stream($resource);
        $stream->write('123');
        $tell = $stream->tell();
        var_dump($tell);
        self::assertEquals(3, $tell);
    }

    public function testStream_eof()
    {
        $stream = new Stream(__DIR__ . '/../temp/stream3.txt', 'r');
        $resource = StreamWrapper::getResource($stream);
        $stream = new Stream($resource);
        $eof = $stream->eof();
        var_dump($eof);
        self::assertFalse($eof);
    }

    public function testStream_seek()
    {
        $stream = new Stream(__DIR__ . '/../temp/stream3.txt', 'r');
        $resource = StreamWrapper::getResource($stream);
        $stream = new Stream($resource);
        $stream->seek(2);
        self::assertIsObject($stream);
    }

    public function testStream_cast()
    {
        StreamWrapper::register();
        $stream = new Stream(__DIR__ . '/../temp/stream3.txt', 'r');
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
        $stream = new Stream(__DIR__ . '/../temp/stream3.txt', 'r');
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
