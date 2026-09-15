# StreamSocket

流套接字。继承 [`Stream`](Stream.md)，在 PSR-7 读写之外封装 `stream_socket_*`。

> 命名空间：`Fize\Stream`
> 源文件：`src/StreamSocket.php`
> 父类：[Stream](Stream.md)

底层仍是 PHP 流资源，因此 `read()` / `write()` / `close()` 等均可使用。失败的 socket 调用统一抛 `RuntimeException`（`shutdown()` 除外，它返回 `bool`）。

---

## 静态工厂

### `client(string $remote_socket, int &$errno = null, string &$errstr = null, float $timeout = null, int $flags = 4, $context = null): StreamSocket`

打开 Internet 或 Unix 域客户端套接字，对应 `stream_socket_client()`。

| 参数 | 类型 | 说明 |
|------|------|------|
| `$remote_socket` | `string` | 如 `tcp://example.com:80`、`unix:///tmp/app.sock` |
| `$errno` | `int` | 输出错误码 |
| `$errstr` | `string` | 输出错误信息 |
| `$timeout` | `float\|null` | 超时秒数；`null` 交给 PHP 默认值 |
| `$flags` | `int` | 默认 `4`，即 `STREAM_CLIENT_CONNECT`。还可按位或 `STREAM_CLIENT_ASYNC_CONNECT`、`STREAM_CLIENT_PERSISTENT` |
| `$context` | `resource\|null` | [`StreamContext`](StreamContext.md) 资源 |

失败抛 `RuntimeException: error on stream_socket_client`。

```php
use Fize\Stream\StreamSocket;

$fp = StreamSocket::client('tcp://example.com:80', $errno, $errstr, 30);
$fp->write("GET / HTTP/1.0\r\nHost: example.com\r\n\r\n");
while (!$fp->eof()) {
    echo $fp->read(1024);
}
$fp->close();
```

---

### `server(string $local_socket, int &$errno = null, string &$errstr = null, int $flags = 12, $context = null): StreamSocket`

创建服务端套接字，对应 `stream_socket_server()`。

| 参数 | 类型 | 说明 |
|------|------|------|
| `$local_socket` | `string` | `transport://target`，如 `tcp://0.0.0.0:8000` |
| `$flags` | `int` | 默认 `12`，即 `STREAM_SERVER_BIND \| STREAM_SERVER_LISTEN` |
| `$context` | `resource\|null` | 可选上下文 |

失败抛 `RuntimeException: error on stream_socket_server`。

源码注释里写的 `Stream::socketServer()` 是笔误，实际方法名是 `StreamSocket::server()`。

---

### `pair(int $domain, int $type, int $protocol): array`

对应 `stream_socket_pair()`，返回**两个 socket 资源**组成的数组（不是 `StreamSocket` 实例）。

| 参数 | 常用取值 |
|------|----------|
| `$domain` | `STREAM_PF_INET` / `STREAM_PF_INET6` / `STREAM_PF_UNIX` |
| `$type` | `STREAM_SOCK_STREAM` / `STREAM_SOCK_DGRAM` / `STREAM_SOCK_RAW` / `STREAM_SOCK_RDM` / `STREAM_SOCK_SEQPACKET` |
| `$protocol` | `STREAM_IPPROTO_TCP` / `STREAM_IPPROTO_UDP` / `STREAM_IPPROTO_ICMP` / `STREAM_IPPROTO_IP` / `STREAM_IPPROTO_RAW` |

失败抛 `RuntimeException: error on stream_socket_pair`。

```php
$sockets = StreamSocket::pair(STREAM_PF_INET, STREAM_SOCK_STREAM, STREAM_IPPROTO_TCP);
```

---

## 实例方法

### `accept(float $timeout = null, string &$peername = null): StreamSocket`

在 `server()` 得到的监听套接字上接受连接，对应 `stream_socket_accept()`。

| 参数 | 说明 |
|------|------|
| `$timeout` | `null` 时不传超时给 PHP（用默认）；否则以秒为单位 |
| `$peername` | 输出对端地址（仅在传入 `$timeout` 时才会交给底层函数） |

注意：源码只在 `$timeout !== null` 时把 `$peername` 传给 `stream_socket_accept`。需要对端名时必须同时传超时。

返回新的 `StreamSocket`。失败抛 `RuntimeException: error on stream_socket_accept`。本调用会阻塞，直到有连接或超时。

```php
$server = StreamSocket::server('tcp://0.0.0.0:8000', $errno, $errstr);
$conn = $server->accept(30.0, $peer);
$conn->write('The local time is ' . date('Y-m-d H:i:s') . "\n");
$conn->close();
```

---

### `enableCrypto(bool $enable, int $crypto_type = null, $session_stream = null)`

对应 `stream_socket_enable_crypto()`。成功 `true`，失败 `false`，数据不够时可能返回 `0`。

| 参数 | 说明 |
|------|------|
| `$enable` | 是否开启加密 |
| `$crypto_type` | 如 `STREAM_CRYPTO_METHOD_TLS_CLIENT` |
| `$session_stream` | 可选，复用另一条流的 TLS 会话 |

`$session_stream === null` 时不把第四参数传给 PHP。

---

### `getName(int $want_peer): string`

对应 `stream_socket_get_name()`。

| `$want_peer` | 含义（PHP 按布尔使用） |
|--------------|------------------------|
| 真值 | 远端名 |
| 假值 | 本地名 |

失败抛 `RuntimeException: error on stream_socket_get_name`。

对 HTTP URL `open()` 得到的流也可以调用（测试里对 `https://` 用过）。

---

### `recvfrom(int $length, int $flags = 0, string &$address = null): string`

对应 `stream_socket_recvfrom()`。无论是否已 `connect` 都可以收。

| `$flags` | 含义 |
|----------|------|
| `0` | 普通收取并消费缓冲 |
| `STREAM_OOB` | 带外数据 |
| `STREAM_PEEK` | 偷看，不消费 |

`$address` 会被填成对端地址。失败抛 `RuntimeException: error on stream_socket_recvfrom`。

---

### `sendto(string $data, int $flags = 0, string $address = null): int`

对应 `stream_socket_sendto()`，返回发送字节数（源码注释写「结果代码」）。失败抛 `RuntimeException: error on stream_socket_sendto`。

`$address` 为 `null` 时仍作为第四参数传入（PHP 视为未指定对端，用于已连接套接字）。

---

### `shutdown(int $how): bool`

对应 `stream_socket_shutdown()`，关闭全双工连接的一侧或两侧。

| `$how` | 含义 |
|--------|------|
| `STREAM_SHUT_RD` | 不再接收 |
| `STREAM_SHUT_WR` | 不再发送 |
| `STREAM_SHUT_RDWR` | 两侧都关 |

只返回 `bool`，失败不抛异常。

---

## 继承

父类 [`Stream`](Stream.md) 的 PSR-7 方法、`open()`、`copyToString()`、`copyToStream()`、`createTemp()` 均可使用。`createTemp()` 因 `new static` 会得到 `StreamSocket` 实例，但临时流并不是套接字。

也可以 `new StreamSocket()` 后 `open('https://example.com', 'r')`，再调用 `getName()`。

[返回目录](index.md) · 上一章：[StreamContext](StreamContext.md) · 下一章：[StreamWrapper](StreamWrapper.md)
