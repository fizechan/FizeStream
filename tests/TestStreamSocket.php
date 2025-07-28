<?php

namespace Tests;

use Fize\Stream\StreamSocket;
use PHPUnit\Framework\TestCase;

class TestStreamSocket extends TestCase
{

    /**
     * @todo 未实际测试
     */
    public function testEnableCrypto()
    {
        $client = StreamSocket::client('tcp://127.0.0.1:1935', $errno, $errstr, 30);
        $rst = $client->enableCrypto(true, STREAM_CRYPTO_METHOD_SSLv23_CLIENT);
        self::assertTrue($rst);
        $rst = $client->enableCrypto(false);
        self::assertTrue($rst);
    }

    public function testGetName()
    {
        $socket = new StreamSocket();
        $socket->open('https://www.baidu.com', 'r');
        $rst = $socket->getName(true);
        var_dump($rst);
        $rst = $socket->getName(false);
        var_dump($rst);
        self::assertIsString($rst);
    }

    public function testPair()
    {
        $sockets = StreamSocket::pair(STREAM_PF_INET, STREAM_SOCK_STREAM, STREAM_IPPROTO_TCP);
        var_dump($sockets);
        self::assertIsArray($sockets);
    }

    /**
     * 该测试为阻断方式，请在命令行中中使用telnet进行效果查看
     */
    public function testRecvfrom()
    {
        $server = StreamSocket::server('tcp://127.0.0.1:1935', $errno, $errstr);
        $client = $server->accept();

        /* Grab a packet (1500 is a typical MTU size) of OOB data */
        echo "Received Out-Of-Band: '" . $client->recvfrom(1500, STREAM_OOB) . "'\n";

        /* Take a peek at the normal in-band data, but don't consume it. */
        echo "Data: '" . $client->recvfrom(1500, STREAM_PEEK) . "'\n";

        /* Get the exact same packet again, but remove it from the buffer this time. */
        echo "Data: '" . $client->recvfrom(1500) . "'\n";

        self::assertIsString($client->recvfrom(1500));
    }

    public function testSendto()
    {
        $client = StreamSocket::client('tcp://www.baidu.com:80', $errno, $errstr, 30);
        $rst = $client->sendto('Out of Band data.', STREAM_OOB);
        var_dump($rst);
        self::assertIsInt($rst);
    }

    public function testShutdown()
    {
        $client = StreamSocket::client('tcp://www.baidu.com:80', $errno, $errstr, 30);
        $rst = $client->shutdown(STREAM_SHUT_WR);
        var_dump($rst);
        self::assertTrue($rst);
    }

    /**
     * 该测试为阻断方式，请在命令行中中使用telnet进行效果查看
     */
    public function testAccept()
    {
        $server = StreamSocket::server('tcp://0.0.0.0:8000', $errno, $errstr);

        while ($conn = $server->accept(100)) {
            $conn->write('The local time is ' . date('Y-m-d H:i:s') . "\n");
            $conn->close();
        }
        unset($socket);
    }

    public function testClient()
    {
        $fp = StreamSocket::client('tcp://www.baidu.com:80', $errno, $errstr, 30);
        var_dump($fp);
        self::assertNotNull($fp);
    }

    public function testSocketServer()
    {
        $socket = StreamSocket::server('tcp://0.0.0.0:8000', $errno, $errstr);
        var_dump($socket);
        self::assertNotNull($socket);
    }
}
