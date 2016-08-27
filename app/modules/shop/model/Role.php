<?php
/**
 * 页面模块
 */
namespace Shop\Models;

use Engine\AbstractModel;
use Phalcon\DI;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Acl;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;
use Phalcon\Acl\Adapter\Memory as AcList;
use Phalcon\Events\Manager as EventsManager;

class Action extends AbstractModel
{

    public function initialize()
    {
        parent::initialize();
    }


    public function getRoleList()
    {
        $result = [];

        $builder = $this->getBuilder('Role')->columns('*')->where(1)->orderBy('id');
        $result = $builder->getQuery()->execute()->current();
        if (empty($result))
        {
            return [];
        }

        return $result;
    }


    public function getRoleInfo($id = 0)
    {
        static $roles = [];
        if (!empty($roles[$id]))
        {
            return $roles[$id];
        }

        $builder = $this->getBuilder('Role')->columns('*')->where('id='.$id)->limit(1);
        $roles[$id] = $builder->getQuery()->execute();
        if (empty($roles[$id]))
        {
            return false;
        }

        return $roles[$id];
    }


    public function getActionList()
    {
        static $action = [];
        if (!empty($action))
        {
            return $action;
        }

        $builder = $this->getBuilder('Action')->columns('*')->orderBy('id');
        $action = $builder->getQuery()->execute()->current();
		pr($action);
		exit;

        return $action;
    }


    public function getMenuList()
    {
        # TODO;
    }

}
