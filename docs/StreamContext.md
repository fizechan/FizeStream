# StreamContext

PHP 流上下文的面向对象封装，对应 `stream_context_*`。

> 命名空间：`Fize\Stream`
> 源文件：`src/StreamContext.php`

本类**不**实现 `StreamInterface`。得到的 `resource` 可传给 `fopen`、[`Stream::open()`](Stream.md)、[`StreamSocket::client()`](StreamSocket.md) 等。

---

## 构造

### `__construct($context = null)`

| 参数 | 类型 | 说明 |
|------|------|------|
| `$context` | `resource\|null` | 已有上下文；`null` 时使用 [`getDefault()`](#getdefault) |

```php
use Fize\Stream\StreamContext;

$ctx = new StreamContext(); // 进程默认上下文
$ctx = new StreamContext(StreamContext::create([
    'http' => [
        'method' => 'GET',
        'header' => "Accept-language: en\r\n",
    ],
]));
```

---

## 实例方法

### `get()`

返回内部上下文资源。源码标注「请谨慎使用」——调用方拿到的是同一份 resource，修改会影响本对象。

---

### `getOptions(): array`

对应 `stream_context_get_options()`。二维结构：`$arr['wrapper']['option']`。

---

### `getParams(): array`

对应 `stream_context_get_params()`。除 `options` 外还可含 `notification` 等。

---

### `setOption(array $options): bool`

对应 `stream_context_set_option()` 的「整表」形式（只传数组，不传 wrapper/option/value 三参数形式）。

```php
$ctx = new StreamContext();
$ctx->setOption([
    'http' => [
        'method' => 'GET',
        'header' => "Accept-language: en\r\nCookie: foo=bar\r\n",
    ],
]);
```

---

### `setParams(array $params): bool`

对应 `stream_context_set_params()`。例如：

```php
$ctx->setParams(['notification' => 'stream_notification_callback']);
```

---

## 静态方法

### `create(array $options = null, array $params = null)`

对应 `stream_context_create()`，返回上下文 **resource**（不是本类实例）。

| 参数 | 格式 |
|------|------|
| `$options` | `$arr['wrapper']['option'] = $value` |
| `$params` | `$arr['parameter'] = $value`，见 PHP「上下文参数」文档 |

```php
$resource = StreamContext::create([
    'http' => [
        'method' => 'GET',
        'header' => "Accept-language: en\r\nCookie: foo=bar\r\n",
    ],
]);

$stream = new \Fize\Stream\Stream();
$stream->open('https://example.com', 'r', false, $resource);
```

---

### `setDefault(array $options)`

对应 `stream_context_set_default()`，改进程默认上下文并返回该 resource。之后未显式传 context 的 `file_get_contents` / `readfile` 等会用到它。

---

### `getDefault(array $options = [])`

对应 `stream_context_get_default()`。传入 `$options` 时会合并进默认上下文（副作用），并返回默认上下文资源。

---

## 与默认上下文的关系

构造函数在 `$context === null` 时调用 `self::getDefault()`，因此 `new StreamContext()` 拿到的是**进程默认上下文**，不是一份独立副本。对其 `setOption()` 会改默认上下文。

需要隔离的上下文请先 `create()` 再传入构造函数：

```php
$isolated = new StreamContext(StreamContext::create($opts));
```

[返回目录](index.md) · 上一章：[StreamFactory](StreamFactory.md) · 下一章：[StreamSocket](StreamSocket.md)
