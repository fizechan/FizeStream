<?php


namespace fize\stream;


use RuntimeException;
use Psr\Http\Message\StreamInterface;

class NoSeekStream extends StreamDecorator implements StreamInterface
{

    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        throw new RuntimeException('Cannot seek a NoSeekStream');
    }

    public function isSeekable()
    {
        return false;
    }
}