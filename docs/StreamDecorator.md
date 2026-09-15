# StreamDecorator

流装饰器抽象基类。把 PSR-7 方法默认委托给内部的 `$stream`，子类只覆盖需要改变的行为。

> 命名空间：`Fize\Stream`
> 源文件：`src/StreamDecorator.php`
> 实现：`Psr\Http\Message\StreamInterface`
> 子类：[CachingStream](CachingStream.md)、[DroppingStream](DroppingStream.md)、[FnStream](FnStream.md)、[InflateStream](InflateStream.md)、[LazyOpenStream](LazyOpenStream.md)、[LimitStream](LimitStream.md)、[MultipartStream](MultipartStream.md)、[NoSeekStream](NoSeekStream.md)

[`AppendStream`](AppendStream.md)、[`BufferStream`](BufferStream.md)、[`PumpStream`](PumpStream.md) **不**继承本类，它们独立实现 `StreamInterface`。

---

## 概述

本类没有构造函数。子类负责把某个 `StreamInterface` 赋给 `protected $stream`。

`getContents()` **不**调用内部流的 `getContents()`，而是 `Stream::copyToString($this)`，因此会走**本对象**的 `read()` / `eof()`。子类覆盖 `read()` 后，`getContents()` 与 `__toString()` 会自动用新逻辑。

---

## 委托方法

下列方法原样转给 `$this->stream`：

| 方法 | 签名 |
|------|------|
| `close` | `(): void` |
| `detach` | `()` |
| `getSize` | `(): ?int` |
| `tell` | `(): int` |
| `eof` | `(): bool` |
| `isSeekable` | `(): bool` |
| `seek` | `(int $offset, int $whence = SEEK_SET): void` |
| `isWritable` | `(): bool` |
| `write` | `(string $string): int` |
| `isReadable` | `(): bool` |
| `read` | `(int $length): string` |
| `getMetadata` | `(?string $key = null)` |

`rewind()` 调用的是 **`$this->seek(0)`**，不是内部流的 `rewind()`。覆盖了 `seek()` 的子类（如 [`NoSeekStream`](NoSeekStream.md)、[`LimitStream`](LimitStream.md)）会一并影响 `rewind()`。

---

## `__toString(): string`

若 `isSeekable()` 为真则先 `seek(0)`，再 `getContents()`。与 [`Stream::__toString()`](Stream.md) 不同，这里**不**捕获异常。

---

## 自行扩展

```php
use Fize\Stream\StreamDecorator;
use Psr\Http\Message\StreamInterface;

class UpperReadStream extends StreamDecorator
{
    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;
    }

    public function read(int $length): string
    {
        return strtoupper($this->stream->read($length));
    }
}
```

只改写入上限、禁止 seek、截取窗口等需求，优先用现成子类，不必再写装饰器。

[返回目录](index.md) · 上一章：[Stream](Stream.md) · 下一章：[StreamFactory](StreamFactory.md)
