<?php

namespace Fize\Stream;

/**
 * 流上下文
 */
class StreamContext
{

    /**
     * @var resource 资源流上下文
     */
    protected $context;

    /**
     * 初始化
     * @param resource $context 资源流上下文
     */
    public function __construct($context = null)
    {
        if (is_null($context)) {
            $context = self::getDefault();
        }
        $this->context = $context;
    }

    /**
     * 返回当前上下文
     * @notice 请谨慎使用
     * @return resource
     */
    public function get()
    {
        return $this->context;
    }

    /**
     * 获取资源流/数据包/上下文的参数
     * @return array 返回一个包含有原参数的关联数组。
     */
    public function getOptions(): array
    {
        return stream_context_get_options($this->context);
    }

    /**
     * 从上下文检索参数
     * @return array 参数
     */
    public function getParams(): array
    {
        return stream_context_get_params($this->context);
    }

    /**
     * 对资源流、数据包或者上下文设置参数
     * @param array $options 选项
     * @return bool
     */
    public function setOption(array $options): bool
    {
        return stream_context_set_option($this->context, $options);
    }

    /**
     * 设置流/包装器/上下文的参数
     * @param array $params 参数
     * @return bool
     */
    public function setParams(array $params): bool
    {
        return stream_context_set_params($this->context, $params);
    }

    /**
     * 创建资源流上下文
     *
     * 参数 `$options` :
     *   格式如下：$arr['wrapper']['option'] = $value 。
     * 参数 `$params` :
     *   必须是 $arr['parameter'] = $value 格式的关联数组。
     *   请参考 context parameters 里的标准资源流参数列表。
     * @param array|null $options 选项，必须是一个二维关联数组。
     * @param array|null $params  参数
     * @return resource 返回资源流上下文，可直接使用
     */
    public static function create(array $options = null, array $params = null)
    {
        return stream_context_create($options, $params);
    }

    /**
     * 设置默认流上下文
     * @param array $options 选项
     * @return resource 返回默认流
     */
    public static function setDefault(array $options)
    {
        return stream_context_set_default($options);
    }

    /**
     * 检索默认流上下文
     * @param array $options 选项
     * @return resource
     */
    public static function getDefault(array $options = [])
    {
        return stream_context_get_default($options);
    }
}