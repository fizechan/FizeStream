# FnStream

方法流。用「方法名 → callable」表实现 `StreamInterface`，或在已有流上只覆盖其中几个方法。

> 命名空间：`Fize\Stream\Protocol`
> 源文件：`src/Protocol/FnStream.php`
> 父类：[StreamDecorator](StreamDecorator.md)

继承装饰器只是为了实现接口；构造时**不会**设置 `$this->stream`。所有 PSR-7 方法都改走 `$this->methods[...]`。

---

## 必须实现的槽位

```text
__toString, close, detach, rewind, getSize,
tell, eof, isSeekable, seek, isWritable,
write, isReadable, read, getContents, getMetadata
```

直接 `new FnStream($methods)` 时，**调用到的槽位必须存在**，否则 `call_user_func` 会报错。未调用的槽位可以省略。

用 [`decorate()`](#decorate) 时，缺的槽位自动绑到原流的对应方法。

---

## 构造与生命周期

### `__construct(array $methods)`

`$methods` 的键为方法名，值为 `callable`。

```php
use Fize\Stream\Protocol\FnStream;

$stream = new FnStream([
    'read' => function (int $length) {
        return (string)$length;
    },
    'getContents' => function () {
        return '123456';
    },
    '__toString' => function () {
        return '123456';
    },
]);
echo $stream->read(10); // 10
```

### `__destruct()`

若定义了 `close`，析构时调用它。

### `__wakeup()`

抛 `LogicException: FnStream should never be unserialized`。禁止反序列化。

---

## `decorate(StreamInterface $stream, array $methods): FnStream`

在 `$stream` 上覆盖部分方法，其余槽位填 `[$stream, $methodName]`。

```php
use Fize\Stream\Stream;
use Fize\Stream\Protocol\FnStream;

$inner = new Stream(fopen('/tmp/demo.txt', 'r')); // 文件只读，isWritable() === false

$stream = FnStream::decorate($inner, [
    'isWritable' => function () {
        return true; // 只改这个判断，并不真的让底层可写
    },
]);

$stream->isWritable(); // true
$stream->rewind();
echo $stream->getContents(); // 仍读原文件
```

[`CachingStream`](CachingStream.md) 在 `SEEK_END` 需要整流缓存时，会 `new FnStream(['write' => 'strlen'])` 当作只计长度的 sink。

---

## 各方法

全部是 `call_user_func($this->methods['xxx'], ...)`：

| 方法 | 额外参数 |
|------|----------|
| `seek` | `$offset`, `$whence` |
| `write` | `$string` |
| `read` | `$length` |
| `getMetadata` | `$key` |
| 其余无参槽位 | 无 |

没有「未定义则回退装饰器」的逻辑；没填的键一调用就失败。

---

## 使用注意

- 覆盖 `isWritable()` 不会让底层真的可写，只改返回值。
- 自己实现的 `close` 负责释放资源；析构会再调一次，注意幂等。
- 不要 `serialize()` / `unserialize()` 本类。

[返回目录](index.md) · 上一章：[DroppingStream](DroppingStream.md) · 下一章：[InflateStream](InflateStream.md)
