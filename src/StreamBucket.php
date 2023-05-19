<?php

namespace Fize\Stream;

/**
 * 流处理桶
 */
class StreamBucket
{

    /**
     * 将桶附加到队列
     * @param resource $brigade 队列
     * @param object   $bucket  桶
     * @todo 本函数还未编写文档，仅有参数列表。
     */
    public static function append($brigade, object $bucket)
    {
        stream_bucket_append($brigade, $bucket);
    }

    /**
     * 从队列中返回一个bucket对象，用于操作
     * @param resource $brigade 队列
     * @return object
     * @todo 本函数还未编写文档，仅有参数列表。
     */
    public static function makeWriteable($brigade): object
    {
        return stream_bucket_make_writeable($brigade);
    }

    /**
     * 创建一个用于当前流的新桶
     * @param resource $stream 流
     * @param string   $buffer 缓存区
     * @return object
     * @todo 本函数还未编写文档，仅有参数列表。
     */
    public static function bucketNew($stream, string $buffer): object
    {
        return stream_bucket_new($stream, $buffer);
    }

    /**
     * 为队列准备桶
     * @param resource $brigade 队列
     * @param object   $bucket  桶
     * @todo 官方文档过于复杂，暂时不测试
     */
    public static function prepend($brigade, object $bucket)
    {
        stream_bucket_prepend($brigade, $bucket);
    }

}