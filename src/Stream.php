<?php

namespace Fize\Stream;

use Exception;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * 数据流
 */
class Stream implements StreamInterface
{

    /**
     * 可读模式
     */
    private const READABLE_MODES = '/r|a\+|ab\+|w\+|wb\+|x\+|xb\+|c\+|cb\+/';

    /**
     * 可写模式
     */
    private const WRITABLE_MODES = '/a|w|r\+|rb\+|rw|x|c/';

    /**
     * @var resource 资源流上下文
     */
    protected $stream;

    /**
     * @var int 流的数据大小
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
     * 初始化
     * @param resource $stream  资源流
     * @param array    $options 附加选项
     */
    public function __construct($stream = null, array $options = [])
    {
        $this->stream = $stream;
        if (isset($options['size'])) {
            $this->size = $options['size'];
        }
        $this->customMetadata = $options['metadata'] ?? [];
        $this->checkMetadata();
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
    public function __toString(): string
    {
        try {
            $this->seek(0);
            return $this->getContents();
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * 打开流
     * @param string      $file             文件路径
     * @param string|null $mode             打开模式
     * @param bool        $use_include_path 是否在 include_path 中搜寻文件
     * @param resource    $context          上下文支持
     */
    public function open(string $file, string $mode = null, bool $use_include_path = false, $context = null)
    {
        if ($this->stream) {
            throw new RuntimeException('The original stream has not been closed');
        }
        if (strstr($file, '://') === false || substr($file, 0, 4) == 'file') {
            if (in_array($mode, ['r+', 'w', 'w+', 'a', 'a+', 'x', 'x+'])) {
                $dir = dirname($file);
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
            }
        }
        $this->stream = fopen($file, $mode, $use_include_path, $context);
        $this->checkMetadata();
    }

    /**
     * 关闭流和任何底层资源
     */
    public function close(): void
    {
        $stream = $this->detach();
        if ($stream && is_resource($stream) && get_resource_type($stream) == 'stream') {
            fclose($stream);
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

        $stream = $this->stream;
        unset($this->stream);
        $this->size = $this->uri = null;
        $this->readable = $this->writable = $this->seekable = false;

        return $stream;
    }

    /**
     * 获取流的数据大小
     *
     * 如果可知，返回以字节为单位的大小，如果未知返回 `null`。
     * @return int|null
     */
    public function getSize(): ?int
    {
        if ($this->size !== null) {
            return $this->size;
        }

        if (!isset($this->stream)) {
            return null;
        }

        if ($this->uri) {
            clearstatcache(true, $this->uri);
        }

        $stats = fstat($this->stream);
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
    public function tell(): int
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }

        $result = ftell($this->stream);

        if ($result === false) {
            throw new RuntimeException('Unable to determine stream position');
        }

        return $result;
    }

    /**
     * 是否位于流的末尾
     * @return bool
     */
    public function eof(): bool
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }

        return feof($this->stream);
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
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }
        if (!$this->seekable) {
            throw new RuntimeException('Stream is not seekable');
        }
        if (fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException('Unable to seek to stream position ' . $offset . ' with whence ' . var_export($whence, true));
        }
    }

    /**
     * 定位流的起始位置
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * 返回流是否可写
     * @return bool
     */
    public function isWritable(): bool
    {
        return $this->writable;
    }

    /**
     * 向流中写数据
     * @param string $string 要写入流的数据
     * @return int 返回写入流的字节数
     */
    public function write(string $string): int
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }
        if (!$this->writable) {
            throw new RuntimeException('Cannot write to a non-writable stream');
        }

        $this->size = null;  //数据大小无法得知
        $result = fwrite($this->stream, $string);

        if ($result === false) {
            throw new RuntimeException('Unable to write to stream');
        }

        return $result;
    }

    /**
     * 返回流是否可读
     * @return bool
     */
    public function isReadable(): bool
    {
        return $this->readable;
    }

    /**
     * 从流中读取数据
     * @param int $length 最多读取 $length 字节的数据
     * @return string
     */
    public function read(int $length): string
    {
        if (!isset($this->stream)) {
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

        $string = fread($this->stream, $length);
        if (false === $string) {
            throw new RuntimeException('Unable to read from stream');
        }

        return $string;
    }

    /**
     * 返回字符串中的剩余内容
     * @return string
     */
    public function getContents(): string
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }

        $contents = stream_get_contents($this->stream);

        if ($contents === false) {
            throw new RuntimeException('Unable to read stream contents');
        }

        return $contents;
    }

    /**
     * 获取流中的元数据作为关联数组，或者检索指定的键
     * @param string|null $key 键名
     * @return mixed
     */
    public function getMetadata(?string $key = null)
    {
        if (!isset($this->stream)) {
            return $key ? null : [];
        } elseif (!$key) {
            return $this->customMetadata + stream_get_meta_data($this->stream);
        } elseif (isset($this->customMetadata[$key])) {
            return $this->customMetadata[$key];
        }
        $meta = stream_get_meta_data($this->stream);
        return $meta[$key] ?? null;
    }

    /**
     * 将流复制为字符串
     * @param StreamInterface $stream 流
     * @param int             $maxLen 最长字节数，-1表示不限制
     * @return string
     */
    public static function copyToString(StreamInterface $stream, int $maxLen = -1): string
    {
        $buffer = '';

        if ($maxLen === -1) {
            while (!$stream->eof()) {
                $buf = $stream->read(1048576);
                if ($buf == null) {
                    break;
                }
                $buffer .= $buf;
            }
            return $buffer;
        }

        $len = 0;
        while (!$stream->eof() && $len < $maxLen) {
            $buf = $stream->read($maxLen - $len);
            if ($buf == null) {
                break;
            }
            $buffer .= $buf;
            $len = strlen($buffer);
        }

        return $buffer;
    }

    /**
     * 将一个流的内容复制到另一个流中
     * @param StreamInterface $source 源
     * @param StreamInterface $dest   目标
     * @param int             $maxLen 直到指定的数字为止字节已被读取
     */
    public static function copyToStream(StreamInterface $source, StreamInterface $dest, int $maxLen = -1)
    {
        $bufferSize = 8192;

        if ($maxLen === -1) {
            while (!$source->eof()) {
                if (!$dest->write($source->read($bufferSize))) {
                    break;
                }
            }
        } else {
            $remaining = $maxLen;
            while ($remaining > 0 && !$source->eof()) {
                $buf = $source->read(min($bufferSize, $remaining));
                $len = strlen($buf);
                if (!$len) {
                    break;
                }
                $remaining -= $len;
                $dest->write($buf);
            }
        }
    }

    /**
     * 创建临时流
     * @param array $options 选项
     * @return StreamInterface
     */
    public static function createTemp(array $options = []): StreamInterface
    {
        $resource = fopen('php://temp', 'r+');
        return new static($resource, $options);
    }

    /**
     * 检测META数据
     */
    protected function checkMetadata()
    {
        $meta = $this->getMetadata();
        if (empty($meta)) {
            return;
        }
        $this->seekable = $meta['seekable'];
        $this->readable = (bool)preg_match(self::READABLE_MODES, $meta['mode']);
        $this->writable = (bool)preg_match(self::WRITABLE_MODES, $meta['mode']);
        $this->uri = $this->getMetadata('uri');
    }
}
