<?php

namespace Tests\Protocol;

use Fize\Stream\Protocol\PumpStream;
use PHPUnit\Framework\TestCase;

class TestPumpStream extends TestCase
{

    public function test__construct()
    {
        $stream = new PumpStream(function ($length) {
            if ($length >= 10) {
                return null;
            }
            return (string)$length;
        });
        var_dump($stream);
        self::assertIsObject($stream);
    }

    public function test__toString()
    {
        $once = true;
        $stream = new PumpStream(function ($length) use (&$once) {
            var_dump($length);
            if (!$once) {
                return null;  //模拟结束
            }
            $once = false;
            return '123456789';
        });
        $str = (string)$stream;
        var_dump($str);
        self::assertEquals('123456789', $str);
    }

    public function testClose()
    {
        $once = true;
        $stream = new PumpStream(function ($length) use (&$once) {
            var_dump($length);
            if (!$once) {
                return null;  //模拟结束
            }
            $once = false;
            return '123456789';
        });
        $stream->close();
        var_dump($stream);
        self::assertIsObject($stream);
    }

    public function testDetach()
    {
        $once = true;
        $stream = new PumpStream(function ($length) use (&$once) {
            var_dump($length);
            if (!$once) {
                return null;  //模拟结束
            }
            $once = false;
            return '123456789';
        });
        $res = $stream->detach();
        self::assertNull($res);
    }

    public function testGetSize()
    {
        $options = [
            'size' => 9  //模拟size
        ];
        $once = true;
        $stream = new PumpStream(function ($length) use (&$once) {
            var_dump($length);
            if (!$once) {
                return null;  //模拟结束
            }
            $once = false;
            return '123456789';
        }, $options);
        $size = $stream->getSize();
        self::assertEquals(9, $size);
    }

    public function testTell()
    {
        $options = [
            'size' => 9  //模拟size
        ];
        $once = true;
        $stream = new PumpStream(function ($length) use (&$once) {
            var_dump($length);
            if (!$once) {
                return null;  //模拟结束
            }
            $once = false;
            return '123456789';
        }, $options);
        $content = $stream->read(2);
        var_dump($content);
        $tell = $stream->tell();
        var_dump($tell);
        self::assertIsInt($tell);
    }

    public function testEof()
    {
        $once = true;
        $stream = new PumpStream(function ($length) use (&$once) {
            var_dump($length);
            if (!$once) {
                return null;  //模拟结束
            }
            $once = false;
            return '123456789';
        });
        $content = $stream->read(10);
        var_dump($content);
        $eof = $stream->eof();
        self::assertTrue($eof);
    }

    public function testIsSeekable()
    {
        $once = true;
        $stream = new PumpStream(function ($length) use (&$once) {
            var_dump($length);
            if (!$once) {
                return null;  //模拟结束
            }
            $once = false;
            return '123456789';
        });
        $content = $stream->read(10);
        var_dump($content);
        $bool = $stream->isSeekable();
        self::assertFalse($bool);
    }

    public function testSeek()
    {
        $once = true;
        $stream = new PumpStream(function ($length) use (&$once) {
            var_dump($length);
            if (!$once) {
                return null;  //模拟结束
            }
            $once = false;
            return '123456789';
        });
        //开启以下注释将看到错误效果
        //$stream->seek(3);
        self::assertIsObject($stream);
    }

    public function testRewind()
    {
        $once = true;
        $stream = new PumpStream(function ($length) use (&$once) {
            var_dump($length);
            if (!$once) {
                return null;  //模拟结束
            }
            $once = false;
            return '123456789';
        });
        //开启以下注释将看到错误效果
        //$stream->rewind();
        self::assertIsObject($stream);
    }

    public function testIsWritable()
    {
        $once = true;
        $stream = new PumpStream(function ($length) use (&$once) {
            var_dump($length);
            if (!$once) {
                return null;  //模拟结束
            }
            $once = false;
            return '123456789';
        });
        $bool = $stream->isWritable();
        self::assertFalse($bool);
    }

    public function testWrite()
    {
        $once = true;
        $stream = new PumpStream(function ($length) use (&$once) {
            var_dump($length);
            if (!$once) {
                return null;  //模拟结束
            }
            $once = false;
            return '123456789';
        });
        //开启以下注释将看到错误效果
        //$stream->write('123');
        self::assertIsObject($stream);
    }

    public function testIsReadable()
    {
        $once = true;
        $stream = new PumpStream(function ($length) use (&$once) {
            var_dump($length);
            if (!$once) {
                return null;  //模拟结束
            }
            $once = false;
            return '123456789';
        });
        $bool = $stream->isReadable();
        self::assertTrue($bool);
    }

    public function testRead()
    {
        $once = true;
        $stream = new PumpStream(function ($length) use (&$once) {
            var_dump($length);
            if (!$once) {
                return null;  //模拟结束
            }
            $once = false;
            return '123456789';
        });
        $str = $stream->read(3);
        self::assertEquals('123', $str);
    }

    public function testGetContents()
    {
        $once = true;
        $stream = new PumpStream(function ($length) use (&$once) {
            var_dump($length);
            if (!$once) {
                return null;  //模拟结束
            }
            $once = false;
            return '123456789';
        });
        $str = $stream->getContents();
        self::assertEquals('123456789', $str);
    }

    public function testGetMetadata()
    {
        $options = [
            'metadata' => [
                'kkk' => 'for test'
            ]  //模拟metadata
        ];
        $once = true;
        $stream = new PumpStream(function ($length) use (&$once) {
            var_dump($length);
            if (!$once) {
                return null;  //模拟结束
            }
            $once = false;
            return '123456789';
        }, $options);
        $metas = $stream->getMetadata();
        var_dump($metas);
        self::assertIsArray($metas);
        $kkk = $stream->getMetadata('kkk');
        self::assertEquals('for test', $kkk);
        $und = $stream->getMetadata('und');
        self::assertNull($und);
    }
}
