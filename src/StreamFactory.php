<?php

namespace fize\stream;

use InvalidArgumentException;
use Iterator;
use fize\stream\protocol\PumpStream;
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
        $file = fopen('php://temp', 'r+');
        if ($content !== '') {
            fwrite($file, $content);
            fseek($file, 0);
        }
        return new Stream($file);
    }

    /**
     * 通过现有文件创建一个流
     * @param string $filename 用作流基础的文件名或 URI
     * @param string $mode     用于打开基础文件名或流的模式
     * @return StreamInterface
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        $resource = fopen($filename, $mode);
        return new Stream($resource);
    }

    /**
     * 通过现有资源创建一个流
     * @param resource $resource 用作流的基础的 PHP 资源
     * @return StreamInterface
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }

    /**
     * 通过迭代器创建一个流
     * @param Iterator $iterator 迭代器
     * @param array    $options  选项
     * @return StreamInterface
     */
    public function createStreamFromIterator(Iterator $iterator, array $options = []): StreamInterface
    {
        return new PumpStream(function () use ($iterator) {
            if (!$iterator->valid()) {
                return false;
            }
            $result = $iterator->current();
            $iterator->next();
            return $result;
        }, $options);
    }

    /**
     * 通过对象创建一个流
     * @param mixed $object 任意实现了 StreamInterface 接口 或者 __toString 方法的对象
     * @return StreamInterface
     */
    public function createStreamFromObject($object): StreamInterface
    {
        if ($object instanceof StreamInterface) {
            return $object;
        }
        if (!method_exists($object, '__toString')) {
            throw new InvalidArgumentException('Invalid resource type: ' . gettype($object));
        }
        return $this->createStream((string)$object);
    }

    /**
     * 通过回调函数创建一个流
     * @param callable $function 回调函数
     * @param array    $options  选项
     * @return StreamInterface
     */
    public function creatStreamFromCallable(callable $function, array $options = []): StreamInterface
    {
        return new PumpStream($function, $options);
    }
}
