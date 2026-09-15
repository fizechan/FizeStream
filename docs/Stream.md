# Stream

PSR-7 数据流的核心实现。包装一个 PHP `resource`（`fopen` 得到的流），并补上打开文件、流复制、创建临时流等扩展方法。

> 命名空间：`Fize\Stream`
> 源文件：`src/Stream.php`
> 实现：`Psr\Http\Message\StreamInterface`
> 子类：[StreamSocket](StreamSocket.md)
> 工厂：[StreamFactory](StreamFactory.md)

`fize/http` 的 `Fize\Http\Stream` 原样继承本类，没有新增成员。

---

## 概述

内部保存：

| 属性 | 含义 |
|------|------|
| `$stream` | 底层 PHP 流资源 |
| `$size` | 缓存的字节数；写入后置 `null`，下次再 `fstat` |
| `$seekable` / `$readable` / `$writable` | 由元数据 `mode` 正则判定 |
| `$uri` | `getMetadata('uri')`，`getSize()` 前会 `clearstatcache` |
| `$customMetadata` | 构造时传入的自定义元数据，优先于 `stream_get_meta_data()` |

可读模式：`/r|a\+|ab\+|w\+|wb\+|x\+|xb\+|c\+|cb\+/`

可写模式：`/a|w|r\+|rb\+|rw|x|c/`

析构时调用 `close()`。

---

## 构造与析构

### `__construct($stream = null, array $options = [])`

