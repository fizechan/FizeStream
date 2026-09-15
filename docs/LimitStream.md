# LimitStream

截止流。只暴露底层流从某偏移开始、最多若干字节的窗口。

> 命名空间：`Fize\Stream\Protocol`
> 源文件：`src/Protocol/LimitStream.php`
> 父类：[StreamDecorator](StreamDecorator.md)
> 相关：[InflateStream](InflateStream.md)

[`InflateStream`](InflateStream.md) 用 `new LimitStream($stream, -1, 10 + $filenameHeaderLength)` 跳过 gzip 头。

---

## 构造

### `__construct(StreamInterface $stream, int $limit = -1, int $offset = 0)`

| 参数 | 说明 |
|------|------|
| `$stream` | 底层流 |
| `$limit` | 窗口长度；**`-1` 表示从偏移起不限制** |
| `$offset` | 窗口起点（底层绝对位置） |

构造时调用 `setOffset()`：底层可 seek 则 `seek($offset)`；不可 seek 且当前指针已超过偏移则抛 `RuntimeException`；不可 seek 但指针还在偏移前，则 `read()` 吞掉中间字节。

```php
use Fize\Stream\Stream;
use Fize\Stream\Protocol\LimitStream;

$inner = new Stream(fopen('php://temp', 'r+'));
$inner->write('123456789');
$inner->rewind();

$stream = new LimitStream($inner, 2); // 只看到 "12"
echo $stream->read(10);               // 12
echo $stream->getSize();              // 2
```

---

## 覆盖的方法

### `read(int $length): string`

- `$limit === -1`：原样读底层
- 否则最多读到窗口右沿 `offset + limit`；已到右沿返回 `''`

### `seek(int $offset, int $whence = SEEK_SET): void`

只允许 `$whence === SEEK_SET` 且 `$offset >= 0`，否则 `RuntimeException`。

目标换算为底层位置 `$offset + $this->offset`，并截断到窗口右沿。`tell()` 返回的是**窗口内**相对位置。

```php
$stream = new LimitStream($inner, 2); // 窗口 [0, 2)
$stream->seek(5);                     // 被截到窗口末尾
echo $stream->read(1);                // ''
echo $stream->tell();                 // 2
```

### `tell(): int`

`底层 tell() - $offset`。

### `eof(): bool`

底层已结束，或（`$limit !== -1` 且底层指针 `>= offset + limit`）。

### `getSize(): ?int`

| 底层大小 | `$limit` | 返回 |
|----------|----------|------|
| `null` | 任意 | `null` |
| 已知 | `-1` | `底层大小 - offset` |
| 已知 | `>= 0` | `min(limit, 底层大小 - offset)` |

---

## 未覆盖的方法

`write()` 仍委托底层，**不受 `$limit` 约束**。本类限制的是读窗口。需要限制写入用 [`DroppingStream`](DroppingStream.md)。

`rewind()` 走装饰器的 `seek(0)`，会回到窗口起点（底层的 `$offset`），不是底层 0。

[返回目录](index.md) · 上一章：[LazyOpenStream](LazyOpenStream.md) · 下一章：[MultipartStream](MultipartStream.md)
