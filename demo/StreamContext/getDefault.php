<?php
require_once "../../vendor/autoload.php";

use Fize\IO\File;
use Fize\Stream\StreamContext;

$default_opts = [
    'http' => [
        'method' => "GET",
        'header' => "Accept-language: en\r\n" . "Cookie: foo=bar",
    ]
];

$alternate_opts = [
    'http' => [
        'method'  => "POST",
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n" .
            "Content-length: " . strlen("baz=bomb"),
        'content' => "baz=bomb"
    ]
];

StreamContext::getDefault($default_opts);
$alternate = StreamContext::create($alternate_opts);

$fp = new File('https://www.baidu.com');

$fp->readfile();  // 使用了默认上下文

$fp->readfile(false, $alternate);  // 使用指定上下文