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

class Member extends AbstractModel
{
    public function getCodeRow($code_id, $web_id)
    {
        $builder = $this->getBuilder('Member')
            ->columns('id, username')
            ->from('my_member')
            ->orderBy('id');

        $paginator = new \Phalcon\Paginator\Adapter\QueryBuilder(array(
            "builder" => $builder,
            "limit" => 20,
            "page" => 1
        ));
    }

    public function getCodeList()
    {
        $builder = $this->getBuilder('Member')
            ->columns('id, username')->inWhere('id', array(1, 2, 3))
            ->orderBy('id');

        /*
        $paginator = new \Phalcon\Paginator\Adapter\QueryBuilder(array(
            "builder" => $builder,
            "limit"=> 5,
            "page" => 2
        ));
        */
//        $page = $paginator->getPaginate();
//        pr($page);
//        pr($builder->getQuery()->execute());exit;
        $robots = $builder->getQuery()->execute();

//
        $builder1 = new \Engine\CurdBuilder();

        // Request a transaction
        $transaction = $this->getDI()->getTransactions()->get();
        $this->setTransaction($transaction);
//        $status = $this->sqlBuilder('Member')->insert(['userid', 'username'], [3333, 'shies'])->getQuery()->execute();
//        pr($status->getModel()->id);

        $status = $this->sqlBuilder('Member')->update(['userid', 'username'], [355, 'rocky'])->where('id=3')->getQuery()->execute();

        pr($status->success());
//        $status = $this->sqlBuilder('Member')->delete()->where('id=6')->getQuery()->execute();
        pr($status->success());
        $transaction->commit();

        return $robots;
    }


    public function iniAclTest()
    {
        $acl = new AcList();

        // Default action is deny access
        $acl->setDefaultAction(\Phalcon\Acl::DENY);
    }


    public function addRoleToAcl()
    {
        $acl = new AcList();

        // Create some roles
        $roleAdmins = new Role('Adminstrators', 'Super-User role');
        $roleGuests = new Role('Guests');

        // Add "Guests" role to acl
        $acl->addRole($roleGuests);

        // Add "Designers" role to acl without a Phalcon\Acl\Role
        $acl->addRole("Designers");
    }


    public function addResource()
    {
        $acl = new AcList();

        // Define the "Customers" resource
        $customersResource = new Resource("Customers");
        # pr(get_class_methods($customersResource));

        // Add "customers" resource with a couple of operations
        $acl->addResource($customersResource, 'search');
        $acl->addReource($customersResource, array('create', 'update'));
    }


    public function setAccessControl()
    {
        $acl = new AcList();

        // Set access level for roles into resource
        $acl->allow('Guests', 'Customers', 'search');
        $acl->allow('Guests', 'Customers', 'create');
        $acl->deny('Guests', 'Customers', 'update');
    }


    public function isAllowAccess()
    {
        $acl = new AcList();

        // Check whether role has access to the operations
        $status1 = $acl->isAllowed('Guests', 'Customers', 'edit');
        $status2 = $acl->isAllowed('Guests', 'Customers', 'search');
        $status3 = $acl->isAllowed('Guests', 'Customers', 'search');
    }


    public function isVerifyAcl()
    {
        // Check whether act data already exists
        if (!is_file('app/security/acl.data'))
        {
            $acl = new AcList();
            // ... Define roles, resources, access, etc

            // Store serialized list into plain file
            file_put_contents("app/security/acl.data", serialize($acl));
        }
        else
        {
            // Restore acl object from serialized file
            $acl = unserialize(file_get_contents("app/security/acl.data"));
        }

        // Use acl list as needed
        if ($acl->isAllowed("Guests", "Customers", "edit"))
        {
            exit("Access granted!");
        }

        exit("Access denied :(");
    }


    public function getRoleInstance()
    {
        $acl = new AcList();

        // Create some roles
        $roleAdmins = new Role("Adminstrators", "Super-User role");
        $roleGuests = new Role("Guests");

        // Add "Guests" role to acl
        $acl->addRole($roleGuests);

        // Add "Adminstrators" role inheriting from "Guests" its accesses
        $acl->addRole($roleAdmins, $roleGuests);
    }


    public function setAclEvents()
    {
        // Create an event manager
        $eventManager = new EventsManager();

        // Attach a listener for type "acl"
        $eventManager->attach("acl", function($event, $acl)
        {
            if ($event->getType() == 'beforeCheckAccess')
            {
                echo $acl->getActiveRole(),
                $acl->getActiveResource(),
                $acl->getActiveAccess();
            }
        });

        $acl = new AcList();
        // Setup the $acl
        // ...

        // Bind the eventsManager to the acl component
        $acl->setEventsManager($eventManager);
    }

}
