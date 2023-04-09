<?php

namespace Fize\Stream\Protocol;

use Fize\Stream\StreamDecorator;
use Psr\Http\Message\StreamInterface;

/**
 * 丢弃流
 *
 * 一旦底层流的大小写满，就不再写入数据。
 */
class DroppingStream extends StreamDecorator implements StreamInterface
{

    /**
     * @var int 最大获取字节数
     */
    private $maxLength;

    /**
     * 构造
     * @param StreamInterface $stream    流
     * @param int             $maxLength 最大获取字节数
     */
    public function __construct(StreamInterface $stream, int $maxLength)
    {
        $this->stream = $stream;
        $this->maxLength = $maxLength;
    }

    /**
     * 向流中写数据
     * @param string $string 要写入流的数据
     * @return int 返回写入流的字节数
     */
    public function write(string $string): int
    {
        $diff = $this->maxLength - $this->stream->getSize();

        if ($diff <= 0) {
            return 0;
        }

        if (strlen($string) < $diff) {
            return $this->stream->write($string);
        }

        return $this->stream->write(substr($string, 0, $diff));
    }
}
