<?php


namespace fize\stream\protocol;

use Exception;
use RuntimeException;
use Psr\Http\Message\StreamInterface;
use fize\stream\Stream;

/**
 * PHP 数据流
 *
 * 提供一个只读流，用于从PHP可调用项中提取数据。
 */
class PumpStream implements StreamInterface
{

    /**
     * @var callable 流数据的来源
     */
    private $source;

    /**
     * @var int|null 流的数据大小
     */
    private $size;

    /**
     * @var int
     */
    private $tellPos = 0;

    /**
     * @var array 元数据
     */
    private $metadata;

    /**
     * @var BufferStream 缓冲区
     */
    private $buffer;

    /**
     * 构造
     *
     * 参数 `$source`:
     *   该方法接受一个用于控制返回的数据量的整型参数。
     *   调用时返回一个字符串，如果错误则返回 false 或 EOF。
     * @param callable $source 数据源
     * @param array $options 选项
     */
    public function __construct(callable $source, array $options = [])
    {
        $this->source = $source;
        $this->size = isset($options['size']) ? $options['size'] : null;
        $this->metadata = isset($options['metadata']) ? $options['metadata'] : [];
        $this->buffer = new BufferStream();
    }

    /**
     * 从头到尾将流中的所有数据读取到字符串
     * @return string
     */
    public function __toString()
    {
        try {
            return Stream::copyToString($this);
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * 关闭流和任何底层资源
     */
    public function close()
    {
        $this->detach();
    }

    /**
     * 从流中分离任何底层资源
     *
     * 分离之后，流处于不可用状态。
     * @return null
     */
    public function detach()
    {
        $this->tellPos = false;
        $this->source = null;
        return null;
    }

    /**
     * 获取流的数据大小
     *
     * 如果可知，返回以字节为单位的大小
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * 返回当前读/写的指针位置
     * @return int
     */
    public function tell()
    {
        return $this->tellPos;
    }

    /**
     * 是否位于流的末尾
     * @return bool
     */
    public function eof()
    {
        return !$this->source;
    }

    /**
     * 返回流是否可随机读取
     * @return bool
     */
    public function isSeekable()
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
        throw new RuntimeException('Cannot seek a PumpStream');
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
    public function isWritable()
    {
        return false;
    }

    /**
     * 向流中写数据
     * @param string $string 要写入流的数据
     */
    public function write($string)
    {
        throw new RuntimeException('Cannot write to a PumpStream');
    }

    /**
     * 返回流是否可读
     * @return bool
     */
    public function isReadable()
    {
        return true;
    }

    /**
     * 从流中读取数据
     * @param int $length 最多读取 $length 字节的数据
     * @return string
     */
    public function read($length)
    {
        $data = $this->buffer->read($length);
        $readLen = strlen($data);
        $this->tellPos += $readLen;
        $remaining = $length - $readLen;

        if ($remaining) {
            $this->pump($remaining);
            $data .= $this->buffer->read($remaining);
            $this->tellPos += strlen($data) - $readLen;
        }

        return $data;
    }

    /**
     * 返回字符串中的剩余内容
     * @return string
     */
    public function getContents()
    {
        $result = '';
        while (!$this->eof()) {
            $result .= $this->read(1000000);
        }
        return $result;
    }

    /**
     * 获取流中的元数据作为关联数组，或者检索指定的键
     * @param string|null $key 键名
     * @return array|mixed|null
     */
    public function getMetadata($key = null)
    {
        if (!$key) {
            return $this->metadata;
        }

        return isset($this->metadata[$key]) ? $this->metadata[$key] : null;
    }

    /**
     * 获取输出数据
     * @param int $length 输出长度
     */
    private function pump($length)
    {
        if ($this->source) {
            do {
                $data = call_user_func($this->source, $length);
                if ($data === false || $data === null) {
                    $this->source = null;
                    return;
                }
                $this->buffer->write($data);
                $length -= strlen($data);
            } while ($length > 0);
        }
    }
}
