# PumpStream

从 PHP callable 泵取数据的只读流。每次 `read()` 若缓冲不够，就调用数据源要下一块。

> 命名空间：`Fize\Stream\Protocol`
> 源文件：`src/Protocol/PumpStream.php`
> 实现：`Psr\Http\Message\StreamInterface`（**不**继承 [StreamDecorator](StreamDecorator.md)）
> 相关：[BufferStream](BufferStream.md)、[StreamFactory](StreamFactory.md)

[`StreamFactory::createStreamFromIterator()`](StreamFactory.md) 与 [`creatStreamFromCallable()`](StreamFactory.md) 都返回本类。

---

## 能力

| 能力 | 值 |
|------|-----|
| 可读 | `true` |
| 可写 | `false`，`write()` 抛 `RuntimeException: Cannot write to a PumpStream` |
| 可 seek | `false`，`seek()` / `rewind()` 抛 `RuntimeException: Cannot seek a PumpStream` |
| `eof()` | 数据源已被置 `null`（读尽或 `detach`） |
| `getSize()` | 仅返回构造选项里的 `size`，不会实测 |
| `detach()` | 丢掉 source，`$tellPos = false`，返回 `null` |

---

## 构造

### `__construct(callable $source, array $options = [])`

`$source` 约定：

- 接受一个整数参数（本次还想要的字节数，仅作提示）
- 返回 **字符串** 表示一块数据
- 返回 `false` 或 `null` 表示结束（之后 `eof()` 为真）

`$options`：

| 键 | 说明 |
|----|------|
| `size` | 对外 `getSize()` 的值；不传则为 `null` |
| `metadata` | `getMetadata()` 的数组 |

内部用 [`BufferStream`](BufferStream.md)（默认高水位 16384）暂存多读到的数据。

```php
use Fize\Stream\Protocol\PumpStream;

$once = true;
$stream = new PumpStream(function (int $length) use (&$once) {
    if (!$once) {
        return null;
    }
    $once = false;
    return '123456789';
}, [
    'size'     => 9,
    'metadata' => ['kkk' => 'for test'],
]);

echo $stream->read(3);        // 123
echo $stream->getContents();  // 456789
```

回调每次多返回的部分留在缓冲里，下次 `read()` 先取缓冲。

---

## 读取过程

`read($length)`：

1. 从内部 `BufferStream` 取
2. 不够则 `pump($remaining)`：循环调用 `$source($length)`，`write` 进缓冲，直到凑够或 source 结束
3. 再从缓冲补读
4. `$tellPos` 累加实际得到的字节

`getContents()`：循环 `read(1000000)` 直到 `eof()`。

`__toString()`：`Stream::copyToString($this)`，异常返回 `''`。不会先 seek（也 seek 不了）。

---

## `getMetadata(?string $key = null)`

只返回构造时的 `$metadata`，没有 PHP `stream_get_meta_data()`。未知键为 `null`。

---

## 与工厂的关系

```php
use Fize\Stream\StreamFactory;

$factory = new StreamFactory();

$fromFn = $factory->creatStreamFromCallable(function () {
    static $n = 0;
    return $n++ < 1 ? 'hello' : null;
});

$fromIt = $factory->createStreamFromIterator(new ArrayIterator(['a', 'b', 'c']));
echo $fromIt->getContents(); // abc
```

迭代器版本的回调忽略 `$length`，每次产出 `current()` 一个元素。

---

## 使用注意

- 数据源是一次性的，读尽不能重来。需要回退请再包 [`CachingStream`](CachingStream.md)。
- `BufferStream` 高水位 16384：若单次回调返回极长字符串，仍会写入（见 [BufferStream 的超限语义](BufferStream.md)）。
- `detach()` 后 `tell()` 会返回 `false`（属性被设成 `false`），类型与 `int` 声明不一致，不要再读指针。
- 回调里不要再去读同一个 `PumpStream`，以免重入。

[返回目录](index.md) · 上一章：[NoSeekStream](NoSeekStream.md)
