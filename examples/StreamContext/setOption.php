<?php
require_once "../../vendor/autoload.php";

use Fize\IO\FileF;
use Fize\Stream\StreamContext;

$opts = [
    'http' => [
        'method' => "GET",
        'header' => "Accept-language: en\r\n" .
            "Cookie: foo=bar\r\n"
    ]
];

$context = new StreamContext();

$rst = $context->setOption($opts);
var_dump($rst);

$fp = new FileF();
$fp->open('https://www.baidu.com', 'r', false, StreamContext::create($opts));
$fp->passthru();
$fp->close();