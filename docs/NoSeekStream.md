# NoSeekStream

不可随机流。包装任意 `StreamInterface`，强制 `isSeekable() === false`，`seek()` 抛异常。

> 命名空间：`Fize\Stream\Protocol`
> 源文件：`src/Protocol/NoSeekStream.php`
> 父类：[StreamDecorator](StreamDecorator.md)
> 相关：[AppendStream](AppendStream.md)、[InflateStream](InflateStream.md)

用于测试「不可 seek」路径，或向调用方隐藏底层其实可 `seek` 的能力。

---

## 构造

### `__construct(StreamInterface $stream)`

```php
use Fize\Stream\Stream;
use Fize\Stream\Protocol\NoSeekStream;

$inner = new Stream(fopen('php://temp', 'r+'));
$stream = new NoSeekStream($inner);

$stream->isSeekable(); // false
// $stream->seek(0);   // RuntimeException: Cannot seek a NoSeekStream
```

---

## 覆盖

| 方法 | 行为 |
|------|------|
| `isSeekable()` | 恒 `false` |
| `seek($offset, $whence)` | 抛 `RuntimeException: Cannot seek a NoSeekStream` |

`rewind()` 在装饰器里调用 `$this->seek(0)`，因此同样会抛。

读写、`tell()`、`eof()`、`getSize()` 仍走底层。底层若可写，包装后依然可写。

---

## 对其它类的影响

- 放入 [`AppendStream`](AppendStream.md) 后，整条拼接流变为不可 seek。
- [`InflateStream`](InflateStream.md) 在跳过头之后的流不可 seek 时，会再包一层本类。

[返回目录](index.md) · 上一章：[MultipartStream](MultipartStream.md) · 下一章：[PumpStream](PumpStream.md)
