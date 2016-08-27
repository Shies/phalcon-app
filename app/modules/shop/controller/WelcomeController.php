<?php
/**
 * 默认展示页面
 */
namespace Shop\Controllers;

use Phalcon\DI;
use Shop\Models\Action;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Acl;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;
use Phalcon\Acl\Adapter\Memory as AcList;
use Phalcon\Events\Manager as EventsManager;

class WelcomeController extends AbstractController
{

    public function initialize()
    {
        parent::initialize();
    }


    public function indexAction()
    {
        # $this->persistent->parameters = NULL;
		$user = session_get('user');
        if (!$user)
        {
            return $this->dispatcher->forward(array(
                'controller' => 'users',
                'action' => 'index'
            ));
        }
		
		$this->view->setVar("user", $user);
        $this->view->pick('welcome/index');
    }


    public function menuAction()
    {
        # require(BASE_URL . '/data/privilege.php');
        $action = new Action;
        $modules = $action->getActionList();
        ksort($modules);

        $action_list = isset($_SESSION['user']['action_list']) ? $_SESSION['user']['action_list'] : '';
        if ($action_list != 'all')
        {
            $action_list = explode(',', $action_list);
        }

        $show = $_LANG = array();
        if (is_array($action_list))
        {
            foreach ($action_list AS $key => $val)
            {
                if (array_key_exists($val, $modules))
                {
                    $show[$key]['url'] = $modules[$val];
                    $show[$key]['name'] = $_LANG[$val];
                }
            }
        }
        else
        {
            foreach ($modules AS $key => $val)
            {
                $show[$key]['url'] = $val;
                $show[$key]['name'] = $_LANG[$key];
            }
        }
        pr($show);
    }


    public function topAction()
    {
        # TODO;
    }


    public function systemAction()
    {
        # TODO;
    }



}
