<?php


namespace fize\stream;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * 流工厂类
 */
class StreamFactory implements StreamFactoryInterface
{

    /**
     * 从字符串创建一个流
     * @param string $content 字符串
     * @return StreamInterface
     */
    public function createStream(string $content = ''): StreamInterface
    {
        return Stream::create($content);
    }

    /**
     * 通过现有文件创建一个流
     * @param string $filename 用作流基础的文件名或 URI
     * @param string $mode 用于打开基础文件名或流的模式
     * @return StreamInterface
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return new Stream($filename, $mode);
    }

    /**
     * 通过现有资源创建一个流
     * @param resource $resource 用作流的基础的 PHP 资源
     * @return StreamInterface
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return Stream::create($resource);
    }
}
