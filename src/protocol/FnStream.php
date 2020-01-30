<?php


namespace fize\stream\protocol;

use LogicException;
use Psr\Http\Message\StreamInterface;
use fize\stream\StreamDecorator;

/**
 * 方法流
 *
 * 根据传递的一些列方法定义一个流
 */
class FnStream extends StreamDecorator implements StreamInterface
{

    /**
     * @var array 所有方法
     */
    private $methods;

    /**
     * @var array 必须在给定数组中实现的方法
     */
    private static $slots = [
        '__toString', 'close', 'detach', 'rewind', 'getSize',
        'tell', 'eof', 'isSeekable', 'seek', 'isWritable',
        'write', 'isReadable', 'read', 'getContents', 'getMetadata'
    ];

    /**
     * 构造
     * @param array $methods 实现方法组成的数组
     */
    public function __construct(array $methods)
    {
        $this->methods = $methods;
    }

    /**
     * 析构
     */
    public function __destruct()
    {
        if (isset($this->methods['close'])) {
            call_user_func($this->methods['close']);
        }
    }

    /**
     * 魔法方法：序列化
     */
    public function __wakeup()
    {
        throw new LogicException('FnStream should never be unserialized');
    }

    /**
     * 从头到尾将流中的所有数据读取到字符串
     * @return string
     */
    public function __toString()
    {
        return call_user_func($this->methods['__toString']);
    }

    /**
     * 关闭流和任何底层资源
     */
    public function close()
    {
        return call_user_func($this->methods['close']);
    }

    /**
     * 从流中分离任何底层资源
     *
     * 分离之后，流处于不可用状态。
     * @return resource|null 如果存在的话，返回底层 PHP 流。
     */
    public function detach()
    {
        return call_user_func($this->methods['detach']);
    }

    /**
     * 获取流的数据大小
     *
     * 如果可知，返回以字节为单位的大小，如果未知返回 `null`。
     * @return int|null
     */
    public function getSize()
    {
        return call_user_func($this->methods['getSize']);
    }

    /**
     * 返回当前读/写的指针位置
     * @return int
     */
    public function tell()
    {
        return call_user_func($this->methods['tell']);
    }

    /**
     * 是否位于流的末尾
     * @return bool
     */
    public function eof()
    {
        return call_user_func($this->methods['eof']);
    }

    /**
     * 返回流是否可随机读取
     * @return bool
     */
    public function isSeekable()
    {
        return call_user_func($this->methods['isSeekable']);
    }

    /**
     * 定位流中的指定位置
     * @param int $offset 要定位的流的偏移量
     * @param int $whence 指定如何根据偏移量计算光标位置
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        call_user_func($this->methods['seek'], $offset, $whence);
    }

    /**
     * 定位流的起始位置
     */
    public function rewind()
    {
        call_user_func($this->methods['rewind']);
    }

    /**
     * 返回流是否可写
     * @return bool
     */
    public function isWritable()
    {
        return call_user_func($this->methods['isWritable']);
    }

    /**
     * 向流中写数据
     * @param string $string 要写入流的数据
     * @return int 返回写入流的字节数
     */
    public function write($string)
    {
        return call_user_func($this->methods['write'], $string);
    }

    /**
     * 返回流是否可读
     * @return bool
     */
    public function isReadable()
    {
        return call_user_func($this->methods['isReadable']);
    }

    /**
     * 从流中读取数据
     * @param int $length 最多读取 $length 字节的数据
     * @return string
     */
    public function read($length)
    {
        return call_user_func($this->methods['read'], $length);
    }

    /**
     * 返回字符串中的剩余内容
     * @return string
     */
    public function getContents()
    {
        return call_user_func($this->methods['getContents']);
    }

    /**
     * 获取流中的元数据作为关联数组，或者检索指定的键
     * @param string|null $key 键名
     * @return array|mixed|null
     */
    public function getMetadata($key = null)
    {
        return call_user_func($this->methods['getMetadata'], $key);
    }

    /**
     * 通过拦截特定的方法调用向基础流添加自定义功能。
     * @param StreamInterface $stream 流
     * @param array $methods 自定义功能
     * @return static
     */
    public static function decorate(StreamInterface $stream, array $methods)
    {
        foreach (array_diff(self::$slots, array_keys($methods)) as $diff) {
            $methods[$diff] = [$stream, $diff];
        }

        return new self($methods);
    }
}
