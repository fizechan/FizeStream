# StreamWrapper

自定义流包装器。主要做两件事：

1. 注册 `fize://` 协议，把任意 `StreamInterface` 暴露成 PHP `resource`，供 `fread` / `stream_filter_append` / `fstat` 等使用。
2. 对 `stream_wrapper_register` / `restore` / `unregister` 做薄封装。

> 命名空间：`Fize\Stream`
> 源文件：`src/StreamWrapper.php`
> 相关：[Stream](Stream.md)、[InflateStream](InflateStream.md)

[`InflateStream`](InflateStream.md) 依赖本类：先 `getResource()` 拿到 resource，再挂 `zlib.inflate`。

---

## 把 PSR-7 流变成 resource

### `register()`

若 `fize` 尚未在 `stream_get_wrappers()` 中，则 `stream_wrapper_register('fize', StreamWrapper::class)`。

**无参数。** 不要写成 `register($protocol, $class)`——那是 [`register2()`](#register2)。

---

### `getResource(StreamInterface $stream)`

1. 调用 `register()`
2. 按能力选模式：可读可写 → `r+`；只读 → `r`；只写 → `w`；都不能 → `InvalidArgumentException`
3. `fopen('fize://stream', $mode, false, $context)`，上下文为 `['fize' => ['stream' => $stream]]`

返回 PHP 流资源；`fopen` 失败时为 `false`。

---

### `get(StreamInterface $stream): Stream`

`getResource()` 后再包一层 [`Stream`](Stream.md)。用于「PSR-7 流 → resource → 再回到本库 Stream」。

```php
use Fize\Stream\Stream;
use Fize\Stream\StreamWrapper;

$inner = new Stream();
$inner->open('/tmp/demo.txt', 'w+');
$inner->write('123456');
$inner->rewind();

$wrapped = StreamWrapper::get($inner);
echo $wrapped->read(6); // 123456
```

---

### `createStreamContext(StreamInterface $stream)`（protected）

`stream_context_create(['fize' => ['stream' => $stream]])`。`getResource()` 使用。

---

## 包装器回调（PHP 引擎调用）

下列方法实现 PHP 用户流包装器协议，一般不必手动调用。`fopen('fize://stream', ...)` 时引擎会走这里。

| 方法 | 行为 |
|------|------|
| `stream_open($path, $mode, $options, &$opened_path)` | 从上下文取出 `fize.stream`；没有则返回 `false`。`$opened_path = realpath($path)`（对 `fize://stream` 通常是 `false`） |
| `stream_close()` | 空实现，不关闭内部 PSR-7 流 |
| `stream_read($count)` | `$this->stream->read($count)` |
| `stream_write($data)` | `$this->stream->write($data)` |
| `stream_tell()` | `$this->stream->tell()` |
| `stream_eof()` | `$this->stream->eof()` |
| `stream_seek($offset, $whence)` | `$this->stream->seek(...)` 后返回 `true`（不捕获异常） |
| `stream_cast($cast_as)` | `STREAM_CAST_FOR_SELECT` 返回 `null`；否则 `clone` 内部流再 `detach()` |
| `stream_stat()` | 按 mode 填 `mode` 位，`size` 取 `getSize()` |
| `url_stat($path, $flags)` | 全 0 的占位 `stat` 数组 |

`stream_stat()` 的 mode 映射：

| 打开 mode | 数值 |
|-----------|------|
| `r` / `rb` | `33060` |
| `r+` | `33206` |
| `w` / `wb` | `33188` |

未列出的 mode（例如 `w+`）会触发未定义下标。`getResource()` 只会用 `r` / `r+` / `w`，正常路径没有问题。

`stream_cast(STREAM_CAST_FOR_SELECT)` 返回 `null`，因此 `stream_select()` 无法直接监视 `fize://` 流。

---

## 协议管理

### `gets(): array`

`stream_get_wrappers()`，已注册协议名列表。

---

### `register2(string $protocol, string $classname, int $flags = 0): bool`

对应 `stream_wrapper_register()`。`$flags` 为 `STREAM_IS_URL` 时表示 URL 协议，默认 `0`（本地流）。

```php
StreamWrapper::register2('txt', MyTxtWrapper::class);
```

---

### `restore(string $protocol): bool`

对应 `stream_wrapper_restore()`，恢复被卸载的内置包装器（如先 `unregister('http')` 再 `restore('http')`）。

---

### `unregister(string $protocol): bool`

对应 `stream_wrapper_unregister()`。

---

## 自定义包装器示例

```php
use Fize\Stream\StreamWrapper;

class MemoryWrapper
{
    public $context;
    private $data = '';
    private $pos = 0;

    public function stream_open($path, $mode, $options, &$opened_path): bool
    {
        return true;
    }

    public function stream_read($count): string
    {
        $chunk = substr($this->data, $this->pos, $count);
        $this->pos += strlen($chunk);
        return $chunk;
    }

    public function stream_write($data): int
    {
        $this->data .= $data;
        return strlen($data);
    }

    public function stream_tell(): int
    {
        return $this->pos;
    }

    public function stream_eof(): bool
    {
        return $this->pos >= strlen($this->data);
    }

    public function stream_seek($offset, $whence): bool
    {
        $this->pos = $offset;
        return true;
    }
}

StreamWrapper::register2('mem', MemoryWrapper::class);
$fp = fopen('mem://x', 'w+');
fwrite($fp, 'hello');
```

[返回目录](index.md) · 上一章：[StreamSocket](StreamSocket.md) · 下一章：[StreamFilter](StreamFilter.md)
