<?php

namespace Seller;

use Phalcon\Mvc\Dispatcher;

class Module
{
    protected $_moduleName = "Seller";

    public function registerServices ($di)
    {
        $di->set ('dispatcher', function () {
            $dispatcher = new Dispatcher();
            $dispatcher->setDefaultNamespace ($this->_moduleName . "\Controllers");
            return $dispatcher;
        });
    }

}