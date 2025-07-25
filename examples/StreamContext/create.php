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

$context = StreamContext::create($opts);
var_dump($context);

$fp = new FileF();
$fp->open('https://www.baidu.com', 'r', false, $context);
$fp->passthru();
$fp->close();