<?php

namespace protocol;

use fize\stream\protocol\CachingStream;
use fize\stream\protocol\LazyOpenStream;
use fize\stream\Stream;
use fize\io\File;
use PHPUnit\Framework\TestCase;

class CachingStreamTest extends TestCase
{

    public function test__construct()
    {
        $stream = new CachingStream(new LazyOpenStream('php://input', 'r+'));
        var_dump($stream);
        self::assertIsObject($stream);
    }

    public function testClose()
    {
        $stream = new CachingStream(new LazyOpenStream('php://input', 'r+'));
        $stream->close();
        var_dump($stream);
        self::assertIsObject($stream);
    }

    public function testGetSize()
    {
        $file = new File('php://temp', 'r+');
//        $file->write('123456');
//        $tell = $file->tell();
//        var_dump($tell);
//        die();
        $stream = new CachingStream(new Stream($file));
        //$stream = new CachingStream(new Stream(fopen('php://temp', 'r+')));
        $stream->write('0123456789');
        $size = $stream->getSize();
        var_dump($size);
        self::assertEquals(10, $size);
    }

    public function testSeek()
    {

    }

    public function testRead()
    {

    }

    public function testDetach()
    {

    }

    public function testTell()
    {

    }

    public function testEof()
    {

    }

    public function testIsSeekable()
    {

    }

    public function testGetMetadata()
    {

    }

    public function test__toString()
    {

    }

    public function testRewind()
    {

    }

    public function testWrite()
    {

    }

    public function testIsReadable()
    {

    }



    public function testGetContents()
    {

    }





    public function testIsWritable()
    {

    }
}
