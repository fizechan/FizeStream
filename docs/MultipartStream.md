# MultipartStream

多组件流。把若干表单字段拼成 `multipart/form-data` 请求体，只读。

> 命名空间：`Fize\Stream\Protocol`
> 源文件：`src/Protocol/MultipartStream.php`
> 父类：[StreamDecorator](StreamDecorator.md)
> 相关：[AppendStream](AppendStream.md)、[StreamFactory](StreamFactory.md)

内部用 [`AppendStream`](AppendStream.md) 顺序拼接：每个 part 的头、正文、`CRLF`，最后 `--boundary--\r\n`。

---

## 构造

### `__construct(array $elements = [], string $boundary = null)`

| 参数 | 说明 |
|------|------|
| `$elements` | part 数组，见下表 |
| `$boundary` | 边界串；默认 `sha1(uniqid('', true))` |

每个 element **必须**含 `contents`、`name`，否则 `InvalidArgumentException: A 'contents' key is required`（或 `name`）。

| 键 | 类型 | 说明 |
|----|------|------|
| `contents` | `string` | 字段值。内部调用 `StreamFactory::createStream(string)`，**不能**直接传 `StreamInterface` |
| `name` | `string` | 表单字段名 |
| `filename` | `string` | 可选。有值时按文件字段处理，并尝试补 `Content-Type` |
| `headers` | `array` | 可选。自定义该 part 的头（键大小写不敏感地检测） |

`filename` 已设置但是空字符串时，会尝试用 `contents` 流的 `uri` 元数据当文件名（排除 `php://`）。因 `contents` 总是被做成 `php://temp`，这条回退通常不会生效。

```php
use Fize\Stream\Protocol\MultipartStream;

$stream = new MultipartStream([
    ['name' => 'title', 'contents' => 'hello'],
    [
        'name'     => 'file',
        'contents' => file_get_contents('/tmp/a.png'),
        'filename' => 'a.png',
        'headers'  => ['Content-Type' => 'image/png'],
    ],
], '----FizeBoundary');

echo $stream->getBoundary();
echo (string)$stream;
```

`isWritable()` 固定返回 `false`。

---

## `getBoundary(): string`

返回构造时确定的边界串。发 HTTP 请求时用于：

```http
Content-Type: multipart/form-data; boundary=----FizeBoundary
```

---

## 自动生成的头

`createElement()` 在调用方未提供时补：

| 头 | 规则 |
|----|------|
| `Content-Disposition` | 有 `filename`：`form-data; name=".."; filename="basename"`；否则 `form-data; name=".."`。`filename` 为字符串 `'0'` 也视为有文件名 |
| `Content-Length` | 正文流 `getSize()` 非空时写入 |
| `Content-Type` | 仅当有文件名，且能从扩展名映射到 MIME |

每个 part 的序列化格式：

```text
--{boundary}\r\n
{Header}: {value}\r\n
\r\n
{body}\r\n
```

全部 part 之后：

```text
--{boundary}--\r\n
```

---

## 扩展名 → MIME

`mimetypeFromExtension()` 内置一张常见后缀表（`jpg` / `png` / `pdf` / `zip` / `json` 等，来源注释指向 Apache `mime.types`）。未知后缀返回 `null`，此时不自动加 `Content-Type`。

---

## 使用注意

- 只读；不要 `write()`。
- **大文件不要先 `file_get_contents` 再塞进 `contents`**：当前 API 只接受字符串，会整份进内存。若需要流式文件 part，需改源码或自行拼 [`AppendStream`](AppendStream.md)。
- `filename` 会 `basename()`，路径不会进 `Content-Disposition`。
- 自定义 `headers` 的键会原样输出，检测是否已存在时才忽略大小写。

[返回目录](index.md) · 上一章：[LimitStream](LimitStream.md) · 下一章：[NoSeekStream](NoSeekStream.md)
