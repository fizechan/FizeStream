<?php

namespace fize\stream;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

/**
 * 流包装器
 */
class StreamWrapper
{

    /**
     * @var resource 资源
     */
    public $context;

    /**
     * @var StreamInterface 流
     */
    private $stream;

    /**
     * @var string 方式：r, r+, or w
     */
    private $mode;

    /**
     * @var string 路径
     */
    protected $path;

    /**
     * @var array 选项
     */
    protected $options;

    /**
     * @var int 标识
     */
    protected $flags;

    /**
     * 获取流资源
     * @param StreamInterface $stream 流对象
     * @return resource 失败时返回false
     */
    public static function getResource(StreamInterface $stream)
    {
        self::register();

        if ($stream->isReadable()) {
            $mode = $stream->isWritable() ? 'r+' : 'r';
        } elseif ($stream->isWritable()) {
            $mode = 'w';
        } else {
            throw new InvalidArgumentException('The stream must be readable, writable, or both.');
        }

        return fopen('fize://stream', $mode, null, self::createStreamContext($stream));
    }

    /**
     * 注册协议
     */
    public static function register()
    {
        if (!in_array('fize', stream_get_wrappers())) {
            stream_wrapper_register('fize', __CLASS__);
        }
    }

    /**
     * 创建流上下文
     * @param StreamInterface $stream 流对象
     * @return resource
     */
    public static function createStreamContext(StreamInterface $stream)
    {
        return stream_context_create([
            'fize' => ['stream' => $stream]
        ]);
    }

    /**
     * 打开文件或者URL
     * @param string $path    文件路径或者URL
     * @param string $mode    模式
     * @param array  $options 选项
     * @param string $opened_path
     * @return bool 如果路径被成功打开，该值返回实际路径
     */
    public function stream_open(string $path, string $mode, array $options, string &$opened_path): bool
    {
        $this->path = $path;
        $this->options = $options;
        $options = stream_context_get_options($this->context);

        if (!isset($options['fize']['stream'])) {
            return false;
        }

        $this->mode = $mode;
        $this->stream = $options['fize']['stream'];
        $opened_path = realpath($path);
        return true;
    }

    /**
     * 读取
     * @param int $count 字节数
     * @return string
     */
    public function stream_read(int $count): string
    {
        return $this->stream->read($count);
    }

    /**
     * 写入
     * @param string $data 数据
     * @return int
     */
    public function stream_write(string $data): int
    {
        return $this->stream->write($data);
    }

    /**
     * 返回当前流位置
     * @return int
     */
    public function stream_tell(): int
    {
        return $this->stream->tell();
    }

    /**
     * 是否到流的结尾
     * @return bool
     */
    public function stream_eof(): bool
    {
        return $this->stream->eof();
    }

    /**
     * 移动流位置
     * @param int $offset 偏移
     * @param int $whence 偏移参照
     * @return bool
     */
    public function stream_seek(int $offset, int $whence): bool
    {
        $this->stream->seek($offset, $whence);

        return true;
    }

    /**
     * 返回底层资源
     * @param int $cast_as STREAM_CAST_FOR_SELECT|STREAM_CAST_AS_STREAM
     * @return resource|null
     */
    public function stream_cast(int $cast_as)
    {
        if ($cast_as == STREAM_CAST_FOR_SELECT) {
            return null;
        }

        $stream = clone($this->stream);
        return $stream->detach();
    }

    /**
     * 检索关于文件资源的信息
     * @return array
     */
    public function stream_stat(): array
    {
        static $modeMap = [
            'r'  => 33060,
            'rb' => 33060,
            'r+' => 33206,
            'w'  => 33188,
            'wb' => 33188
        ];

        $size = $this->stream->getSize();
        return [
            'dev'     => 0,
            'ino'     => 0,
            'mode'    => $modeMap[$this->mode],
            'nlink'   => 0,
            'uid'     => 0,
            'gid'     => 0,
            'rdev'    => 0,
            'size'    => $size ?: 0,
            'atime'   => 0,
            'mtime'   => 0,
            'ctime'   => 0,
            'blksize' => 0,
            'blocks'  => 0
        ];
    }

    /**
     * 检索关于文件的信息
     * @param string $path  路径或者URL
     * @param int    $flags 标识
     * @return array
     */
    public function url_stat(string $path, int $flags): array
    {
        $this->path = $path;
        $this->flags = $flags;

        return [
            'dev'     => 0,
            'ino'     => 0,
            'mode'    => 0,
            'nlink'   => 0,
            'uid'     => 0,
            'gid'     => 0,
            'rdev'    => 0,
            'size'    => 0,
            'atime'   => 0,
            'mtime'   => 0,
            'ctime'   => 0,
            'blksize' => 0,
            'blocks'  => 0
        ];
    }
}
