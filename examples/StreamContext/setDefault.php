<?php
require_once "../../vendor/autoload.php";

use Fize\Stream\StreamContext;

$default_opts = [
    'http'=>[
        'method'=>"GET",
        'header'=>"Accept-language: en\r\n" .
            "Cookie: foo=bar",
    ]
];

$context = StreamContext::setDefault($default_opts);

readfile('https://www.baidu.com');  // 使用了以上的默认上下文