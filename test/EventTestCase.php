<?php
namespace ffan\php\event;

use Psr\EventManager\EventInterface;

require_once '../vendor/autoload.php';

class testCallback
{
    public static function a(EventInterface $eve)
    {
        echo 'I am callback a event:' . $eve->getName() . PHP_EOL;
    }

    public static function b(EventInterface $eve)
    {
        echo 'I am callback b event:' . $eve->getName() . PHP_EOL;
    }

    public function c(EventInterface $eve)
    {
        echo 'I am callback c event:' . $eve->getName() . PHP_EOL;
    }
}

$eve_manager = new EventManager();
var_dump($eve_manager->attach('test', '\ffan\php\event\testCallback::a'));
//这一条应该加不进去
var_dump($eve_manager->attach('test', '\ffan\php\event\testCallback::a'));
//这一条应该优化执行
var_dump($eve_manager->attach('test', '\ffan\php\event\testCallback::b', 10));
$test = new testCallback();
var_dump($eve_manager->attach('test2', array($test, 'c')));
$eve_manager->trigger('test');
$eve_manager->trigger('test2');
