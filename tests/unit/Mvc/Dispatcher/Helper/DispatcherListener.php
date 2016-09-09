<?php

namespace Phalcon\Test\Unit\Mvc\Dispatcher\Helper;

use Exception;
use Phalcon\DispatcherInterface;
use Phalcon\Events\Event;

/**
 * \Phalcon\Test\Unit\Mvc\Dispatcher\Helper\DispatcherListener
 * Early listener for all dispatch events.
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Andres Gutierrez <andres@phalconphp.com>
 * @author    Nikolaos Dimopoulos <nikos@phalconphp.com>
 * @package   Phalcon\Test\Unit\Http\Helper
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class DispatcherListener
{
    /**
     * @var array
     */
    protected $_trace = [];

    public function clearTrace()
    {
        $this->_trace = [];
    }

    public function trace($text)
    {
        $this->_trace[] = $text;
    }

    public function getTrace()
    {
        return $this->_trace;
    }

    public function compare(array $eventTraces)
    {
        return $this->_trace === $eventTraces;
    }

    public function beforeDispatchLoop(Event $event, DispatcherInterface $dispatcher)
    {
        $this->trace('beforeDispatchLoop');
    }

    public function beforeDispatch(Event $event, DispatcherInterface $dispatcher)
    {
        $this->trace('beforeDispatch');
    }

    public function beforeExecuteRoute(Event $event, DispatcherInterface $dispatcher)
    {
        $this->trace('beforeExecuteRoute');
    }

    public function afterInitialize(Event $event, DispatcherInterface $dispatcher)
    {
        $this->trace('afterInitialize');
    }

    public function afterExecuteRoute(Event $event, DispatcherInterface $dispatcher)
    {
        $this->trace('afterExecuteRoute');
    }

    public function afterDispatch(Event $event, DispatcherInterface $dispatcher)
    {
        $this->trace('afterDispatch');
    }

    public function afterDispatchLoop(Event $event, DispatcherInterface $dispatcher)
    {
        $this->trace('afterDispatchLoop');
    }

    public function beforeNotFoundAction(Event $event, DispatcherInterface $dispatcher)
    {
        $this->trace('beforeNotFoundAction');
    }

    public function beforeException(Event $event, DispatcherInterface $dispatcher, Exception $exception)
    {
        $this->trace('beforeException: ' . $exception->getMessage());
    }
}