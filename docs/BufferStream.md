# BufferStream

内存 FIFO 缓冲流。写入填充缓冲区，读取从头部拿走字节。不包装底层 PHP 资源。

> 命名空间：`Fize\Stream\Protocol`
> 源文件：`src/Protocol/BufferStream.php`
> 实现：`Psr\Http\Message\StreamInterface`（**不**继承 [StreamDecorator](StreamDecorator.md)）
> 相关：[PumpStream](PumpStream.md)

[`PumpStream`](PumpStream.md) 内部用本类暂存回调多出来的数据。

---

## 能力

| 能力 | 值 |
|------|-----|
| 可读 / 可写 | 均为 `true` |
| 可 seek | `false`；`seek()` / `rewind()` / `tell()` 抛 `RuntimeException` |
| `eof()` | 缓冲区长度为 0 |
| `getSize()` | 当前缓冲字节数 `strlen($buffer)` |
| `detach()` | 等价 `close()`，返回 `null` |

---

## 构造

### `__construct(int $hwm = 16384)`

`$hwm`（high water mark）是高水位：缓冲长度 **已经 ≥ `$hwm`** 时，再 `write()` 返回 `0` 且不写入。

默认 16384。元数据键 `hwm` 可取出该值。

```php
use Fize\Stream\Protocol\BufferStream;

$stream = new BufferStream(10);
echo $stream->write('0123456789'); // 10
echo $stream->write('ABC');        // 0，已满
echo $stream->read(5);             // 01234
```

---

## 读写语义

### `write(string $string): int`

- `strlen($buffer) >= $hwm` → 返回 `0`，内容不变
- 否则整段追加，返回 `strlen($string)`

注意：判断只看**写入前**是否已满。缓冲区还剩 1 字节时仍可能一次写入很长的字符串，实际长度会超过 `$hwm`。高水位是「满了就拒写」，不是「截断到恰好 hwm」。

### `read(int $length): string`

从缓冲头部取走最多 `$length` 字节。取完则缓冲变空。

### `getContents(): string` / `__toString(): string`

返回并**清空**全部缓冲。`__toString()` 直接调 `getContents()`，因此转字符串有破坏性。

---

## `getMetadata(?string $key = null)`

| `$key` | 返回 |
|--------|------|
| `null` | `['hwm' => int]` |
| `'hwm'` | 高水位整数 |
| 其他 | `null` |

---

## 典型用途

- 生产者写入、消费者按块读取的内存管道
- 为 [`PumpStream`](PumpStream.md) 缓存一次回调返回过多的数据
- 需要背压时：`write()` 返回 `0` 表示下游还没读走

[返回目录](index.md) · 上一章：[AppendStream](AppendStream.md) · 下一章：[CachingStream](CachingStream.md)
