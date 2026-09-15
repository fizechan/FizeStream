# AppendStream

多流数据流：按顺序从一个或多个 `StreamInterface` 读取，对外表现为一条只读流。

> 命名空间：`Fize\Stream\Protocol`
> 源文件：`src/Protocol/AppendStream.php`
> 实现：`Psr\Http\Message\StreamInterface`（**不**继承 [StreamDecorator](StreamDecorator.md)）
> 相关：[MultipartStream](MultipartStream.md)、[StreamFactory](StreamFactory.md)

[`MultipartStream`](MultipartStream.md) 内部用本类拼接各个 part 与收尾边界。

---

## 能力

| 能力 | 值 |
|------|-----|
| 可读 | 始终 `true` |
| 可写 | 始终 `false`，`write()` 抛 `RuntimeException` |
| 可 seek | 仅当**所有**子流都 `isSeekable()`；且只支持 `SEEK_SET` |
| `getMetadata` | `$key === null` 返回 `[]`，否则 `null` |
| `detach()` | 分离所有子流后返回 `null` |

---

## 构造

### `__construct(array $streams = [])`

`$streams` 为 `StreamInterface[]`。逐个交给 [`addStream()`](#addstream)。

```php
use Fize\Stream\StreamFactory;
use Fize\Stream\Protocol\AppendStream;

$factory = new StreamFactory();
$stream = new AppendStream([
    $factory->createStream('123'),
    $factory->createStream('456'),
]);
echo (string)$stream; // 123456
```

---

## 特有方法

### `addStream(StreamInterface $stream)`

追加一条子流。

- 子流必须 `isReadable()`，否则 `InvalidArgumentException: Each stream must be readable`
- 任一子流不可 seek，则整条 `AppendStream` 变为不可 seek
- 构造之后仍可追加；已读过的指针 `$pos` / `$current` 不会因追加而重置

```php
$stream->addStream($factory->createStream('789'));
echo $stream->getContents(); // 视当前指针而定
```

---

## PSR-7 行为要点

### `read(int $length): string`

从当前子流读，不够则切到下一条，直到凑满 `$length` 或全部读完。子流 `read()` 返回 `null` 时也会前进到下一条。

### `seek(int $offset, int $whence = SEEK_SET): void`

1. 不可 seek → `RuntimeException: This AppendStream is not seekable`
2. `$whence !== SEEK_SET` → `RuntimeException: The AppendStream can only seek with SEEK_SET`
3. 所有子流 `rewind()`，失败则包装为 `Unable to seek stream {i} of the AppendStream`
4. 再通过循环 `read()` 前进到 `$offset`（每次最多 8096 字节）

因此 seek 是「全部回零再读过去」，开销与偏移成正比。

### `getSize(): ?int`

各子流 `getSize()` 求和；任一为 `null` 则整体 `null`。

### `eof(): bool`

没有子流，或已在最后一条且该条 `eof()`。

### `tell(): int`

累计已读字节 `$pos`，不是某个子流的 `tell()`。

### `close()` / `detach()`

关闭或分离全部子流，清空列表，并把 `$pos` / `$current` 置 0、`$seekable` 置回 `true`。

### `__toString(): string`

`rewind()` + `getContents()`，异常时返回 `''`。

---

## 使用注意

- 只读：不要对它 `write()`。
- 含 [`NoSeekStream`](NoSeekStream.md) 等不可 seek 子流时，整条不可 `seek` / `rewind`。
- `getContents()` 走 [`Stream::copyToString()`](Stream.md)，从**当前指针**读到末尾，不是总从头开始。

[返回目录](index.md) · 上一章：[StreamBucket](StreamBucket.md) · 下一章：[BufferStream](BufferStream.md)
