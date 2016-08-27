<?php
/**
 * 默认展示页面
 */
namespace Shop\Controllers;

use Phalcon\DI;
use Shop\Models\Users;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Acl;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;
use Phalcon\Acl\Adapter\Memory as AcList;
use Phalcon\Events\Manager as EventsManager;

class UsersController extends AbstractController
{

    public function initialize()
    {
        parent::initialize();
    }


    public function indexAction()
    {
		# $cache = $this->getDI()->get('cacheData');
		# $cache->flush();
        if (session_get('user'))
        {
            return $this->dispatcher->forward(array(
                'controller' => 'welcome',
                'action' => 'index'
            ));
        }
		$this->view->pick('users/login');
    }


    public function loginAction()
    {
        if (!$this->request->isPost())
        {
            return $this->dispatcher->forward(
                array(
                    'controller' => 'users',
                    'action' => 'index'
                )
            );
        }

        $name = $this->request->getPost('name');
        $pass = $this->request->getPost('pass');
        if (empty($name))
        {
            $this->flash->error('the username cannot empty');
			exit;
        }

        if (empty($pass) ||
            strlen($pass) < 4 ||
            strpos($pass, ' ') !== false)
        {
            $this->flash->error('please input valid password');
			exit;
        }
		
        $user = new Users();
        if (!$user->getUserByName($name))
        {
            $this->flash->error('the account not exists in DB');
			exit;
        }

        $userinfo = $user->getUserInfo($name, $pass);
        if (!$userinfo)
        {
            $this->flash->error('Sorry, Your password is wrong');
            exit;
        }
        session_set('user', $userinfo);

        return $this->dispatcher->forward(
                    array(
                        'controller' => 'welcome',
                        'action' => 'index'
                    )
                );
    }


    public function create_password($len = 8)
    {
        $randpwd = strval(NULL);
        for ($i = 0; $i < $len; $i += 1)
        {
            $randpwd .= chr(mt_rand(33, 126));
        }

        return $randpwd;
    }


    public function forgetPassAction()
    {
        # TODO;
    }


    public function searchAction()
    {
        $numberPage = 1;
		if ($this->request->isPost())
		{
			// maybe is async request
			$query = Criteria::formInput($this->di, "Users", $_POST);
			$this->persistent->parameters = $query->getParams();
		}
		else
		{
			// direct request
			$numberPage = (int) $this->request->getQuery('page', 'int');
        }
		
		$parameters = $this->persistent->parameters;
		if (!is_array($parameters))
		{
			$parameters = array();
		}
		$parameters['page'] = $numberPage;
		
		$user = new Users;
		$list = $user->getUserList($parameters);
		
		$this->view->setVar("user",   session_get('user'));
		$this->view->setVar("result", $list['result']);
		$this->view->setVar("where",  $list['where']);
		$this->view->setVar('pager',  $list['pager']);
		
		$this->view->pick('users/lists');
    }


    public function newAction()
    {
        # TODO;
    }


    public function editAction($id)
    {
        if (!$this->request->isPost())
        {
            $user = new Users;
			if (!$userinfo = $user->getUserById($id))
			{
				$this->flash->error('not found');
			}

            return $this->dispatcher->forward(
                array(
                    'controller' => 'users',
                    'action' => 'index'
                )
            );
		}
        // redirect('users/index');
    }


    public function createAction()
    {
        if (!$this->request->isPost())
        {
            return $this->dispatcher->forward(
                array(
                    'controller' => 'users',
                    'action' => 'index'
                )
            );
        }
        // redirect('users/index');
    }


    public function saveAction()
    {
        if (!$this->request->isPost())
        {
            return $this->dispatcher->forward(
                array(
                    'controller' => 'users',
                    'action' => 'index'
                )
            );
        }

        $user = new Users;
        $id = $this->request->getPost('id', 'int');
        $userinfo = $user->getUserById($id);
        if (!$userinfo)
        {
            $this->flash->error('user does not exists');
            return $this->dispatcher->forward(
                array(
                    'controller' => 'users',
                    'action' => 'index'
                )
            );
        }

        $form = new Form;
        $data = $this->request->getPost();
        if (!$form->isValid($data, $userinfo))
        {
            foreach ($form->getMessages() as $message)
            {
                $this->flash->error($message);
            }

            return $this->dispatcher->forward(
                array(
                    'controller' => 'users',
                    'action' => 'new'
                )
            );
        }

        if ($form->save() == false)
        {
            foreach ($form->getMessages() as $message)
            {
                $this->flash->error($message);
            }

            return $this->dispatcher->forward(
                array(
                    'controller' => 'users',
                    'action' => 'new'
                )
            );
        }

        $form->clear();
        $this->flash->success("users was updated successfully");
        return $this->dispatcher->forward(
            array(
                'controller' => 'users',
                'action' => 'index'
            )
        );
    }


    public function deleteAction()
    {
        # TODO;
    }


    public function allotAction()
    {
        # TODO;
    }

}
