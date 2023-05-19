<?php

namespace Tests;

use Fize\IO\FileF;
use Fize\Stream\StreamFilter;
use php_user_filter;
use PHPUnit\Framework\TestCase;

class TestStreamFilter extends TestCase
{

    public function testGets()
    {
        $filters = StreamFilter::gets();
        var_dump($filters);
        self::assertIsArray($filters);
    }

    public function testAppend()
    {
        $fp = fopen(dirname(__DIR__) . '/temp/testStreamFilterAppend.txt', 'w+');
        $res = StreamFilter::append($fp, 'string.rot13', STREAM_FILTER_WRITE);
        self::assertIsResource($res);
        $ff = new FileF($fp);
        $ff->write("This is a test\n");
        $ff->rewind();
        $ff->passthru();
        $ff->close();
    }

    public function testPrepend()
    {
        $fp = fopen(dirname(__DIR__) . '/temp/testStreamFilterPrepend.txt', 'w+');
        $res = StreamFilter::prepend($fp, 'string.rot13', STREAM_FILTER_WRITE);
        self::assertIsResource($res);
        $ff = new FileF($fp);
        $ff->write("This is a test\n");
        $ff->rewind();
        $ff->passthru();
        $ff->close();
    }

    public function testRegister()
    {
        $rst = StreamFilter::register('strtoupper', strtoupper_filter::class);
        var_dump($rst);
        self::assertTrue($rst);

        $fp = fopen(dirname(__DIR__) . '/temp/testStreamFilterRegister.txt', 'w+');
        StreamFilter::append($fp, 'strtoupper');
        $ff = new FileF($fp);
        $ff->write("Line1\n");
        $ff->write("Word - 2\n");
        $ff->write("Easy As 123\n");
        $ff->rewind();
        $content = $ff->read(1000);
        $ff->close();
        var_dump($content);
    }

    public function testFilterRemove()
    {
        $fp = fopen(dirname(__DIR__) . '/temp/testStreamFilterRemove.txt', 'w+');
        $filter = StreamFilter::append($fp, 'string.rot13', STREAM_FILTER_WRITE);
        $rst = StreamFilter::remove($filter);
        self::assertTrue($rst);
        $ff = new FileF($fp);
        $ff->write("This is a test\n");
        $ff->rewind();
        $ff->passthru();
        $ff->close();
    }

}


/**
 * 自定义过滤器
 *
 * 该过滤器功能为使所有字符串改为大写
 */
class strtoupper_filter extends php_user_filter
{
    public function filter($in, $out, &$consumed, $closing): int
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            $bucket->data = strtoupper($bucket->data);
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }
        return PSFS_PASS_ON;
    }
}