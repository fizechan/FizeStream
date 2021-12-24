<?php

namespace Fize\Stream\Protocol;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * 缓冲区流
 *
 * 可以将其写入以填充缓冲区，并从中读取以从缓冲区中删除字节。
 */
class BufferStream implements StreamInterface
{

    /**
     * @var int 最高缓冲量
     */
    private $hwm;

    /**
     * @var string 当前缓冲
     */
    private $buffer = '';

    /**
     * 构造
     * @param int $hwm 最高缓冲量
     */
    public function __construct(int $hwm = 16384)
    {
        $this->hwm = $hwm;
    }

    /**
     * 从头到尾将流中的所有数据读取到字符串
     * @return string
     */
    public function __toString()
    {
        return $this->getContents();
    }

    /**
     * 关闭流和任何底层资源
     */
    public function close()
    {
        $this->buffer = '';
    }

    /**
     * 从流中分离任何底层资源
     *
     * 分离之后，流处于不可用状态。
     * @return null
     */
    public function detach()
    {
        $this->close();
        return null;
    }

    /**
     * 获取流的数据大小
     *
     * 如果可知，返回以字节为单位的大小
     * @return int
     */
    public function getSize(): int
    {
        return strlen($this->buffer);
    }

    /**
     * 返回当前读/写的指针位置
     */
    public function tell(): int
    {
        throw new RuntimeException('Cannot determine the position of a BufferStream');
    }

    /**
     * 是否位于流的末尾
     * @return bool
     */
    public function eof(): bool
    {
        return strlen($this->buffer) === 0;
    }

    /**
     * 返回流是否可随机读取
     * @return bool
     */
    public function isSeekable(): bool
    {
        return false;
    }

    /**
     * 定位流中的指定位置
     * @param int $offset 要定位的流的偏移量
     * @param int $whence 指定如何根据偏移量计算光标位置
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        throw new RuntimeException('Cannot seek a BufferStream');
    }

    /**
     * 定位流的起始位置
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * 返回流是否可写
     */
    public function isWritable(): bool
    {
        return true;
    }

    /**
     * 向流中写数据
     * @param string $string 要写入流的数据
     * @return int 返回写入流的字节数
     */
    public function write($string): int
    {
        if (strlen($this->buffer) >= $this->hwm) {
            return 0;
        }
        $this->buffer .= $string;
        return strlen($string);
    }

    /**
     * 返回流是否可读
     * @return bool
     */
    public function isReadable(): bool
    {
        return true;
    }

    /**
     * 从流中读取数据
     * @param int $length 最多读取 $length 字节的数据
     * @return string
     */
    public function read($length): string
    {
        $currentLength = strlen($this->buffer);

        if ($length >= $currentLength) {
            $result = $this->buffer;
            $this->buffer = '';
        } else {
            $result = substr($this->buffer, 0, $length);
            $this->buffer = substr($this->buffer, $length);
        }

        return $result;
    }

    /**
     * 返回字符串中的剩余内容
     * @return string
     */
    public function getContents(): string
    {
        $buffer = $this->buffer;
        $this->buffer = '';
        return $buffer;
    }

    /**
     * 获取流中的元数据作为关联数组，或者检索指定的键
     * @param string|null $key 键名
     * @return array|int|null
     */
    public function getMetadata($key = null)
    {
        if ($key == 'hwm') {
            return $this->hwm;
        }

        return $key ? null : ['hwm' => $this->hwm];
    }
}
