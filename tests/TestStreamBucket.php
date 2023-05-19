<?php

namespace Tests;

use Fize\Stream\StreamBucket;
use PHPUnit\Framework\TestCase;
use stdClass;

class TestStreamBucket extends TestCase
{

    /**
     * @todo 因为PHP文档问题，暂时无法测试
     */
    public function testAppend()
    {
        $brigade = fopen('https://www.baidu.com', 'r');
        $bucket = new stdClass();
        StreamBucket::append($brigade, $bucket);
        self::assertTrue(true);
    }

    /**
     * @todo 因为PHP文档问题，暂时无法测试
     */
    public function testMakeWriteable()
    {
        $brigade = fopen('https://www.baidu.com', 'r');
        StreamBucket::makeWriteable($brigade);
        self::assertTrue(true);
    }

    /**
     * @todo 因为PHP文档问题，暂时无法测试
     */
    public function testBucketNew()
    {
        $stream = fopen('https://www.baidu.com', 'r');
        $buffer = 'test';
        StreamBucket::bucketNew($stream, $buffer);
        self::assertTrue(true);
    }

    /**
     * @todo 官方文档过于复杂，暂时不测试
     */
    public function testPrepend()
    {
        $brigade = fopen('https://www.baidu.com', 'r');
        $bucket = new stdClass();
        StreamBucket::prepend($brigade, $bucket);
        self::assertTrue(true);
        self::assertTrue(true);
    }

}