| 参数 | 类型 | 说明 |
|------|------|------|
| `$stream` | `resource\|null` | 已打开的流；也可稍后 [`open()`](#open) |
| `$options['size']` | `int` | 预先指定字节数，跳过 `fstat` |
| `$options['metadata']` | `array` | 自定义元数据 |

构造后立刻根据元数据刷新 `$seekable` / `$readable` / `$writable` / `$uri`。传入 `null` 时这些标志保持未设置（使用前需 `open()`）。

```php
use Fize\Stream\Stream;

$stream = new Stream(fopen('/tmp/demo.txt', 'r+'), [
    'size'     => 123,
    'metadata' => ['app' => 'fize'],
]);
```

### `__destruct()`

调用 `close()`，关闭并释放底层资源。

---

## PSR-7 方法

### `__toString(): string`

`seek(0)` 后 `getContents()`。任意异常被吞掉，返回 `''`。不可 `seek` 或已分离时得到空字符串，调试请直接调用 `getContents()`。

---

### `close(): void`

`detach()` 取出资源，若仍是 `stream` 类型则 `fclose()`。

---

### `detach()`

分离底层资源，之后本对象不可用。

- 返回原来的 `resource`，没有则 `null`。
- 清空 `$size`、`$uri`，并把 `$readable` / `$writable` / `$seekable` 置 `false`。
- **不**关闭资源，所有权交给调用方。

---

### `getSize(): ?int`

| 步骤 | 行为 |
|------|------|
| 已缓存 `$size` | 直接返回 |
| 已 `detach` | 返回 `null` |
| 有 `$uri` | `clearstatcache(true, $uri)` 后 `fstat` |
| `fstat` 含 `size` | 写入缓存并返回 |
| 否则 | `null` |

---

### `tell(): int`

当前指针，对应 `ftell()`。流已分离或 `ftell` 失败时抛 `RuntimeException`。

---

### `eof(): bool`

是否到末尾，对应 `feof()`。已分离时抛 `RuntimeException`。

---

### `isSeekable(): bool` / `isReadable(): bool` / `isWritable(): bool`

返回构造或 `open()` / `checkMetadata()` 时根据 `mode` 算出的标志。

---

### `seek(int $offset, int $whence = SEEK_SET): void`

对应 `fseek()`。已分离、不可 `seek`、或 `fseek` 返回 `-1` 时抛 `RuntimeException`。

### `rewind(): void`

等价于 `seek(0)`。

---

### `write(string $string): int`

写入并返回字节数。

- 已分离 / 不可写 / `fwrite` 失败：`RuntimeException`
- 成功后把 `$size` 置 `null`（长度需重新统计）

---

### `read(int $length): string`

最多读 `$length` 字节。

- `$length === 0` 返回 `''`
- `$length < 0`、已分离、不可读、`fread` 失败：`RuntimeException`

---

### `getContents(): string`

从当前位置读到末尾，对应 `stream_get_contents()`。已分离或失败时抛 `RuntimeException`。

---

### `getMetadata(?string $key = null)`

| `$key` | 已分离 | 未分离 |
|--------|--------|--------|
| `null` | `[]` | `$customMetadata + stream_get_meta_data()`（自定义键优先） |
| 有值 | `null` | 先查自定义，再查原生元数据，都没有则 `null` |

```php
$stream->getMetadata();           // 全部
$stream->getMetadata('seekable'); // bool
$stream->getMetadata('app');      // 自定义
```

---

## 扩展实例方法

### `open(string $file, string $mode = null, bool $use_include_path = false, $context = null)`

用 `fopen()` 打开路径或 URL，再刷新元数据。

| 参数 | 类型 | 说明 |
|------|------|------|
| `$file` | `string` | 文件路径或带协议的 URI |
| `$mode` | `string\|null` | `fopen` 模式 |
| `$use_include_path` | `bool` | 是否搜索 `include_path` |
| `$context` | `resource\|null` | [`StreamContext`](StreamContext.md) 的资源 |

行为：

- 当前已有流且未关闭：抛 `RuntimeException: The original stream has not been closed`
- 路径不含 `://`，或协议为 `file`，且模式属于 `r+` / `w` / `w+` / `a` / `a+` / `x` / `x+`：自动 `mkdir(dirname, 0777, true)`
- 不检查 `fopen` 失败（失败时 `$stream` 为 `false`，后续读写会出问题）

```php
$stream = new Stream();
$stream->open('/tmp/a/b/c.txt', 'w+'); // 不存在的目录会被创建
```

---

## 静态方法

### `copyToString(StreamInterface $stream, int $maxLen = -1): string`

把任意 PSR-7 流读成字符串。

| `$maxLen` | 行为 |
|-----------|------|
| `-1` | 每次 `read(1048576)` 直到 `eof` 或读到 `null` |
| `> 0` | 累计不超过该字节数 |

`read()` 返回空串或 `null` 会提前结束。

---

### `copyToStream(StreamInterface $source, StreamInterface $dest, int $maxLen = -1)`

流到流复制，缓冲 8192 字节。

- `$maxLen === -1`：直到源 `eof`，或某次 `write` 得到假值
- 否则：最多复制 `$maxLen` 字节

---

### `createTemp(array $options = []): StreamInterface`

`fopen('php://temp', 'r+')` 后 `new static($resource, $options)`。子类（如 `Fize\Http\Stream`）调用时得到的是子类实例。

```php
$tmp = Stream::createTemp();
$tmp->write('buffer');
$tmp->rewind();
```

---

## 完整示例

```php
use Fize\Stream\Stream;

$src = new Stream();
$src->open(__DIR__ . '/data.txt', 'r');

$dst = Stream::createTemp();
Stream::copyToStream($src, $dst);

$dst->rewind();
echo Stream::copyToString($dst, 100);
```

---

## 使用注意

- `__toString()` 会先 `seek(0)`，对不可随机访问的流会失败并得到 `''`。
- 传入外部 `fopen` 资源时，对象析构会 `fclose`。需要继续用原资源时先 `detach()`。
- `open()` 对 `fopen` 失败没有抛异常，调用方应保证路径与模式合法。
- `w` 模式被判为可写、不可读（符合 PHP：`w` 不能读）。

[返回目录](index.md) · 下一章：[StreamDecorator](StreamDecorator.md)
