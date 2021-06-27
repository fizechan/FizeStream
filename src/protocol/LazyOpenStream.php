<?php

namespace fize\stream\protocol;

use Psr\Http\Message\StreamInterface;
use fize\stream\Stream;
use fize\stream\StreamDecorator;

/**
 * 惰性流
 *
 * 延迟地读写一个文件，该文件只有在流上进行IO操作后才打开。
 */
class LazyOpenStream extends StreamDecorator implements StreamInterface
{

    /**
     * @var string 要打开的文件
     */
    private $filename;

    /**
     * @var string 打开方式
     */
    private $mode;

    /**
     * 构造
     * @param string $filename 要打开的文件
     * @param string $mode     打开方式
     */
    public function __construct(string $filename, string $mode)
    {
        $this->filename = $filename;
        $this->mode = $mode;
        $this->stream = $this->createStream();
    }

    /**
     * 创建流
     * @return StreamInterface
     */
    protected function createStream()
    {
        $resource = fopen($this->filename, $this->mode);
        return new Stream($resource);
    }
}
