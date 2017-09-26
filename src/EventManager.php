<?php

namespace FFan\Std\Event;

/**
 * Class EventManager
 * @package FFan\Std\Event
 */
class EventManager
{
    /**
     * shutdown 事件
     */
    const SHUTDOWN_EVENT = 'ffan-shutdown';

    /**
     * @var EventManager 单例
     */
    private static $single_instance;

    /**
     * @var array 事件列表
     */
    private $event_list = [];

    /**
     * EventManager constructor.
     */
    public function __construct()
    {
        register_shutdown_function(array($this, 'shutdownEvent'));
    }

    /**
     * 设置一个监听事件
     *
     * @param string $event 事件
     * @param callable $callback 回调 函数
     * @param int $priority 优先级
     * @return bool true on success false on failure
     */
    public function attach($event, callable $callback, $priority = 0)
    {
        //数据结构[$priority, $callback, $priority, $callback, $priority, $callback]
        //如果没有事件
        if (!isset($this->event_list[$event])) {
            $this->event_list[$event] = array($priority, $callback);
        } //如果已经有事件了，就要按照优化级排序存放
        else {
            $current_list = &$this->event_list[$event];
            //求出当前事件长度
            $len = count($current_list);
            //先遍历一次，该callback是否已经存在
            for ($i = 1; $i < $len; $i += 2) {
                if ($current_list[$i] === $callback) {
                    return false;
                }
            }
            //找到最后一项的优先级
            $last_priority = $current_list[$len - 2];
            //如果新加入的优先级不高于最后一个事件的，直接附加在最后面
            if ($priority <= $last_priority) {
                $current_list[] = $priority;
                $current_list[] = $callback;
            } //如果该事件的优先级比第一个的还高，排在最前面
            else if ($priority > $current_list[0]) {
                array_unshift($current_list, $priority, $callback);
            } //最坏的情况，老老实实的排序
            else {
                $new_list = [];
                $is_add = false;
                for ($i = 0; $i < $len; ++$i) {
                    $tmp_priority = $current_list[$i++];
                    $tmp_callback = $current_list[$i];
                    if (!$is_add && $tmp_priority < $priority) {
                        $is_add = true;
                        $new_list[] = $priority;
                        $new_list[] = $callback;
                    }
                    $new_list[] = $tmp_priority;
                    $new_list[] = $tmp_callback;
                }
                $this->event_list[$event] = $new_list;
            }
        }
        return true;
    }

    /**
     * 移除一个事件
     *
     * @param string $event 事件
     * @param callable $callback 事件
     * @return bool true:成功 false： 失败
     */
    public function detach($event, callable $callback)
    {
        if (!isset($this->event_list[$event])) {
            return false;
        }
        $tmp_list = &$this->event_list[$event];
        $len = count($tmp_list);
        //只有偶数项 才是callback, 奇数项是 priority
        $is_find = false;
        for ($i = 1; $i < $len; $i += 2) {
            //如果已经找到了，向前移两位
            if ($is_find || $callback === $tmp_list[$i]) {
                $is_find = true;
                if ($i + 2 < $len) {
                    $tmp_list[$i - 1] = $tmp_list[$i + 1];
                    $tmp_list[$i] = $tmp_list[$i + 2];
                }
            }
        }
        if ($is_find) {
            unset($tmp_list[$len - 2], $tmp_list[$len - 1]);
        }
        return $is_find;
    }

    /**
     * 清除一个事件
     *
     * @param  string $event
     * @return void
     */
    public function clearListeners($event)
    {
        unset($this->event_list[$event]);
    }

    /**
     * 触发一次事件
     *
     * @param  string $event_name 事件名称
     * @param  object|string $target 事件源
     * @param  mixed $argv 事件参数
     */
    public function trigger($event_name, $target = null, $argv = null)
    {
        if (empty($event_name) || !is_string($event_name)) {
            throw new \InvalidArgumentException('Invalid event_name');
        }
        $event = new Event();
        $event->setName($event_name);
        if (null !== $argv) {
            $event->setParams($argv);
        }
        if (null !== $target) {
            $event->setTarget($target);
        }
        if (!isset($this->event_list[$event_name])) {
            return;
        }
        $tmp_list = $this->event_list[$event_name];
        $len = count($tmp_list);
        for ($i = 1; $i < $len; $i += 2) {
            call_user_func($tmp_list[$i], $event);
        }
    }

    /**
     * shutdown 事件
     */
    public function shutdownEvent()
    {
        $this->trigger(self::SHUTDOWN_EVENT);
    }

    /**
     * 实例
     * @return EventManager
     */
    public static function instance()
    {
        if (!self::$single_instance) {
            self::$single_instance = new self();
        }
        return self::$single_instance;
    }
}
