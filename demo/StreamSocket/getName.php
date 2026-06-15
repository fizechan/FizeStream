<?php
require_once "../../vendor/autoload.php";

use Fize\Stream\StreamSocket;

$socket = new StreamSocket();
$socket->open('https://www.baidu.com', 'r');

// 远程
$rst = $socket->getName(true);
var_dump($rst);

// 本地
$rst = $socket->getName(false);
var_dump($rst);
