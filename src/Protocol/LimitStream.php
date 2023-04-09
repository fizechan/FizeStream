<?php

namespace Fize\Stream\Protocol;

use Fize\Stream\StreamDecorator;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * 截止流
 *
 * 只允许读取到指定长度
 */
class LimitStream extends StreamDecorator implements StreamInterface
{

    /**
     * @var int 偏移
     */
    private $offset;

    /**
     * @var int 允许读取的字节数
     */
    private $limit;

    /**
     * 构造
     * @param StreamInterface $stream 流对象
     * @param int             $limit  允许读取的字节数
     * @param int             $offset 偏移
     */
    public function __construct(StreamInterface $stream, int $limit = -1, int $offset = 0)
    {
        $this->stream = $stream;
        $this->setLimit($limit);
        $this->setOffset($offset);
    }

    /**
     * 是否流的结束
     * @return bool
     */
    public function eof(): bool
    {
        if ($this->stream->eof()) {
            return true;
        }

        if ($this->limit == -1) {
            return false;
        }

        return $this->stream->tell() >= $this->offset + $this->limit;
    }

    /**
     * 获取流大小
     * @return int|null
     */
    public function getSize(): ?int
    {
        $length = $this->stream->getSize();
        if (null === $length) {
            return null;
        } elseif ($this->limit == -1) {
            return $length - $this->offset;
        } else {
            return min($this->limit, $length - $this->offset);
        }
    }

    /**
     * 移动流指针
     * @param int $offset 偏移量
     * @param int $whence 偏移参照
     */
    public function seek(int $offset, int $whence = SEEK_SET)
    {
        if ($whence !== SEEK_SET || $offset < 0) {
            throw new RuntimeException(sprintf('Cannot seek to offset %s with whence %s', $offset, $whence));
        }

        $offset += $this->offset;

        if ($this->limit !== -1) {
            if ($offset > $this->offset + $this->limit) {
                $offset = $this->offset + $this->limit;
            }
        }

        $this->stream->seek($offset);
    }

    /**
     * 流当前指针位置
     * @return int
     */
    public function tell(): int
    {
        return $this->stream->tell() - $this->offset;
    }

    /**
     * 读取
     * @param int $length 字节长度
     * @return string
     */
    public function read(int $length): string
    {
        if ($this->limit == -1) {
            return $this->stream->read($length);
        }

        // Check if the current position is less than the total allowed
        // bytes + original offset
        $remaining = ($this->offset + $this->limit) - $this->stream->tell();
        if ($remaining > 0) {
            // Only return the amount of requested data, ensuring that the byte
            // limit is not exceeded
            return $this->stream->read(min($remaining, $length));
        }

        return '';
    }

    /**
     * 设置允许读取的字节数
     *
     * 参数 `$limit` :
     *   -1 表示不限制
     * @param int $limit 允许读取的字节数
     */
    protected function setLimit(int $limit)
    {
        $this->limit = $limit;
    }

    /**
     * 设置偏移量
     * @param int $offset 偏移量
     */
    protected function setOffset(int $offset)
    {
        $current = $this->stream->tell();

        if ($current !== $offset) {
            // If the stream cannot seek to the offset position, then read to it
            if ($this->stream->isSeekable()) {
                $this->stream->seek($offset);
            } elseif ($current > $offset) {
                throw new RuntimeException("Could not seek to stream offset $offset");
            } else {
                $this->stream->read($offset - $current);
            }
        }

        $this->offset = $offset;
    }
}
