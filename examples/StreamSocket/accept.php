<?php
require_once "../../vendor/autoload.php";

use Fize\Stream\StreamSocket;

$socket = StreamSocket::server("tcp://0.0.0.0:8000", $errno, $errstr);

while ($fpconn = $socket->accept(100)) {  // 接受连接
    $fpconn->write('The local time is ' . date('Y-m-d H:i:s') . "\n");
    $fpconn->close();
}
$socket->close();