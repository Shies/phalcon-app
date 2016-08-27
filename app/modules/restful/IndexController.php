<?php
namespace Rest;

use \Phalcon\Mvc\View as PhView;
use \Niden\Models\User as NidenUser;

class UserController extends \Rest\RrstController
{
    /**
     * Initializes the controller
     */
    public function initialize()
    {
        parent::initialize();
    }

    /**
     * The index action
     */
    public function indexAction()
    {
        // You can return anything you want here, perhaps the schema
    }

    public function getByUserIdAction($id)
    {
        $data = NidenUser::fetchById($id);

        $results = $this->getResultset(
            $data,
            'user',
            '\\Niden\\Models\\User'
        );

        $this->initResponse();
        $this->setPayload($results);

        return $this->render();
    }
}