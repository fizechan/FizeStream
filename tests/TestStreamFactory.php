<?php

namespace Tests;

use Fize\Stream\Stream;
use Fize\Stream\StreamFactory;
use Iterator;
use PHPUnit\Framework\TestCase;

class TestStreamFactory extends TestCase
{

    public function testCreateStream()
    {
        $factory = new StreamFactory();
        $str = '0123456789';
        $stream = $factory->createStream($str);
        var_dump($stream);
        $content = $stream->getContents();
        var_dump($content);
        self::assertEquals('0123456789', $content);
        $stream->write('9876543210');
        $stream->rewind();
        $content = $stream->getContents();
        var_dump($content);
        self::assertEquals('01234567899876543210', $content);
        $writable = $stream->isWritable();
        self::assertTrue($writable);
        $readble = $stream->isReadable();
        self::assertTrue($readble);
    }

    public function testCreateStreamFromFile()
    {
        $factory = new StreamFactory();
        $stream = $factory->createStreamFromFile(__DIR__ . '/../temp/stream.txt');
        $writable = $stream->isWritable();
        self::assertFalse($writable);
        $readble = $stream->isReadable();
        self::assertTrue($readble);
        $content = $stream->getContents();
        var_dump($content);
        $stream->close();
        $stream = $factory->createStreamFromFile(__DIR__ . '/../temp/stream.txt', 'w');
        $writable = $stream->isWritable();
        self::assertTrue($writable);
        $stream->close();
    }

    public function testCreateStreamFromResource()
    {
        $resource = fopen(__DIR__ . '/../temp/stream.txt', 'r');
        $factory = new StreamFactory();
        $stream = $factory->createStreamFromResource($resource);
        $writable = $stream->isWritable();
        self::assertFalse($writable);
        $readble = $stream->isReadable();
        self::assertTrue($readble);
        $content = $stream->getContents();
        var_dump($content);
    }

    public function testCreateStreamFromIterator()
    {
        $it = new MyIterator();
        $factory = new StreamFactory();
        $stream = $factory->createStreamFromIterator($it);
        $content = $stream->getContents();
        var_dump($content);
        self::assertEquals('firstelementsecondelementlastelement', $content);
    }

    public function testCreateStreamFromObject()
    {
        $factory = new StreamFactory();
        $resource = fopen(__DIR__ . '/../temp/stream.txt', 'r');
        $stream = new Stream($resource);
        $stream = $factory->createStreamFromObject($stream);
        var_dump($stream);
        $content = $stream->getContents();
        var_dump($content);
        self::assertEquals('0123456789', $content);
    }

    public function testCreatStreamFromCallable()
    {
        $factory = new StreamFactory();
        $fired = false;
        $stream = $factory->creatStreamFromCallable(function () use (&$fired){
            if (!$fired) {
                $fired = true;
                return '0123456789';
            }
            return null;
        });
        $content = $stream->getContents();
        var_dump($content);
        self::assertEquals('0123456789', $content);
    }
}

class MyIterator implements Iterator
{
    private $position;

    private $array = [
        "firstelement",
        "secondelement",
        "lastelement",
    ];

    public function __construct()
    {
        $this->position = 0;
    }

    public function rewind()
    {
        var_dump(__METHOD__);
        $this->position = 0;
    }

    public function current()
    {
        var_dump(__METHOD__);
        return $this->array[$this->position];
    }

    public function key()
    {
        var_dump(__METHOD__);
        return $this->position;
    }

    public function next()
    {
        var_dump(__METHOD__);
        ++$this->position;
    }

    public function valid(): bool
    {
        var_dump(__METHOD__);
        return isset($this->array[$this->position]);
    }
}