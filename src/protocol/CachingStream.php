<?php

namespace fize\stream\protocol;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use fize\stream\StreamDecorator;
use fize\stream\Stream;

/**
 * 可缓存流
 *
 * 可以缓存以前从顺序读取的流中读取的字节。
 */
class CachingStream extends StreamDecorator implements StreamInterface
{

    /**
     * @var StreamInterface 待包装的流
     */
    private $remoteStream;

    /**
     * @var int 由于缓冲区上的写操作而要跳过读取的字节数
     */
    private $skipReadBytes = 0;

    /**
     * 构造
     * @param StreamInterface      $stream 原始流
     * @param StreamInterface|null $target 缓存目标流
     */
    public function __construct(StreamInterface $stream, StreamInterface $target = null)
    {
        $this->remoteStream = $stream;
        $this->stream = $target ?: new Stream('php://temp', 'r+');
    }

    /**
     * 关闭流和任何底层资源
     */
    public function close()
    {
        $this->remoteStream->close();
        $this->stream->close();
    }

    /**
     * 获取流的数据大小
     *
     * 如果可知，返回以字节为单位的大小
     * @return int|null 未知返回 null
     */
    public function getSize()
    {
        return max($this->stream->getSize(), $this->remoteStream->getSize());
    }

    /**
     * 是否位于流的末尾
     * @return bool
     */
    public function eof()
    {
        return $this->stream->eof() && $this->remoteStream->eof();
    }

    /**
     * 定位流中的指定位置
     * @param int $offset 要定位的流的偏移量
     * @param int $whence 指定如何根据偏移量计算光标位置
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if ($whence == SEEK_SET) {
            $byte = $offset;
        } elseif ($whence == SEEK_CUR) {
            $byte = $offset + $this->tell();
        } elseif ($whence == SEEK_END) {
            $size = $this->remoteStream->getSize();
            if ($size === null) {
                $size = $this->cacheEntireStream();
            }
            $byte = $size + $offset;
        } else {
            throw new InvalidArgumentException('Invalid whence');
        }

        $diff = $byte - $this->stream->getSize();

        if ($diff > 0) {
            while ($diff > 0 && !$this->remoteStream->eof()) {
                $this->read($diff);
                $diff = $byte - $this->stream->getSize();
            }
        } else {
            $this->stream->seek($byte);
        }
    }

    /**
     * 向流中写数据
     * @param string $string 要写入流的数据
     * @return int 返回写入流的字节数
     */
    public function write($string)
    {
        $overflow = strlen($string) + $this->tell() - $this->remoteStream->tell();
        if ($overflow > 0) {
            $this->skipReadBytes += $overflow;
        }

        return $this->stream->write($string);
    }

    /**
     * 从流中读取数据
     * @param int $length 最多读取 $length 字节的数据
     * @return string
     */
    public function read($length)
    {
        $data = $this->stream->read($length);
        $remaining = $length - strlen($data);
        if ($remaining) {
            $remoteData = $this->remoteStream->read(
                $remaining + $this->skipReadBytes
            );

            if ($this->skipReadBytes) {
                $len = strlen($remoteData);
                $remoteData = substr($remoteData, $this->skipReadBytes);
                $this->skipReadBytes = max(0, $this->skipReadBytes - $len);
            }

            $data .= $remoteData;
            $this->stream->write($remoteData);
        }

        return $data;
    }

    /**
     * 缓存当前流
     * @return int 返回已读取字节数
     */
    private function cacheEntireStream()
    {
        $target = new FnStream(['write' => 'strlen']);
        Stream::copyToStream($this, $target);
        return $this->tell();
    }
}
