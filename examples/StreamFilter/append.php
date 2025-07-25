<?php
require_once "../../vendor/autoload.php";

use Fize\IO\FileF;
use Fize\Stream\StreamFilter;

$fp = fopen('../temp/testStreamFilterAppend.txt', 'w+');
StreamFilter::append($fp, "string.rot13", STREAM_FILTER_WRITE);
$fp = new FileF($fp);
$fp->write("This is a test\n");
$fp->rewind();
$fp->passthru();
$fp->close();
