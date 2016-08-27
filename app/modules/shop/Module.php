<?php
namespace Shop;

use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\View;

class Module
{
    protected $_moduleName = "Shop";

    public function registerServices($di)
    {
        $di->set('dispatcher', function () {
            $dispatcher = new Dispatcher();
            $dispatcher->setDefaultNamespace($this->_moduleName . "\Controllers");
            return $dispatcher;
        });
    }
}