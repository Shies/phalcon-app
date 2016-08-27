<?php
namespace Engine\Plugin;

use Engine\Application as EngineApplication;
use Engine\Exception as EngineException;
use Phalcon\Dispatcher;
use Phalcon\Events\Event;
use Phalcon\Exception as PhalconException;
use Phalcon\Mvc\Dispatcher\Exception as DispatchException;
use Phalcon\Mvc\User\Plugin as PhalconPlugin;

/**
 * Not found plugin.
 *
 * @package   Engine\Plugin
 */
class DispatchErrorHandler extends PhalconPlugin
{
    /**
     * Before exception is happening.
     *
     * @param Event            $event      Event object.
     * @param Dispatcher       $dispatcher Dispatcher object.
     * @param PhalconException $exception  Exception object.
     *
     * @throws \Phalcon\Exception
     * @return bool
     */
    public function beforeException($event, $dispatcher, $exception)
    {
        $dispatcher->setParam('exception', $exception);

        // 错误信息
        $message = get_class($exception) . ': ' .  strip_path($exception->getMessage()) .
            ' (in ' . strip_path($exception->getFile()) . ' on line ' . $exception->getLine() . ')' .
            "\n" . strip_path($exception->getTraceAsString()) . "\n";

        // Write log to files
        write_log('errors', $message, \Phalcon\Logger::ERROR, true);

        $dispatcher->forward(array(
            'controller' => 'error',
            'action' => 'index'
        ));

        return false;
    }
}