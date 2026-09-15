# FizeStream 参考手册

FizeStream（Composer 包名 `fize/stream`）是 Fize 项目族群的 **Stream 定义库**。它把 PHP 原生流资源、套接字、上下文、过滤器、包装器封装成面向对象 API，并提供一套符合 **PSR-7 `StreamInterface`** 的实现与装饰器。`fize/http` 中的 `Fize\Http\Stream` / `Fize\Http\StreamFactory` 直接继承本库。

本手册按源码中的类拆章，入口为本页。阅读建议：先看本页的安装、架构与 PSR 对照，再按需进入对应类章节。

| 项目 | 说明 |
|------|------|
| 包名 | `fize/stream` |
| 命名空间 | `Fize\Stream` / `Fize\Stream\Protocol` |
| PHP | `>= 7.2.0` |
| 许可 | [MIT](https://opensource.org/licenses/MIT) |
| 仓库 | [github.com/fizechan/FizeStream](https://github.com/fizechan/FizeStream) |

---

## 安装

```bash
composer require fize/stream
```

### 运行要求

| 依赖 | 用途 |
|------|------|
| `psr/http-message` | PSR-7 `StreamInterface` |
| `psr/http-factory` | PSR-17 `StreamFactoryInterface` |

开发依赖 `fize/io`、`phpunit/phpunit` 仅用于测试与 `demo/`，生产安装不会强制拉取。

自动加载规则为 PSR-4：`Fize\Stream\` → `src/`。

---

## 源码布局

```
src/
├── Stream.php                 PSR-7 核心实现，包装 PHP 流资源
├── StreamDecorator.php        装饰器抽象基类
├── StreamFactory.php          PSR-17 工厂（含若干扩展方法）
├── StreamContext.php          stream_context_* 封装
├── StreamSocket.php           套接字流（继承 Stream）
├── StreamWrapper.php          自定义 fize:// 包装器 + wrapper 管理
├── StreamFilter.php           stream_filter_* 封装
├── StreamBucket.php           stream_bucket_* 封装
└── Protocol/
    ├── AppendStream.php       多流顺序拼接（只读）
    ├── BufferStream.php       内存 FIFO 缓冲
    ├── CachingStream.php      把顺序流缓存为可随机访问
    ├── DroppingStream.php     超过上限后丢弃写入
    ├── FnStream.php           用回调拼装流行为
    ├── InflateStream.php      gzip 解压流
    ├── LazyOpenStream.php     按文件路径打开的装饰流
    ├── LimitStream.php        限制可读区间
    ├── MultipartStream.php    multipart/form-data 聚合流
    ├── NoSeekStream.php       禁止 seek
    └── PumpStream.php         从回调泵取数据（只读）
```

`demo/` 与 `tests/` 通过 `.gitattributes` 标记为 `export-ignore`，打包发布时不会带上。

---

## 架构

本库分成两条线，互不继承，但可以配合使用。

```
PSR-7 流实现（均实现 StreamInterface）
├── Stream                          包装 PHP resource
│   └── StreamSocket                套接字读写 / TLS / accept
├── StreamDecorator（抽象）         把调用委托给内部 $stream
│   ├── CachingStream
│   ├── DroppingStream
│   ├── FnStream
│   ├── InflateStream
│   ├── LazyOpenStream
│   ├── LimitStream
│   ├── MultipartStream             内部再用 AppendStream 拼接各 part
│   └── NoSeekStream
├── AppendStream                    独立实现，顺序读多个流
├── BufferStream                    独立实现，内存队列
└── PumpStream                      独立实现，回调供数

工厂
└── StreamFactory                   创建上述流

PHP 流设施（不实现 StreamInterface）
├── StreamContext                   上下文 options / params
├── StreamWrapper                   注册 fize://，把 StreamInterface 暴露成 resource
├── StreamFilter                    挂载 / 注册过滤器
└── StreamBucket                    自定义过滤器里的 bucket 队列
```

选型简表：

| 场景 | 选用 |
|------|------|
| 包装已有 `fopen` 资源或打开本地/远程文件 | [`Stream`](Stream.md) |
| 从字符串、文件、resource、迭代器、回调创建流 | [`StreamFactory`](StreamFactory.md) |
| 给现有 `StreamInterface` 加一层行为 | [`StreamDecorator`](StreamDecorator.md) 的子类 |
| 拼接多个只读流 | [`AppendStream`](AppendStream.md) |
| 生产者-消费者式内存缓冲 | [`BufferStream`](BufferStream.md) |
| 让不可 `seek` 的顺序流可回退读取 | [`CachingStream`](CachingStream.md) |
| 写入不超过固定容量 | [`DroppingStream`](DroppingStream.md) |
| 只覆盖某几个 PSR-7 方法 | [`FnStream`](FnStream.md) |
| 解 gzip | [`InflateStream`](InflateStream.md) |
| 按路径打开文件（见该类说明） | [`LazyOpenStream`](LazyOpenStream.md) |
| 只暴露流的一段窗口 | [`LimitStream`](LimitStream.md) |
| 组装 `multipart/form-data` 请求体 | [`MultipartStream`](MultipartStream.md) |
| 强制不可随机访问 | [`NoSeekStream`](NoSeekStream.md) |
| 按需从回调/迭代器取数据 | [`PumpStream`](PumpStream.md) |
| HTTP/SSL/socket 上下文 | [`StreamContext`](StreamContext.md) |
| TCP/UDP/Unix 套接字 | [`StreamSocket`](StreamSocket.md) |
| 把 PSR-7 流交给需要 `resource` 的 PHP 函数 | [`StreamWrapper`](StreamWrapper.md) |
| 注册或挂载 `php_user_filter` | [`StreamFilter`](StreamFilter.md) + [`StreamBucket`](StreamBucket.md) |

---

## PSR 对照

| 规范 | 接口 | 本库实现 |
|------|------|----------|
| PSR-7 | `Psr\Http\Message\StreamInterface` | [`Stream`](Stream.md) 及 `Protocol` 下各流 |
| PSR-17 | `Psr\Http\Message\StreamFactoryInterface` | [`StreamFactory`](StreamFactory.md) |

本库**不**实现 PSR-7 的 Request / Response，也不实现 PSR-18 客户端。HTTP 消息层见 `fize/http`。

`StreamFactory` 在 PSR-17 三方法之外，额外提供：

- `createStreamFromIterator()`
- `createStreamFromObject()`
- `creatStreamFromCallable()`（方法名少一个 `e`，见下节）

---

## 快速开始

### 打开文件并读写

```php
use Fize\Stream\Stream;

$stream = new Stream();
$stream->open('/tmp/demo.txt', 'w+');
$stream->write('hello stream');
$stream->rewind();
echo $stream->getContents(); // hello stream
$stream->close();
```

也可以直接包装已有资源：

```php
use Fize\Stream\Stream;

$stream = new Stream(fopen('/tmp/demo.txt', 'r'));
echo $stream->read(32);
```

### 用工厂创建流

```php
use Fize\Stream\StreamFactory;

$factory = new StreamFactory();

$fromString = $factory->createStream('0123456789');
$fromFile   = $factory->createStreamFromFile('/tmp/demo.txt', 'r');
$fromRes    = $factory->createStreamFromResource(fopen('php://temp', 'r+'));
```

### 拼接多个流

```php
use Fize\Stream\StreamFactory;
use Fize\Stream\Protocol\AppendStream;

$factory = new StreamFactory();
$stream = new AppendStream([
    $factory->createStream('123'),
    $factory->createStream('456'),
]);
echo (string)$stream; // 123456
```

### 限制写入长度

```php
use Fize\Stream\Stream;
use Fize\Stream\Protocol\DroppingStream;

$inner = new Stream(fopen('php://temp', 'r+'));
$stream = new DroppingStream($inner, 5);
$stream->write('123456789');
$stream->rewind();
echo $stream->getContents(); // 12345
```

### 套接字客户端

```php
use Fize\Stream\StreamSocket;

$client = StreamSocket::client('tcp://example.com:80', $errno, $errstr, 30);
$client->write("GET / HTTP/1.0\r\nHost: example.com\r\n\r\n");
echo $client->getContents();
$client->close();
```

---

## 章节目录

### 核心流与工厂

| 章节 | 类 | 用途 |
|------|-----|------|
| [Stream](Stream.md) | `Fize\Stream\Stream` | PSR-7 核心实现；打开、读写、复制、临时流 |
| [StreamDecorator](StreamDecorator.md) | `Fize\Stream\StreamDecorator` | 装饰器抽象基类，默认委托内部流 |
| [StreamFactory](StreamFactory.md) | `Fize\Stream\StreamFactory` | PSR-17 工厂及迭代器 / 对象 / 回调扩展 |

### PHP 流设施

| 章节 | 类 | 用途 |
|------|-----|------|
| [StreamContext](StreamContext.md) | `Fize\Stream\StreamContext` | 创建与修改流上下文 |
| [StreamSocket](StreamSocket.md) | `Fize\Stream\StreamSocket` | Internet / Unix 套接字 |
| [StreamWrapper](StreamWrapper.md) | `Fize\Stream\StreamWrapper` | `fize://` 包装器与协议注册 |
| [StreamFilter](StreamFilter.md) | `Fize\Stream\StreamFilter` | 过滤器挂载与用户过滤器注册 |
| [StreamBucket](StreamBucket.md) | `Fize\Stream\StreamBucket` | 过滤器 brigades / bucket |

### 协议装饰器（`Fize\Stream\Protocol`）

| 章节 | 类 | 用途 |
|------|-----|------|
| [AppendStream](AppendStream.md) | `AppendStream` | 顺序拼接多个只读流 |
| [BufferStream](BufferStream.md) | `BufferStream` | 带高水位的内存缓冲 |
| [CachingStream](CachingStream.md) | `CachingStream` | 缓存远程/顺序流以便回退 |
| [DroppingStream](DroppingStream.md) | `DroppingStream` | 超过最大长度后丢弃写入 |
| [FnStream](FnStream.md) | `FnStream` | 用方法表定义或装饰流 |
| [InflateStream](InflateStream.md) | `InflateStream` | 跳过 gzip 头并 `zlib.inflate` |
| [LazyOpenStream](LazyOpenStream.md) | `LazyOpenStream` | 按文件名构造的装饰流 |
| [LimitStream](LimitStream.md) | `LimitStream` | 限制偏移与可读长度 |
| [MultipartStream](MultipartStream.md) | `MultipartStream` | 组装 multipart POST 体 |
| [NoSeekStream](NoSeekStream.md) | `NoSeekStream` | 禁止随机访问 |
| [PumpStream](PumpStream.md) | `PumpStream` | 从 callable 泵取只读数据 |

---

## 使用注意（摘要）

1. **`__toString()` 吞异常**：[`Stream`](Stream.md) / [`AppendStream`](AppendStream.md) / [`PumpStream`](PumpStream.md) 在转字符串失败时返回 `''`。调试请用 `getContents()`。
2. **`detach()` 之后几乎所有读写都会失败**：`Stream` 会抛 `RuntimeException: Stream is detached`。
3. **析构即关闭**：`Stream` 析构调用 `close()`。从外部 `fopen` 传入的资源也会被关掉；若需移交所有权，先 `detach()`。
4. **`StreamFactory::creatStreamFromCallable()` 少一个 `e`**：这是源码既有拼写，调用必须写 `creat` 而不是 `create`。
5. **[`LazyOpenStream`](LazyOpenStream.md) 名实不完全相符**：类注释写「IO 时才打开」，当前实现在构造函数里就 `fopen()`。
6. **[`MultipartStream`](MultipartStream.md) 的 `contents` 必须是字符串**：内部走 `StreamFactory::createStream(string $content)`，不能直接传入 `StreamInterface`。
7. **[`InflateStream`](InflateStream.md) 按 gzip 处理**：会先读 10 字节头（及可选文件名），再挂 `zlib.inflate`。不是 gzip 的数据不要用它。
8. **[`StreamWrapper::register()`](StreamWrapper.md) 无参数**，只注册 `fize` 协议。注册任意协议请用 `register2()`。
9. **读写能力看 `mode`**：`Stream` 用正则判断可读 / 可写，与 PHP `fopen` 模式一致。
10. **与 `fize/io` 的 [`Fize\IO\Stream`](https://github.com/fizechan/FizeIO) 不是同一个类**：后者面向 `resource` 的 `stream_*` 函数；本库面向 PSR-7。

---

下一章：[Stream](Stream.md)
