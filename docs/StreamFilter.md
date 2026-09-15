# StreamFilter

流过滤器静态封装，对应 `stream_filter_*` 与 `stream_get_filters()`。

> 命名空间：`Fize\Stream`
> 源文件：`src/StreamFilter.php`
> 相关：[StreamBucket](StreamBucket.md)、[InflateStream](InflateStream.md)

过滤器作用在 **PHP resource** 上，不是 `StreamInterface`。若只有 PSR-7 流，先用 [`StreamWrapper::getResource()`](StreamWrapper.md) 转成 resource。

---

## 方法

### `gets(): array`

已注册过滤器名称列表，对应 `stream_get_filters()`。常见内置名：`string.rot13`、`string.toupper`、`convert.iconv.*`、`zlib.inflate` 等。

---

### `append($stream, string $filtername, int $read_write = null, $params = null)`

把过滤器接到流的**末端**，对应 `stream_filter_append()`。

| 参数 | 类型 | 说明 |
|------|------|------|
| `$stream` | `resource` | 目标流 |
| `$filtername` | `string` | 过滤器名 |
| `$read_write` | `int\|null` | `STREAM_FILTER_READ` / `STREAM_FILTER_WRITE` / `STREAM_FILTER_ALL`；`null` 交给 PHP 默认 |
| `$params` | `mixed` | 过滤器参数 |

返回过滤器资源，供 [`remove()`](#remove) 使用。失败抛 `RuntimeException: error on stream_filter_append`。

```php
use Fize\Stream\StreamFilter;

$fp = fopen('/tmp/rot13.txt', 'w+');
$filter = StreamFilter::append($fp, 'string.rot13', STREAM_FILTER_WRITE);
fwrite($fp, "This is a test\n");
rewind($fp);
fpassthru($fp); // Guvf vf n grfg
fclose($fp);
```

---

### `prepend($stream, string $filtername, int $read_write = null, $params = null)`

接到流的**前端**，对应 `stream_filter_prepend()`。失败抛 `RuntimeException: error on stream_filter_prepend`。

同一条流上可叠多个过滤器，顺序：`prepend` 更靠近底层，`append` 更靠近用户读写。

---

### `register(string $filtername, string $classname): bool`

注册用户过滤器，对应 `stream_filter_register()`。`$classname` 须继承 `php_user_filter`，并实现 `filter($in, $out, &$consumed, $closing)`。

桶队列操作用 [`StreamBucket`](StreamBucket.md)。

```php
use Fize\Stream\StreamBucket;
use Fize\Stream\StreamFilter;

class StrToUpperFilter extends php_user_filter
{
    public function filter($in, $out, &$consumed, $closing): int
    {
        while ($bucket = StreamBucket::makeWriteable($in)) {
            $bucket->data = strtoupper($bucket->data);
            $consumed += $bucket->datalen;
            StreamBucket::append($out, $bucket);
        }
        return PSFS_PASS_ON;
    }
}

StreamFilter::register('strtoupper', StrToUpperFilter::class);
$fp = fopen('/tmp/up.txt', 'w+');
StreamFilter::append($fp, 'strtoupper');
fwrite($fp, "Line1\n");
rewind($fp);
echo stream_get_contents($fp); // LINE1
```

---

### `remove($stream_filter): bool`

对应 `stream_filter_remove()`。`$stream_filter` 是 `append()` / `prepend()` 的返回值。去掉后后续读写不再经过该过滤器。

---

## 与 InflateStream

[`InflateStream`](InflateStream.md) 内部直接调用 `stream_filter_append($resource, 'zlib.inflate', STREAM_FILTER_READ)`，没有走本类。效果与 `StreamFilter::append(..., 'zlib.inflate', STREAM_FILTER_READ)` 相同。

[返回目录](index.md) · 上一章：[StreamWrapper](StreamWrapper.md) · 下一章：[StreamBucket](StreamBucket.md)
