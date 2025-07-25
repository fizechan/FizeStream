<?php
require_once "../../vendor/autoload.php";

use Fize\Stream\StreamSocket;

$server = StreamSocket::server("tcp://0.0.0.0:8000", $errno, $errstr);
$server2 = new StreamSocket($server);
$socket = $server2->accept();
$socket = new StreamSocket($socket);

/* Grab a packet (1500 is a typical MTU size) of OOB data */
echo "Received Out-Of-Band: '" . $socket->recvfrom(1500, STREAM_OOB) . "'\n";

/* Take a peek at the normal in-band data, but don't consume it. */
echo "Data: '" . $socket->recvfrom(1500, STREAM_PEEK) . "'\n";

/* Get the exact same packet again, but remove it from the buffer this time. */
echo "Data: '" . $socket->recvfrom(1500) . "'\n";