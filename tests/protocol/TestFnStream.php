<?php

namespace protocol;

use fize\stream\protocol\FnStream;
use fize\stream\Stream;
use PHPUnit\Framework\TestCase;

class TestFnStream extends TestCase
{

    public function test__construct()
    {
        $methods = [];
        $stream = new FnStream($methods);
        self::assertIsObject($stream);
    }

    public function test__destruct()
    {
        $methods = [
            'close' => function () {
                echo '123456';
            }
        ];
        $stream = new FnStream($methods);
        self::assertIsObject($stream);
    }

    public function test__wakeup()
    {
        $methods = [];
        $stream = new FnStream($methods);
        // FnStream无法序列化、反序列化
//        $data = serialize($stream);
//        var_dump($data);
//        $object = unserialize($data);
//        var_dump($object);
        self::assertIsObject($stream);
    }

    public function test__toString()
    {
        $methods = [
            '__toString' => function () {
                return '123456';
            }
        ];
        $stream = new FnStream($methods);
        $str = (string)$stream;
        self::assertEquals('123456', $str);
    }

    public function testClose()
    {
        $methods = [
            'close' => function () {
                echo '123456';
            }
        ];
        $stream = new FnStream($methods);
        self::assertIsObject($stream);
    }

    public function testDetach()
    {
        $methods = [
            'detach' => function () {
                echo 'detach';
            }
        ];
        $stream = new FnStream($methods);
        self::assertIsObject($stream);
    }

    public function testGetSize()
    {
        $methods = [
            'getSize' => function () {
                return 5;
            }
        ];
        $stream = new FnStream($methods);
        $size = $stream->getSize();
        self::assertEquals(5, $size);
    }

    public function testTell()
    {
        $methods = [
            'tell' => function () {
                return 5;
            }
        ];
        $stream = new FnStream($methods);
        $tell = $stream->tell();
        self::assertEquals(5, $tell);
    }

    public function testEof()
    {
        $methods = [
            'eof' => function () {
                return false;
            }
        ];
        $stream = new FnStream($methods);
        $eof = $stream->eof();
        self::assertFalse($eof);
    }

    public function testIsSeekable()
    {
        $methods = [
            'isSeekable' => function () {
                return false;
            }
        ];
        $stream = new FnStream($methods);
        $seekable = $stream->isSeekable();
        self::assertFalse($seekable);
    }

    public function testSeek()
    {
        $methods = [
            'seek' => function ($offset, $whence = SEEK_SET) {
                var_dump($offset);
                var_dump($whence);
            }
        ];
        $stream = new FnStream($methods);
        $stream->seek(11);
        self::assertIsObject($stream);
    }

    public function testRewind()
    {
        $methods = [
            'rewind' => function () {
                echo 'rewind';
            }
        ];
        $stream = new FnStream($methods);
        $stream->rewind();
        self::assertIsObject($stream);
    }

    public function testIsWritable()
    {
        $methods = [
            'isWritable' => function () {
                return true;
            }
        ];
        $stream = new FnStream($methods);
        $writable = $stream->isWritable();
        self::assertTrue($writable);
    }

    public function testWrite()
    {
        $methods = [
            'write' => function ($string) {
                echo $string;
                return strlen($string);
            }
        ];
        $stream = new FnStream($methods);
        $len = $stream->write('123456');
        self::assertEquals($len, strlen('123456'));
    }

    public function testIsReadable()
    {
        $methods = [
            'isReadable' => function () {
                return true;
            }
        ];
        $stream = new FnStream($methods);
        $bool = $stream->isReadable();
        self::assertTrue($bool);
    }

    public function testRead()
    {
        $methods = [
            'read' => function ($length) {
                return (string)$length;
            }
        ];
        $stream = new FnStream($methods);
        $str = $stream->read(10);
        self::assertEquals('10', $str);
    }

    public function testGetContents()
    {
        $methods = [
            'getContents' => function () {
                return '123456';
            }
        ];
        $stream = new FnStream($methods);
        $str = $stream->getContents();
        self::assertEquals('123456', $str);
    }

    public function testGetMetadata()
    {
        $methods = [
            'getMetadata' => function ($key = null) {
                $metas = [
                    'name' => 'FnStream',
                    'type' => 'Fize'
                ];
                if (is_null($key)) {
                    return $metas;
                }
                if (!isset($metas[$key])) {
                    return null;
                }
                return $metas[$key];
            }
        ];
        $stream = new FnStream($methods);
        $metas = $stream->getMetadata();
        var_dump($metas);
        self::assertIsArray($metas);
        $name = $stream->getMetadata('name');
        var_dump($name);
        self::assertEquals('FnStream', $name);
        $und = $stream->getMetadata('und');
        self::assertNull($und);
    }

    public function testDecorate()
    {
        $resource = fopen(__DIR__ . '/../../temp/stream.txt', 'r');
        $stream1 = new Stream($resource);
        $bool1 = $stream1->isWritable();
        self::assertFalse($bool1);
        $methods = [
            'isWritable' => function () {
                return true;
            }
        ];
        $stream2 = FnStream::decorate($stream1, $methods);
        $bool2 = $stream2->isWritable();
        self::assertTrue($bool2);
        $stream2->rewind();
        $content = $stream2->getContents();
        var_dump($content);
    }
}
