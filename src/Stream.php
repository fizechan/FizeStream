<?php


namespace fize\net;

use Exception;
use RuntimeException;
use Psr\Http\Message\StreamInterface;
use fize\io\Stream as StreamIO;
use fize\io\File;
use fize\misc\Preg;

/**
 * 数据流
 */
class Stream implements StreamInterface
{

    /**
     * 可读模式
     */
    const READABLE_MODES = '/r|a\+|ab\+|w\+|wb\+|x\+|xb\+|c\+|cb\+/';

    /**
     * 可写模式
     */
    const WRITABLE_MODES = '/a|w|r\+|rb\+|rw|x|c/';

    /**
     * @var StreamIO|null 流对象
     */
    protected $stream;

    /**
     * @var File 流文件对象
     */
    protected $file;

    /**
     * @var int|null 流的数据大小
     */
    protected $size;

    /**
     * @var bool 是否可随机读取
     */
    protected $seekable;

    /**
     * @var bool 是否可读
     */
    protected $readable;

    /**
     * @var bool 是否可写
     */
    protected $writable;

    /**
     * @var string 资源唯一标识
     */
    protected $uri;

    /**
     * @var array 自定义元数据
     */
    protected $customMetadata;

    /**
     * 构造
     * @param StreamIO $stream 流对象
     * @param array $options 附加选项
     */
    public function __construct(StreamIO $stream, array $options = [])
    {
        $this->stream = $stream;
        $this->file = new File($this->stream->get());
        if (isset($options['size'])) {
            $this->size = $options['size'];
        }
        $this->customMetadata = isset($options['metadata']) ? $options['metadata'] : [];
        $meta = $this->stream->getMetaData();
        $this->seekable = $meta['seekable'];
        $this->readable = Preg::match(self::READABLE_MODES, $meta['mode']) ? true : false;
        $this->writable = Preg::match(self::WRITABLE_MODES, $meta['mode']) ? true : false;
        $this->uri = $this->getMetadata('uri');
    }

    /**
     * 析构
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * 从头到尾将流中的所有数据读取到字符串
     * @return string
     */
    public function __toString()
    {
        try {
            $this->seek(0);
            return $this->stream->getContents();
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * 关闭流和任何底层资源
     */
    public function close()
    {
        if (isset($this->stream)) {
            $this->file = null;
            $this->stream = null;
            $this->detach();
        }
    }

    /**
     * 从流中分离任何底层资源
     *
     * 分离之后，流处于不可用状态。
     * @return resource|null 如果存在的话，返回底层 PHP 流。
     */
    public function detach()
    {
        if (!isset($this->stream)) {
            return null;
        }

        $result = $this->stream->get();
        unset($this->file);
        unset($this->stream);
        $this->size = $this->uri = null;
        $this->readable = $this->writable = $this->seekable = false;

        return $result;
    }

    /**
     * 获取流的数据大小
     *
     * 如果可知，返回以字节为单位的大小，如果未知返回 `null`。
     * @return int|null
     */
    public function getSize()
    {
        if ($this->size !== null) {
            return $this->size;
        }

        if (!isset($this->file)) {
            return null;
        }

        if ($this->uri) {
            clearstatcache(true, $this->uri);
        }

        $stats = $this->file->stat();
        if (isset($stats['size'])) {
            $this->size = $stats['size'];
            return $this->size;
        }

        return null;
    }

    /**
     * 返回当前读/写的指针位置
     * @return int
     */
    public function tell()
    {
        if (!isset($this->file)) {
            throw new RuntimeException('Stream is detached');
        }

        $result = $this->file->tell();

        if ($result === false) {
            throw new RuntimeException('Unable to determine stream position');
        }

        return $result;
    }

    /**
     * 是否位于流的末尾
     * @return bool
     */
    public function eof()
    {
        if (!isset($this->file)) {
            throw new RuntimeException('Stream is detached');
        }

        return $this->file->eof();
    }

    /**
     * 返回流是否可随机读取
     * @return bool
     */
    public function isSeekable()
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
        if (!isset($this->file)) {
            throw new RuntimeException('Stream is detached');
        }
        if (!$this->seekable) {
            throw new RuntimeException('Stream is not seekable');
        }
        if ($this->file->seek($offset, $whence) === -1) {
            throw new RuntimeException('Unable to seek to stream position ' . $offset . ' with whence ' . var_export($whence, true));
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
    public function isWritable()
    {
        return $this->writable;
    }

    /**
     * 向流中写数据
     * @param string $string 要写入流的数据
     * @return int 返回写入流的字节数
     */
    public function write($string)
    {
        if (!isset($this->file)) {
            throw new RuntimeException('Stream is detached');
        }
        if (!$this->writable) {
            throw new RuntimeException('Cannot write to a non-writable stream');
        }

        $this->size = null;  //数据大小无法得知
        $result = $this->file->write($string);

        if ($result === false) {
            throw new RuntimeException('Unable to write to stream');
        }

        return $result;
    }

    /**
     * 返回流是否可读
     * @return bool
     */
    public function isReadable()
    {
        return $this->readable;
    }

    /**
     * 从流中读取数据
     * @param int $length 最多读取 $length 字节的数据
     * @return string
     */
    public function read($length)
    {
        if (!isset($this->file)) {
            throw new RuntimeException('Stream is detached');
        }
        if (!$this->readable) {
            throw new RuntimeException('Cannot read from non-readable stream');
        }
        if ($length < 0) {
            throw new RuntimeException('Length parameter cannot be negative');
        }

        if (0 === $length) {
            return '';
        }

        $string = $this->file->read($length);
        if (false === $string) {
            throw new RuntimeException('Unable to read from stream');
        }

        return $string;
    }

    /**
     * 返回字符串中的剩余内容
     * @return string
     */
    public function getContents()
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }

        $contents = $this->stream->getContents();

        if ($contents === false) {
            throw new RuntimeException('Unable to read stream contents');
        }

        return $contents;
    }

    /**
     * 获取流中的元数据作为关联数组，或者检索指定的键
     * @param string|null $key 键名
     * @return array|mixed|null
     */
    public function getMetadata($key = null)
    {
        if (!isset($this->stream)) {
            return $key ? null : [];
        } elseif (!$key) {
            return $this->customMetadata + $this->stream->getMetaData();
        } elseif (isset($this->customMetadata[$key])) {
            return $this->customMetadata[$key];
        }

        $meta = $this->stream->getMetaData();
        return isset($meta[$key]) ? $meta[$key] : null;
    }
}