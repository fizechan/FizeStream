<?php

namespace Tests;

use Fize\IO\File;
use Fize\Stream\StreamContext;
use PHPUnit\Framework\TestCase;

class TestStreamContext extends TestCase
{

    public function test__construct()
    {
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "Accept-language: en\r\n" .
                    "Cookie: foo=bar\r\n"
            ]
        ];
        $sc = StreamContext::create($opts);
        var_dump($sc);
        self::assertIsResource($sc);
    }

    public function testGet()
    {
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "Accept-language: en\r\n" .
                    "Cookie: foo=bar\r\n"
            ]
        ];
        $sc = new StreamContext(StreamContext::create($opts));
        var_dump($sc->get());
        self::assertIsResource($sc->get());
    }

    public function testGetOptions()
    {
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "Accept-language: en\r\n" .
                    "Cookie: foo=bar\r\n"
            ]
        ];
        $sc = new StreamContext(StreamContext::create($opts));
        $options = $sc->getOptions();
        var_dump($options);
        self::assertIsArray($options);
    }

    public function testGetParams()
    {
        $params = ['notification' => 'stream_notification_callback'];
        $sc = new StreamContext();
        $sc->setParams($params);
        $params = $sc->getParams();
        var_dump($params);
        self::assertIsArray($params);
    }

    public function testSetOption()
    {
        $sc = new StreamContext();
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "Accept-language: en\r\n" .
                    "Cookie: foo=bar\r\n"
            ]
        ];
        $rst = $sc->setOption($opts);
        var_dump($rst);
        self::assertTrue($rst);
        $fp = new File('https://www.baidu.com', 'r', false, $sc->get());
        $fp->fpassthru();
    }

    public function testSetParams()
    {
        $sc = new StreamContext();
        $params = ['notification' => 'stream_notification_callback'];
        $sc->setParams($params);

        $params = $sc->getParams();
        var_dump($params);
        self::assertIsArray($params);
    }

    public function testCreate()
    {
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "Accept-language: en\r\n" .
                    "Cookie: foo=bar\r\n"
            ]
        ];
        $sc = new StreamContext();
        $sc->setOption($opts);
        $context = $sc->get();
        self::assertIsResource($context);

        $fp = new File('https://www.baidu.com', 'r', false, $context);
        $fp->fpassthru();
    }

    public function testSetDefault()
    {
        $default_opts = [
            'http' => [
                'method' => 'GET',
                'header' => "Accept-language: en\r\n" .
                    'Cookie: foo=bar',
            ]
        ];

        $context = StreamContext::setDefault($default_opts);
        var_dump($context);
        self::assertIsResource($context);
        readfile('https://www.baidu.com');  // 使用了以上的默认上下文
    }

    public function testGetDefault()
    {
        $default_opts = [
            'http' => [
                'method' => 'GET',
                'header' => "Accept-language: en\r\n" . 'Cookie: foo=bar',
            ]
        ];


        $alternate_opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n" .
                    'Content-length: ' . strlen('baz=bomb'),
                'content' => 'baz=bomb'
            ]
        ];

        StreamContext::getDefault($default_opts);
        $alternate = StreamContext::create($alternate_opts);

        $fp = new File('https://www.baidu.com');

        $fp->readfile();  // 使用了默认上下文

        $fp->readfile(false, $alternate);  // 使用指定上下文

        self::assertTrue(true);
    }
}
