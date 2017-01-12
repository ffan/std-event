<?php
namespace ffan\php\event;

use Psr\EventManager\EventInterface;
use Psr\EventManager\EventManagerInterface;

/**
 * Class EventManager
 * @package ffan\php\event
 */
class EventManager implements EventManagerInterface
{

    /**
     * @var array 事件列表
     */
    private $event_list = [];

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
                for ($i = 0; $i < $len; ++$i) {
                    $tmp_priority = $current_list[$i++];
                    $tmp_callback = $current_list[$i];
                    if ($tmp_priority < $priority) {
                        $new_list[] = $priority;
                        $new_list[] = $callback;
                    }
                    $new_list[] = $tmp_priority;
                    $new_list[] = $tmp_callback;
                }
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
        for ($i = 1; $i < $len; $i += 2) {
            if ($callback === $tmp_list[$i]) {
                unset($tmp_list[$i - 1], $tmp_list[$i]);
                return true;
            }
        }
        return false;
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
     * @param  string|EventInterface $event 可以直接传递一个event事件过来
     * @param  object|string $target 事件源
     * @param  array|object $argv 事件参数
     * @return mixed
     */
    public function trigger($event, $target = null, $argv = array())
    {
        if (is_string($event) && strlen($event) > 0) {
            $eve_name = $event;
            $event = new Event();
            $event->setName($eve_name);
            $event->setParams($argv);
            if (null !== $target) {
                $event->setTarget($target);
            }
        } else {
            $eve_name = $event->getName();
        }
        if (!is_object($event) || !($event instanceof EventInterface)) {
            trigger_error('event is not instanceof EventInterface', E_USER_WARNING);
            return;
        }
        if (!isset($this->event_list[$eve_name])) {
            return;
        }
        $tmp_list = $this->event_list[$eve_name];
        $len = count($tmp_list);
        for ($i = 1; $i < $len; $i += 2) {
            call_user_func($tmp_list[$i], $event);
        }
    }
}
