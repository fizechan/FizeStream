# DroppingStream

丢弃流。底层流的 `getSize()` 达到上限后，再写入的数据被丢掉（或只写入剩余额度）。

> 命名空间：`Fize\Stream\Protocol`
> 源文件：`src/Protocol/DroppingStream.php`
> 父类：[StreamDecorator](StreamDecorator.md)

只覆盖 `write()`。读、seek、大小等全部委托给内部流。

---

## 构造

### `__construct(StreamInterface $stream, int $maxLength)`

| 参数 | 说明 |
|------|------|
| `$stream` | 实际存储 |
| `$maxLength` | 允许写入的最大字节数（按底层 `getSize()` 计） |

```php
use Fize\Stream\Stream;
use Fize\Stream\Protocol\DroppingStream;

$inner = new Stream(fopen('php://temp', 'r+'));
$stream = new DroppingStream($inner, 5);
$stream->write('123456789');
$stream->rewind();
echo $stream->getContents(); // 12345
```

---

## `write(string $string): int`

```
$diff = $maxLength - $stream->getSize()
```

| 条件 | 行为 |
|------|------|
| `$diff <= 0` | 返回 `0`，不写 |
| `strlen($string) < $diff` | 整段写入 |
| 否则 | 只写 `substr($string, 0, $diff)` |

依赖底层 `getSize()`。若底层返回 `null`，`$diff` 变成数字与 `null` 的运算结果，行为不可靠。请包装能报告大小的流（如 [`Stream`](Stream.md) 打开的 `php://temp`）。

---

## 和 BufferStream 的区别

| | DroppingStream | [BufferStream](BufferStream.md) |
|--|----------------|-------------------------------|
| 存储 | 任意底层流 | 内存字符串 |
| 超限 | 截断本次写入 | 整次拒绝（返回 0） |
| 可读窗口 | 底层决定 | FIFO，读走即删除 |
| seek | 跟随底层 | 不支持 |

需要「文件/临时流 + 硬上限」用本类；需要「队列 + 高水位背压」用 `BufferStream`。

[返回目录](index.md) · 上一章：[CachingStream](CachingStream.md) · 下一章：[FnStream](FnStream.md)
