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

    public function test_stream_open()
    {
        $sw = new StreamWrapper();
        $bool = $sw->stream_open(__DIR__ . '/../temp/stream3.txt', 'w+', STREAM_REPORT_ERRORS);
        var_dump($bool);
        self::assertTrue($bool);
        $sw->stream_close();
        $bool2 = $sw->stream_open(__DIR__ . '/../temp/stream3.txt', 'w+', STREAM_REPORT_ERRORS, $opened_path);
        var_dump($bool2);
        self::assertTrue($bool2);
        var_dump($opened_path);
        $sw->stream_close();
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

    public function testGets()
    {
        $wrappers = StreamWrapper::gets();
        var_dump($wrappers);
        self::assertIsArray($wrappers);
    }

    public function testRegister()
    {
        $rst = StreamWrapper::register('txt', TxtStreamWrapper::class);
        var_dump($rst);
        self::assertTrue($rst);
    }

    public function testRestore()
    {
        $existed = in_array('http', StreamWrapper::gets());
        if ($existed) {
            StreamWrapper::unregister('http');
        }
        $rst = StreamWrapper::register('http', TxtStreamWrapper::class);
        self::assertTrue($rst);
        $myvar = '';

        $fp = fopen('http://myvar', 'r+');

        fwrite($fp, "line1\n");
        fwrite($fp, "line2\n");
        fwrite($fp, "line3\n");

        rewind($fp);
        while (!feof($fp)) {
            echo fgets($fp);
        }
        fclose($fp);
        var_dump($myvar);

        $existed = in_array('http', StreamWrapper::gets());
        if ($existed) {
            $rst = StreamWrapper::restore('http');
            var_dump($rst);
            self::assertTrue($rst);
        }
        $rst = StreamWrapper::restore('http');
        var_dump($rst);
        self::assertTrue($rst);
    }

    public function testUnregister()
    {
        StreamWrapper::register('var', TxtStreamWrapper::class);
        $rst = StreamWrapper::unregister('var');
        var_dump($rst);
        self::assertTrue($rst);
    }
}

/**
 * 自定义封装协议
 */
class TxtStreamWrapper
{
    public const WRAPPER_NAME = 'callback';

    public $context;

    private $seek = 0;

    private $eof = false;

    private static $isRegistered = false;

    /**
     * 获取上下文
     * @param $cb
     * @return resource
     */
    public static function getContext($cb)
    {
        if (!self::$isRegistered) {
            stream_wrapper_register(self::WRAPPER_NAME, get_class());
            self::$isRegistered = true;
        }
        if (!is_callable($cb)) {
            throw new Exception('error on getContext');
        }
        return stream_context_create([self::WRAPPER_NAME => ['cb' => $cb]]);
    }

    public function stream_open($path, $mode, $options, &$opened_path): bool
    {
        var_dump($path);
        var_dump($mode);
        var_dump($options);
        $opened_path = '';
        var_dump($opened_path);
        return true;
    }

    public function stream_read($count): string
    {
        $this->seek = $this->seek + $count;
        if ($this->seek > 10) {
            $this->eof = true;
        }
        return (string)$this->seek;
    }

    public function stream_write($data): int
    {
        return strlen($data);
    }

    public function stream_seek($offset, $whence = 0): bool
    {
        var_dump($offset);
        var_dump($whence);
        return true;
    }

    public function stream_tell(): int
    {
        return 1;
    }

    public function stream_eof(): bool
    {
        return $this->eof;
    }
}
