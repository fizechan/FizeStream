<?php


use fize\io\Stream as StreamIO;
use fize\stream\Stream;
use fize\io\File;
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{

    public function test__construct()
    {
        $file1 = new File(__DIR__ . '/../temp/stream.txt', 'w+');
        $stream1 = new Stream($file1);
        self::assertIsObject($stream1);

        $file = new File(__DIR__ . '/../temp/stream.txt', 'w+');
        $file->open();
        var_dump($file->getStream());
        $stream = new Stream($file->getStream());
        self::assertIsObject($stream);
    }

    public function test__destruct()
    {
        $file = new File(__DIR__ . '/../temp/stream.txt', 'w+');
        $file->open();
        $stream = new Stream($file->getStream());
        unset($stream);
        self::assertTrue(true);
    }

    public function test__toString()
    {
        $file = new File(__DIR__ . '/../temp/stream.txt', 'r');
        $stream = new Stream($file);
        $string = (string)$stream;
        var_dump($string);
        self::assertIsString($string);
    }

    public function testClose()
    {

    }

    public function testEof()
    {

    }

    public function testIsWritable()
    {

    }

    public function testGetMetadata()
    {

    }





    public function testTell()
    {

    }

    public function testRead()
    {

    }



    public function testDetach()
    {

    }

    public function testIsSeekable()
    {

    }

    public function testRewind()
    {

    }

    public function testCopyToString()
    {

    }

    public function testIsReadable()
    {

    }

    public function testCreate()
    {

    }

    public function testGetContents()
    {

    }



    public function testGetSize()
    {

    }

    public function testWrite()
    {

    }

    public function testCopyToStream()
    {

    }

    public function testSeek()
    {

    }
}
