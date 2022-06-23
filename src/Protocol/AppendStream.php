<?php

namespace Fize\Stream\Protocol;

use Exception;
use Fize\Stream\Stream;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * 多流数据流
 *
 * 从多个流中读取，一个接一个。
 * 该数据流是个只读流。
 */
class AppendStream implements StreamInterface
{

    /**
     * @var StreamInterface[] 待读取的数据流
     */
    private $streams = [];

    /**
     * @var bool 是否可随机读写
     */
    private $seekable = true;

    /**
     * @var int 当前流下标
     */
    private $current = 0;

    /**
     * @var int 指针位置
     */
    private $pos = 0;

    /**
     * 构造
     * @param StreamInterface[] $streams 待读取的数据流
     */
    public function __construct(array $streams = [])
    {
        foreach ($streams as $stream) {
            $this->addStream($stream);
        }
    }

    /**
     * 从头到尾将流中的所有数据读取到字符串
     * @return string
     */
    public function __toString(): string
    {
        try {
            $this->rewind();
            return $this->getContents();
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * 关闭流和任何底层资源
     */
    public function close()
    {
        $this->pos = $this->current = 0;
        $this->seekable = true;

        foreach ($this->streams as $stream) {
            $stream->close();
        }

        $this->streams = [];
    }

    /**
     * 从流中分离任何底层资源
     *
     * 分离之后，流处于不可用状态。
     * @return null
     */
    public function detach()
    {
        $this->pos = $this->current = 0;
        $this->seekable = true;

        foreach ($this->streams as $stream) {
            $stream->detach();
        }

        $this->streams = [];
        return null;
    }

    /**
     * 获取流的数据大小
     *
     * 如果可知，返回以字节为单位的大小
     * @return int|null 未知返回 null
     */
    public function getSize(): ?int
    {
        $size = 0;

        foreach ($this->streams as $stream) {
            $s = $stream->getSize();
            if ($s === null) {
                return null;
            }
            $size += $s;
        }

        return $size;
    }

    /**
     * 返回当前读/写的指针位置
     * @return int
     */
    public function tell(): int
    {
        return $this->pos;
    }

    /**
     * 是否位于流的末尾
     * @return bool
     */
    public function eof(): bool
    {
        if (!$this->streams) {
            return true;
        }
        return $this->current >= count($this->streams) - 1 && $this->streams[$this->current]->eof();
    }

    /**
     * 返回流是否可随机读取
     * @return bool
     */
    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    /**
     * 定位流中的指定位置
     * @param int $offset 要定位的流的偏移量
     * @param int $whence 指定如何根据偏移量计算光标位置
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->seekable) {
            throw new RuntimeException('This AppendStream is not seekable');
        } elseif ($whence !== SEEK_SET) {
            throw new RuntimeException('The AppendStream can only seek with SEEK_SET');
        }

        $this->pos = $this->current = 0;

        foreach ($this->streams as $i => $stream) {
            try {
                $stream->rewind();
            } catch (Exception $e) {
                throw new RuntimeException('Unable to seek stream ' . $i . ' of the AppendStream', 0, $e);
            }
        }

        // 通过读取每个流来查找实际位置
        while ($this->pos < $offset && !$this->eof()) {
            $result = $this->read(min(8096, $offset - $this->pos));
            if ($result === '') {
                break;
            }
        }
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
        return false;
    }

    /**
     * 向流中写数据
     * @param string $string 要写入流的数据
     * @return int
     */
    public function write($string): int
    {
        throw new RuntimeException('Cannot write to an AppendStream');
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
        $buffer = '';
        $total = count($this->streams) - 1;
        $remaining = $length;
        $progressToNext = false;

        while ($remaining > 0) {

            // 如果需要，进入到下一个流
            if ($progressToNext || $this->streams[$this->current]->eof()) {
                $progressToNext = false;
                if ($this->current === $total) {
                    break;
                }
                $this->current++;
            }

            $result = $this->streams[$this->current]->read($remaining);

            if ($result == null) {
                $progressToNext = true;
                continue;
            }

            $buffer .= $result;
            $remaining = $length - strlen($buffer);
        }

        $this->pos += strlen($buffer);

        return $buffer;
    }

    /**
     * 返回字符串中的剩余内容
     * @return string
     */
    public function getContents(): string
    {
        return Stream::copyToString($this);
    }

    /**
     * 获取流中的元数据作为关联数组，或者检索指定的键
     * @param string|null $key 键名
     * @return array|null
     */
    public function getMetadata($key = null): ?array
    {
        return $key ? null : [];
    }

    /**
     * 添加一个流
     * @param StreamInterface $stream 数据流
     */
    public function addStream(StreamInterface $stream)
    {
        if (!$stream->isReadable()) {
            throw new InvalidArgumentException('Each stream must be readable');
        }

        // 只有当所有流都是可随机读写时该流才是可随机读写的
        if (!$stream->isSeekable()) {
            $this->seekable = false;
        }

        $this->streams[] = $stream;
    }
}
