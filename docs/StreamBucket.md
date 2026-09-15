# StreamBucket

流处理桶的静态封装，对应 `stream_bucket_*`。只在自定义 `php_user_filter::filter()` 里使用。

> 命名空间：`Fize\Stream`
> 源文件：`src/StreamBucket.php`
> 相关：[StreamFilter](StreamFilter.md)

PHP 把过滤器输入输出做成 brigade（桶队列）。`filter()` 从 `$in` 取出 bucket、改 `data`，再挂到 `$out`。

源码中多个方法带 `@todo`：PHP 官方文档对这组函数说明很少。下列签名以本库与 PHP 实际行为为准。

---

## 方法

### `append($brigade, object $bucket)`

对应 `stream_bucket_append()`，把 `$bucket` 接到队列尾。无返回值。

`$brigade` 是 `filter()` 的 `$in` / `$out`。

---

### `makeWriteable($brigade): object`

对应 `stream_bucket_make_writeable()`，从队列头取出可写 bucket。

典型对象字段：

| 字段 | 含义 |
|------|------|
| `$bucket->data` | 本块字符串 |
| `$bucket->datalen` | 字节长度 |

队列空时 PHP 返回 `null` / `false`，本方法返回类型写成 `object`，调用方应在 `while` 里判断真值。

---

### `bucketNew($stream, string $buffer): object`

对应 `stream_bucket_new()`，为当前流新建一只桶，内容为 `$buffer`。

`$stream` 是过滤器关联的流资源（`php_user_filter` 上通常可用 `$this->stream`）。

---

### `prepend($brigade, object $bucket)`

对应 `stream_bucket_prepend()`，把桶插到队列头。源码注释写「官方文档过于复杂，暂时不测试」。

---

## 过滤器中的用法

```php
use Fize\Stream\StreamBucket;

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
```

等价于直接调用 `stream_bucket_make_writeable` / `stream_bucket_append`。本类只是去掉函数前缀，没有额外校验。

`filter()` 的返回值应为 `PSFS_PASS_ON`、`PSFS_FEED_ME` 或 `PSFS_ERR_FATAL`，见 PHP 手册「php_user_filter」。

[返回目录](index.md) · 上一章：[StreamFilter](StreamFilter.md) · 下一章：[AppendStream](AppendStream.md)
