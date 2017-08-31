<?php

namespace FFan\Std\Event;

/**
 * Class Event
 * @package FFan\Std\Event
 */
class Event
{
    /**
     * @var string 事件名称
     */
    private $event_name;

    /**
     * @var null|string|object
     */
    private $event_target;

    /**
     * @var mixed 参数列表
     */
    private $event_params;

    /**
     * @var bool 是否已经停止冒泡
     */
    private $is_propagation = false;

    /**
     * Get event name
     *
     * @return string
     */
    public function getName()
    {
        return $this->event_name;
    }

    /**
     * Get target/context from which event was triggered
     *
     * @return null|string|object
     */
    public function getTarget()
    {
        return $this->event_target;
    }

    /**
     * 获取通过事件传递的参数列表
     *
     * @return array
     */
    public function getParams()
    {
        return $this->event_params;
    }

    /**
     * 按名称获取参数
     *
     * @param string $name
     * @return mixed
     */
    public function getParam($name)
    {
        return isset($this->event_params[$name]) ? $this->event_params[$name] : null;
    }

    /**
     * 设置event的名称
     *
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->event_name = $name;
    }

    /**
     * 设置事件源
     *
     * @param null|string|object $target
     * @return void
     */
    public function setTarget($target)
    {
        $this->event_target = $target;
    }

    /**
     * 设置参数列表
     *
     * @param mixed $params
     * @return void
     */
    public function setParams($params)
    {
        $this->event_params = $params;
    }

    /**
     * 是否已经停止冒泡了
     *
     * @param bool $flag
     */
    public function stopPropagation($flag)
    {
        $this->is_propagation = (bool)$flag;
    }

    /**
     * 是否已经停止冒泡了
     *
     * @return bool
     */
    public function isPropagationStopped()
    {
        return $this->is_propagation;
    }
}
