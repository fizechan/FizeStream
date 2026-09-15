# LazyOpenStream

按文件路径构造的装饰流。类注释写：「延迟地读写一个文件，该文件只有在流上进行 IO 操作后才打开。」

> 命名空间：`Fize\Stream\Protocol`
> 源文件：`src/Protocol/LazyOpenStream.php`
> 父类：[StreamDecorator](StreamDecorator.md)
> 相关：[Stream](Stream.md)

---

## 实际行为

当前实现**并不是惰性打开**。构造函数立刻调用 `createStream()` → `fopen($filename, $mode)` → `new Stream($resource)`。

```php
public function __construct(string $filename, string $mode)
{
    $this->filename = $filename;
    $this->mode = $mode;
    $this->stream = $this->createStream(); // 此处即 fopen
}
```

因此：

- 文件不存在且模式不能创建时，`fopen` 在构造阶段就失败
- 对象一创建，文件句柄已经占用
- 之后所有 PSR-7 方法只是委托给内部 [`Stream`](Stream.md)

名称与注释保留「Lazy」是历史设计，手册以源码为准。

---

## 构造

### `__construct(string $filename, string $mode)`

| 参数 | 说明 |
|------|------|
| `$filename` | 路径或 PHP 流 URI（如 `php://input`、`php://temp`） |
| `$mode` | `fopen` 模式 |

```php
use Fize\Stream\Protocol\LazyOpenStream;

$stream = new LazyOpenStream('/tmp/demo.txt', 'w+');
$stream->write('hello');
```

测试与 [`CachingStream`](CachingStream.md) 常用：

```php
new LazyOpenStream('php://input', 'r+');
new LazyOpenStream('php://temp', 'r+');
```

---

## `createStream()`（protected）

```php
$resource = fopen($this->filename, $this->mode);
return new Stream($resource);
```

不检查 `fopen` 失败。子类可覆盖以实现真正的延迟打开（例如第一次 `read`/`write` 再 `fopen`）。

---

## 和 Stream::open / StreamFactory 的差别

| 方式 | 何时打开 | 备注 |
|------|----------|------|
| `new Stream(); $s->open($file, $mode)` | 调用 `open()` 时 | 可先空构造 |
| `StreamFactory::createStreamFromFile` | 调用工厂时 | 与本类同样立即 `fopen` |
| `new LazyOpenStream($file, $mode)` | **构造时** | 接口像装饰器，实现等价于立刻打开 |

需要延迟到首次 IO，请自行覆盖 `createStream()` 并推迟赋值 `$this->stream`，或改用工厂在真正使用前再创建。

[返回目录](index.md) · 上一章：[InflateStream](InflateStream.md) · 下一章：[LimitStream](LimitStream.md)
