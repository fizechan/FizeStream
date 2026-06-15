<?php
require_once "../../vendor/autoload.php";

use Fize\IO\FileF;
use Fize\Stream\StreamFilter;

/**
 * 自定义过滤器
 *
 * 该过滤器功能为使所有字符串改为大写
 */
class StrToUpperFilter extends php_user_filter
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



$rst = StreamFilter::register("strtoupper", StrToUpperFilter::class);
var_dump($rst);
$fp = fopen('../temp/testStreamFilterRegister.txt', 'w+');
StreamFilter::append($fp, "strtoupper");

$fp2 = $fp;
StreamFilter::append($fp2, "strtoupper");
$fp = new FileF($fp2);
$fp->write("Line1\n");
$fp->write("Word - 2\n");
$fp->write("Easy As 123\n");
$fp->close();
readfile('../temp/testStreamFilterRegister.txt');
