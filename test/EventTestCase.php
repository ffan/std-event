<?php
use UiStd\Event\Event;
use UiStd\Event\EventManager;

require_once '../vendor/autoload.php';

class testCallback
{
    /**
     * @param Event $eve
     */
    public static function a($eve)
    {
        echo 'I am callback a event:' . $eve->getName() . PHP_EOL;
    }

    /**
     * @param Event $eve
     */
    public static function b($eve)
    {
        echo 'I am callback b event:' . $eve->getName() . PHP_EOL;
    }

    /**
     * @param Event $eve
     */
    public function c($eve)
    {
        echo 'I am callback c event:' . $eve->getName() . PHP_EOL;
    }
}

$eve_manager = new EventManager();
var_dump($eve_manager->attach('test', 'testCallback::a'));
//这一条应该加不进去
var_dump($eve_manager->attach('test', 'testCallback::a'));
//这一条应该优化执行
var_dump($eve_manager->attach('test', 'testCallback::b', 10));
$test = new testCallback();
var_dump($eve_manager->attach('test2', array($test, 'c')));
$eve_manager->trigger('test');
$eve_manager->trigger('test2');
