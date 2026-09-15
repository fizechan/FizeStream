# InflateStream

解压流。假定输入是 **gzip**，跳过文件头后对剩余数据挂 `zlib.inflate` 读过滤器。

> 命名空间：`Fize\Stream\Protocol`
> 源文件：`src/Protocol/InflateStream.php`
> 父类：[StreamDecorator](StreamDecorator.md)
> 相关：[LimitStream](LimitStream.md)、[StreamWrapper](StreamWrapper.md)、[NoSeekStream](NoSeekStream.md)

构造完成后，读写走的是「已挂 inflate 过滤器」的 [`Stream`](Stream.md)（或外包 [`NoSeekStream`](NoSeekStream.md)）。本类自身不再覆盖其它方法。

---

## 构造过程

### `__construct(StreamInterface $stream)`

1. 从 `$stream` **读走 10 字节**，当作 gzip 头（ID1–OS）。
2. 若 FLG 的 `FNAME` 位置为 `08`（`substr(bin2hex($header), 6, 2) === '08'`），继续逐字节读到 `NUL`，计入文件名长度。
3. 用 [`LimitStream($stream, -1, 10 + $filenameHeaderLength)`](LimitStream.md) 跳过头。
4. [`StreamWrapper::getResource()`](StreamWrapper.md) 得到 PHP resource。
5. `stream_filter_append($resource, 'zlib.inflate', STREAM_FILTER_READ)`。
6. 若原 `LimitStream` 可 seek：`new Stream($resource)`；否则 `new NoSeekStream(new Stream($resource))`。

```php
use Fize\Stream\Stream;
use Fize\Stream\Protocol\InflateStream;

$raw = new Stream(fopen('/tmp/data.gz', 'r'));
$plain = new InflateStream($raw);
echo $plain->getContents();
```

---

## 适用数据

面向 **gzip**（魔数 `1f 8b`，10 字节固定头，可选文件名）。

不要用于：

- 裸 deflate / zlib（`zlib.inflate` 对包装格式敏感，且本类已先吃掉 10 字节）
- 未压缩文本
- 空流或不足 10 字节的流（头都读不齐）

`FNAME` 检测只看 FLG 是否恰好为 `0x08`，**没有**按位判断其它标志（`FEXTRA` / `FCOMMENT` / `FHCRC`）。带额外头字段的 gzip 可能切错偏移。

---

## 依赖

必须能把 PSR-7 流变成 resource，因此会注册 `fize://` 包装器。内部流还要可读。

解压后的 `seek` 能力取决于第 3 步的 `LimitStream` 是否可 seek（通常跟随原始流）。

[返回目录](index.md) · 上一章：[FnStream](FnStream.md) · 下一章：[LazyOpenStream](LazyOpenStream.md)
