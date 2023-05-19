<?php

namespace Fize\Stream;

use RuntimeException;

/**
 * 流过滤器
 */
class StreamFilter
{

    /**
     * 获取已注册的数据流过滤器列表
     * @return array
     */
    public static function gets(): array
    {
        return stream_get_filters();
    }

    /**
     * 将后置过滤器附加到流
     *
     * 参数 `$read_write` :
     *   可选值：STREAM_FILTER_READ、STREAM_FILTER_WRITE和/或STREAM_FILTER_ALL
     * @param resource $stream     流
     * @param string   $filtername 过滤器
     * @param int|null $read_write 读写模式
     * @param mixed    $params     相关参数
     * @return resource
     */
    public static function append($stream, string $filtername, int $read_write = null, $params = null)
    {
        $resource = stream_filter_append($stream, $filtername, $read_write, $params);
        if ($resource === false) {
            throw new RuntimeException('error on stream_filter_append');
        }
        return $resource;
    }

    /**
     * 将前置过滤器附加到流
     *
     * 参数 `$read_write` :
     *   可选值：STREAM_FILTER_READ、STREAM_FILTER_WRITE和/或STREAM_FILTER_ALL
     * @param resource $stream     流
     * @param string   $filtername 过滤器
     * @param int|null $read_write 读写模式
     * @param mixed    $params     相关参数
     * @return resource
     */
    public static function prepend($stream, string $filtername, int $read_write = null, $params = null)
    {
        $resource = stream_filter_prepend($stream, $filtername, $read_write, $params);
        if ($resource === false) {
            throw new RuntimeException('error on stream_filter_prepend');
        }
        return $resource;
    }

    /**
     * 注册用户定义的流过滤器
     * @param string $filtername 过滤器名称
     * @param string $classname  类全限定名
     * @return bool
     */
    public static function register(string $filtername, string $classname): bool
    {
        return stream_filter_register($filtername, $classname);
    }

    /**
     * 从资源流里移除某个过滤器
     * @param resource $stream_filter 需要被移除的资源流过滤器
     * @return bool
     */
    public static function remove($stream_filter): bool
    {
        return stream_filter_remove($stream_filter);
    }

}