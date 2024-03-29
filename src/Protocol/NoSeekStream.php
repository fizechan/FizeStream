<?php

namespace Fize\Stream\Protocol;

use Fize\Stream\StreamDecorator;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * 不可随机流
 */
class NoSeekStream extends StreamDecorator implements StreamInterface
{

    /**
     * 构造
     * @param StreamInterface $stream 流对象
     */
    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;
    }

    /**
     * 定位流中的指定位置
     * @param int $offset 要定位的流的偏移量
     * @param int $whence 指定如何根据偏移量计算光标位置
     */
    public function seek(int $offset, int $whence = SEEK_SET)
    {
        throw new RuntimeException('Cannot seek a NoSeekStream');
    }

    /**
     * 返回流是否可随机读取
     * @return bool
     */
    public function isSeekable(): bool
    {
        return false;
    }
}
