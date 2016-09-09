<?php

namespace Phalcon\Test\Unit\Mvc;

use Exception;
use Phalcon\Test\Unit\Mvc\Dispatcher\Helper\BaseDispatcher;

/**
 * \Phalcon\Test\Unit\Mvc\Dispatcher\DispatcherAfterExecuteRouteTest
 * Tests the \Phalcon\Dispatcher and Phalcon\Mvc\Dispatcher "afterExecuteRoute" event.
 *
 * @see https://docs.phalconphp.com/en/latest/reference/dispatching.html
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Andres Gutierrez <andres@phalconphp.com>
 * @author    Nikolaos Dimopoulos <nikos@phalconphp.com>
 * @package   Phalcon\Test\Unit
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class DispatcherAfterExecuteRouteTest extends BaseDispatcher
{
    /**
     * Tests the forwarding in the afterExecuteRoute event
     *
     * @author Mark Johnson <mark@techpivot.net>
     * @since  2016-09-01
     */
    public function testAfterExecuteRouteForwardOnce()
    {
        $this->specify(
            'Forwarding inside the afterExecuteRoute should cancel the default route and forward immediately',
            function () {
                $forwarded = false;
                $dispatcher = $this->getDispatcher();

                $dispatcher->getEventsManager()->attach('dispatch:afterExecuteRoute', function($event, $dispatcher) use (&$forwarded) {
                    if ($forwarded === false) {
                        $dispatcher->forward(['action' => 'index2']);
                        $forwarded = true;
                    }
                });

                $dispatcher->dispatch();

                expect($this->getDispatcherListener()->getTrace())->equals([
                    'beforeDispatchLoop',
                    'beforeDispatch',
                    'beforeExecuteRoute',
                    'beforeExecuteRoute-method',
                    'initialize-method',
                    'afterInitialize',
                    'indexAction',
                    'afterExecuteRoute',
                    'beforeDispatch',
                    'beforeExecuteRoute',
                    'beforeExecuteRoute-method',
                    'index2Action',
                    'afterExecuteRoute',
                    'afterExecuteRoute-method',
                    'afterDispatch',
                    'afterDispatchLoop'
                ]);
            }
        );
    }

    /**
     * Tests returning <tt>false</tt> inside a afterExecuteRoute event.
     *
     * @author Mark Johnson <mark@techpivot.net>
     * @since  2016-09-01
     */
    public function testAfterExecuteRouteReturnFalse()
    {
        $this->specify(
            'Returning false inside a "dispatch:afterExecuteRoute" event should immediately cancel dispatching',
            function () {
                $dispatcher = $this->getDispatcher();
                $dispatcherListener = $this->getDispatcherListener();

                $dispatcher->getEventsManager()->attach('dispatch:afterExecuteRoute', function() use ($dispatcherListener) {
                    $dispatcherListener->trace('afterExecuteRoute: custom return false');
                    return false;
                });
                $dispatcher->dispatch();

                expect($this->getDispatcherListener()->getTrace())->equals([
                    'beforeDispatchLoop',
                    'beforeDispatch',
                    'beforeExecuteRoute',
                    'beforeExecuteRoute-method',
                    'initialize-method',
                    'afterInitialize',
                    'indexAction',
                    'afterExecuteRoute',
                    'afterExecuteRoute: custom return false',
                    'afterDispatchLoop',
                ]);
            }
        );
    }

    /**
     * Tests exception handling to ensure exceptions can be properly handled when thrown from
     * inside a afterExecuteRoute event and then ensure the exception is not bubbled when
     * returning with <tt>false</tt>.
     *
     * @author Mark Johnson <mark@techpivot.net>
     * @since  2016-09-01
     */
    public function testAfterExecuteRouteWithBeforeExceptionReturningFalse()
    {
        $this->specify(
            'Returning false inside a "dispatch:beforeException" event should cancel dispatching and prevent bubbling of the exception',
            function () {
                $dispatcher = $this->getDispatcher();

                $dispatcher->getEventsManager()->attach('dispatch:afterExecuteRoute', function() {
                    throw new Exception('afterExecuteRoute exception occurred');
                });
                $dispatcher->getEventsManager()->attach('dispatch:beforeException', function() {
                    return false;
                });

                $dispatcher->dispatch();

                expect($this->getDispatcherListener()->getTrace())->equals([
                    'beforeDispatchLoop',
                    'beforeDispatch',
                    'beforeExecuteRoute',
                    'beforeExecuteRoute-method',
                    'initialize-method',
                    'afterInitialize',
                    'indexAction',
                    'afterExecuteRoute',
                    'beforeException: afterExecuteRoute exception occurred',
                    'afterDispatchLoop'
                ]);
            }
        );
    }

    /**
     * Tests exception handling to ensure exceptions can be properly handled via beforeException event and
     * then will properly bubble up the stack if anything other than <tt>false</tt> is returned.
     *
     * @author Mark Johnson <mark@techpivot.net>
     * @since  2016-09-01
     */
    public function testAfterExecuteRouteWithBeforeExceptionBubble()
    {
        $this->specify(
            'Returning anything other than false inside a "dispatch:beforeException" event should bubble the exception',
            function () {
                $dispatcher = $this->getDispatcher();
                $dispatcherListener = $this->getDispatcherListener();

                $dispatcher->getEventsManager()->attach('dispatch:afterExecuteRoute', function() {
                    throw new Exception('afterExecuteRoute exception occurred');
                });
                $dispatcher->getEventsManager()->attach('dispatch:beforeException', function() use ($dispatcherListener) {
                    $dispatcherListener->trace('beforeException: custom before exception bubble');
                    return null;
                });

                $caughtException = false;
                try {
                    $dispatcher->dispatch();
                } catch (Exception $exception) {
                    $caughtException = true;
                }

                expect($caughtException)->equals(true);
                expect($this->getDispatcherListener()->getTrace())->equals([
                    'beforeDispatchLoop',
                    'beforeDispatch',
                    'beforeExecuteRoute',
                    'beforeExecuteRoute-method',
                    'initialize-method',
                    'afterInitialize',
                    'indexAction',
                    'afterExecuteRoute',
                    'beforeException: afterExecuteRoute exception occurred',
                    'beforeException: custom before exception bubble'
                ]);
            }
        );
    }

    /**
     * Tests dispatch forward handling inside the beforeException when a afterExecuteRoute exception occurs.
     *
     * @author Mark Johnson <mark@techpivot.net>
     * @since  2016-09-01
     */
    public function testAfterExecuteRouteWithBeforeExceptionForwardOnce()
    {
        $this->specify(
            'Forwarding inside a "dispatch:beforeException" event (and without returning false) should properly forward the dispatcher without the exception bubbling',
            function () {
                $forwarded = false;
                $dispatcher = $this->getDispatcher();
                $dispatcherListener = $this->getDispatcherListener();

                $dispatcher->getEventsManager()->attach('dispatch:afterExecuteRoute', function() use (&$forwarded) {
                    if ($forwarded === false) {
                        $forwarded = true;
                        throw new Exception('afterExecuteRoute exception occurred');
                    }
                });
                $dispatcher->getEventsManager()->attach('dispatch:beforeException', function($event, $dispatcher) use ($dispatcherListener) {
                    $dispatcherListener->trace('beforeException: custom before exception forward');
                    $dispatcher->forward(['action' => 'index2']);
                });

                $dispatcher->dispatch();

                expect($this->getDispatcherListener()->getTrace())->equals([
                    'beforeDispatchLoop',
                    'beforeDispatch',
                    'beforeExecuteRoute',
                    'beforeExecuteRoute-method',
                    'initialize-method',
                    'afterInitialize',
                    'indexAction',
                    'afterExecuteRoute',
                    'beforeException: afterExecuteRoute exception occurred',
                    'beforeException: custom before exception forward',
                    'beforeDispatch',
                    'beforeExecuteRoute',
                    'beforeExecuteRoute-method',
                    'index2Action',
                    'afterExecuteRoute',
                    'afterExecuteRoute-method',
                    'afterDispatch',
                    'afterDispatchLoop'
                ]);
            }
        );
    }
}
