# CachingStream

可缓存流。把从「远程 / 顺序」流读过的字节写进本地缓冲，从而支持回退 `seek`，并允许在已读区域上写入。

> 命名空间：`Fize\Stream\Protocol`
> 源文件：`src/Protocol/CachingStream.php`
> 父类：[StreamDecorator](StreamDecorator.md)
> 相关：[Stream](Stream.md)、[FnStream](FnStream.md)

适合包装 [`PumpStream`](PumpStream.md)、`php://input` 等只能往前读的源。

---

## 构造

### `__construct(StreamInterface $stream, StreamInterface $target = null)`

| 参数 | 说明 |
|------|------|
| `$stream` | 远程/顺序源，保存在 `$remoteStream` |
| `$target` | 本地缓存；默认 [`Stream::createTemp()`](Stream.md)（`php://temp`） |

装饰器的 `$this->stream` 指向**缓存**，不是远程源。因此 `tell()` / `isSeekable()` / `isWritable()` 等未覆盖的方法走缓存流（`php://temp` 可读可写可 seek）。

```php
use Fize\Stream\Protocol\CachingStream;
use Fize\Stream\Protocol\LazyOpenStream;

$stream = new CachingStream(new LazyOpenStream('php://input', 'r+'));
```

---

## 覆盖的行为

### `read(int $length): string`

1. 先从缓存读
2. 不够的部分向远程再读 `$remaining + $skipReadBytes`
3. 若有 `$skipReadBytes`（先前在缓存上写入超出远程指针），丢掉远程开头对应字节
4. 新读到的远程数据追加写入缓存

这样重复 `read` 同一区间时走缓存，不必再碰远程。

### `seek(int $offset, int $whence = SEEK_SET): void`

先换算成绝对字节位置：

| `$whence` | 目标位置 |
|-----------|----------|
| `SEEK_SET` | `$offset` |
| `SEEK_CUR` | `tell() + $offset` |
| `SEEK_END` | 远程 `getSize()` + `$offset`；远程大小未知则先 [`cacheEntireStream()`](#cacheentirestream) |
| 其他 | `InvalidArgumentException: Invalid whence` |

目标超过已缓存长度时，循环 `read()` 向远程补齐；否则只在缓存上 `seek`。

### `write(string $string): int`

写入**缓存**。若写入越过远程当前指针，增加 `$skipReadBytes`，避免之后从远程重复读到已被覆盖的区间。

### `getSize(): ?int`

`max(缓存大小, 远程大小)`。任一为 `null` 时 `max` 仍可能得到另一侧的整数（PHP `max(null, 10)` 为 `10`）。

### `eof(): bool`

缓存与远程都 `eof()`。

### `close(): void`

两边都 `close()`。

---

## `cacheEntireStream()`（private）

用 [`FnStream`](FnStream.md) 做一个只统计 `strlen` 的 sink，再 [`Stream::copyToStream($this, $target)`](Stream.md)，把剩余内容全部读进缓存。`SEEK_END` 且远程 `getSize()` 为 `null` 时会走到这里。

---

## 示例

```php
use Fize\Stream\Protocol\CachingStream;
use Fize\Stream\Protocol\PumpStream;

$remote = new PumpStream(function () {
    static $once = true;
    if (!$once) {
        return null;
    }
    $once = false;
    return '0123456789';
});

$stream = new CachingStream($remote);
echo $stream->read(4);   // 0123
$stream->rewind();
echo $stream->read(10);  // 0123456789（后半来自远程，前半来自缓存）
```

---

## 使用注意

- 远程流若本身可写，本类的 `write()` 仍只打缓存，不写回远程。
- `detach()` 未覆盖，只分离缓存流，远程不会被 `detach`。需要两边都释放请 `close()`。
- 包装已 `close()` 的 `PumpStream` 时（测试里常见写法），远程已无数据源，缓存仍可独立读写。

[返回目录](index.md) · 上一章：[BufferStream](BufferStream.md) · 下一章：[DroppingStream](DroppingStream.md)
