<?php


namespace fize\stream;

use UnexpectedValueException;
use Psr\Http\Message\StreamInterface;

/**
 * 流装饰器
 */
abstract class StreamDecorator implements StreamInterface
{
    /**
     * @var StreamInterface 原始流
     */
    protected $stream;

    /**
     * 魔法方法：获取属性
     *
     * 参数 `$name`:
     *   仅支持参数 `stream`
     * @param string $name 属性名
     * @return StreamInterface
     * @todo 待移除该方法
     */
    public function __get($name)
    {
        if ($name == 'stream') {
            $this->stream = $this->createStream();
            return $this->stream;
        }

        throw new UnexpectedValueException("{$name} not found on class");
    }

    /**
     * 魔法方法：调用方法
     * @param $method
     * @param array $args
     * @return $this|mixed
     * @todo 待移除该方法
     */
    public function __call($method, array $args)
    {
        $result = call_user_func_array([$this->stream, $method], $args);
        return $result === $this->stream ? $this : $result;
    }

    /**
     * 从头到尾将流中的所有数据读取到字符串
     * @return string
     */
    public function __toString()
    {
        if ($this->isSeekable()) {
            $this->seek(0);
        }
        return $this->getContents();
    }

    /**
     * 关闭流和任何底层资源
     */
    public function close()
    {
        $this->stream->close();
    }

    /**
     * 从流中分离任何底层资源
     *
     * 分离之后，流处于不可用状态。
     * @return null
     */
    public function detach()
    {
        return $this->stream->detach();
    }

    /**
     * 获取流的数据大小
     *
     * 如果可知，返回以字节为单位的大小
     * @return int|null 未知返回 null
     */
    public function getSize()
    {
        return $this->stream->getSize();
    }

    /**
     * 返回当前读/写的指针位置
     * @return int
     */
    public function tell()
    {
        return $this->stream->tell();
    }

    /**
     * 是否位于流的末尾
     * @return bool
     */
    public function eof()
    {
        return $this->stream->eof();
    }

    /**
     * 返回流是否可随机读取
     * @return bool
     */
    public function isSeekable()
    {
        return $this->stream->isSeekable();
    }

    /**
     * 定位流中的指定位置
     * @param int $offset 要定位的流的偏移量
     * @param int $whence 指定如何根据偏移量计算光标位置
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        $this->stream->seek($offset, $whence);
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
     * @return bool
     */
    public function isWritable()
    {
        return $this->stream->isWritable();
    }

    /**
     * 向流中写数据
     * @param string $string 要写入流的数据
     * @return int 返回写入流的字节数
     */
    public function write($string)
    {
        return $this->stream->write($string);
    }

    /**
     * 返回流是否可读
     * @return bool
     */
    public function isReadable()
    {
        return $this->stream->isReadable();
    }

    /**
     * 从流中读取数据
     * @param int $length 最多读取 $length 字节的数据
     * @return string
     */
    public function read($length)
    {
        return $this->stream->read($length);
    }

    /**
     * 返回字符串中的剩余内容
     * @return string
     */
    public function getContents()
    {
        return Stream::copyToString($this);
    }

    /**
     * 获取流中的元数据作为关联数组，或者检索指定的键
     * @param string|null $key 键名
     * @return array|mixed|null
     */
    public function getMetadata($key = null)
    {
        return $this->stream->getMetadata($key);
    }
}
