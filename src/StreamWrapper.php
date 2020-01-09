<?php


namespace fize\stream;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use fize\io\Stream;

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

        return fopen('guzzle://stream', $mode, null, self::createStreamContext($stream));
    }

    /**
     * 注册协议
     */
    public static function register()
    {
        if (!in_array('guzzle', Stream::getwrappers())) {
            Stream::wrapperRegister('guzzle', __CLASS__);
        }
    }

    /**
     * 创建流上下文
     * @param StreamInterface $stream 流
     * @return resource
     */
    public static function createStreamContext(StreamInterface $stream)
    {
        return Stream::contextCreate([
            'guzzle' => ['stream' => $stream]
        ]);
    }

    public function streamOpen($path, $mode, $options, &$opened_path)
    {
        $options = stream_context_get_options($this->context);

        if (!isset($options['guzzle']['stream'])) {
            return false;
        }

        $this->mode = $mode;
        $this->stream = $options['guzzle']['stream'];

        return true;
    }

    public function streamRead($count)
    {
        return $this->stream->read($count);
    }

    public function streamWrite($data)
    {
        return (int)$this->stream->write($data);
    }

    public function streamTell()
    {
        return $this->stream->tell();
    }

    public function streamEof()
    {
        return $this->stream->eof();
    }

    public function streamSeek($offset, $whence)
    {
        $this->stream->seek($offset, $whence);

        return true;
    }

    public function streamCast($cast_as)
    {
        $stream = clone($this->stream);

        return $stream->detach();
    }

    public function streamStat()
    {
        static $modeMap = [
            'r'  => 33060,
            'rb' => 33060,
            'r+' => 33206,
            'w'  => 33188,
            'wb' => 33188
        ];

        return [
            'dev'     => 0,
            'ino'     => 0,
            'mode'    => $modeMap[$this->mode],
            'nlink'   => 0,
            'uid'     => 0,
            'gid'     => 0,
            'rdev'    => 0,
            'size'    => $this->stream->getSize() ?: 0,
            'atime'   => 0,
            'mtime'   => 0,
            'ctime'   => 0,
            'blksize' => 0,
            'blocks'  => 0
        ];
    }

    public function urlStat($path, $flags)
    {
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