# StreamFactory

流工厂。实现 PSR-17 `StreamFactoryInterface`，并增加从迭代器、对象、回调创建流的方法。

> 命名空间：`Fize\Stream`
> 源文件：`src/StreamFactory.php`
> 实现：`Psr\Http\Message\StreamFactoryInterface`
> 相关：[Stream](Stream.md)、[PumpStream](PumpStream.md)

`fize/http` 的 `Fize\Http\StreamFactory` 原样继承本类。

---

## PSR-17 方法

### `createStream(string $content = ''): StreamInterface`

打开 `php://temp`（`r+`），写入 `$content`（非空时），`fseek(0)`，返回 [`Stream`](Stream.md)。得到的流可读可写。

```php
use Fize\Stream\StreamFactory;

$factory = new StreamFactory();
$stream = $factory->createStream('0123456789');
echo $stream->getContents(); // 0123456789
$stream->write('ABCD');
$stream->rewind();
echo $stream->getContents(); // 0123456789ABCD
```

---

### `createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface`

`fopen($filename, $mode)` 后包装为 [`Stream`](Stream.md)。默认 `'r'`：可读、不可写。

不检查 `fopen` 失败。

---

### `createStreamFromResource($resource): StreamInterface`

把已有 PHP 流资源交给 `new Stream($resource)`。

---

## 扩展方法

### `createStreamFromIterator(Iterator $iterator, array $options = []): StreamInterface`

把迭代器包装成 [`PumpStream`](PumpStream.md)。每次泵取：

1. `$iterator->valid()` 为假 → 返回 `false`（EOF）
2. 否则取 `current()`，再 `next()`，把当前值当作一块数据

`$options` 与 `PumpStream` 相同，可含 `size`、`metadata`。

迭代器的 `current()` 应返回字符串（或可拼进缓冲的标量）。`rewind()` 不会被调用，调用方需保证迭代器已就位。

```php
$it = new ArrayIterator(['first', 'second', 'last']);
$stream = $factory->createStreamFromIterator($it);
echo $stream->getContents(); // firstsecondlast
```

---

### `createStreamFromObject($object): StreamInterface`

| `$object` | 结果 |
|-----------|------|
| 已是 `StreamInterface` | 原样返回 |
| 实现了 `__toString` | `createStream((string)$object)` |
| 其他 | `InvalidArgumentException: Invalid resource type: ...` |

异常消息用 `gettype()`，对象会显示为 `object`。

---

### `creatStreamFromCallable(callable $function, array $options = []): StreamInterface`

从回调创建 [`PumpStream`](PumpStream.md)。

**方法名少一个 `e`（`creat` 不是 `create`）**，这是源码既有拼写，调用必须写该名字。

回调约定见 [PumpStream](PumpStream.md)：接受一个 `int $length`，返回字符串；`false` / `null` 表示结束。

```php
$fired = false;
$stream = $factory->creatStreamFromCallable(function () use (&$fired) {
    if (!$fired) {
        $fired = true;
        return '0123456789';
    }
    return null;
});
echo $stream->getContents(); // 0123456789
```

---

## 对照

| 来源 | 方法 | 实际类型 |
|------|------|----------|
| 字符串 | `createStream` | `Stream`（`php://temp`） |
| 文件路径 | `createStreamFromFile` | `Stream` |
| `resource` | `createStreamFromResource` | `Stream` |
| `Iterator` | `createStreamFromIterator` | `PumpStream` |
| 对象 | `createStreamFromObject` | 原流或 `Stream` |
| `callable` | `creatStreamFromCallable` | `PumpStream` |

[返回目录](index.md) · 上一章：[StreamDecorator](StreamDecorator.md) · 下一章：[StreamContext](StreamContext.md)
