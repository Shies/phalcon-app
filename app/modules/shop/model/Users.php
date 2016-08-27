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

class Users extends AbstractModel
{

    public function initialize()
    {
        parent::initialize();
    }


    public function getCountByName($name)
    {
        $name = strval($name);

        $builder = $this->getBuilder('Users')->columns('COUNT(id) AS total')->where("name='$name'")->limit(1);
		$result = $builder->getQuery()->execute()->getFirst();
		
		if (isset($result->total))
			return $result->total;
		else 
			return 0;
    }

	
	public function getUserCount($where = '')
	{
		if (empty($where))
		{
			$where = '1=1';
		}
		
		$builder = $this->getBuilder('Users')->columns('COUNT(*) AS total')->where($where)->limit(1);
		$result = $builder->getQuery()->execute()->getFirst();
		
		if (isset($result->total))
			return $result->total;
		else 
			return 0;
	}
	
	
    public function getUserByName($name)
    {
        $name = strval($name);

        $builder = $this->getBuilder('Users')->columns('id, name, pass')->where("name='$name'")->limit(1);
        $result = $builder->getQuery()->execute()->current();

        if (empty($result))
        {
            return false;
        }

        return $result;
    }


    public function getUserInfo($name, $pass)
    {
        $name = strval($name);
        $pass = md5(md5(str_replace(' ', '', $pass)));

        $builder = $this->getBuilder('Users')->columns('id, name, pass')->where("name='$name' AND pass='$pass'")->limit(1);
		$result = $builder->getQuery()->execute()->current();
		
        if (empty($result))
        {
            return false;
        }

        return $result;
    }


    public function getUserById($id)
    {
        $id = intval($id);

        $builder = $this->getBuilder('Users')->columns('id, name, pass')->where("id='$id'")->limit(1);
        $result = $builder->getQuery()->execute()->current();

        if (empty($result))
        {
            return false;
        }

        return $result;
    }
	
    public function getUserList(array $param)
    {
		$where = $this->getWhere([
					'page'  => $param['page'], 
					'total' => $this->getUserCount(), 
					'limit' => 1
				]);
		
		
        $builder = $this->getBuilder('Users')
				->columns('id, name')
				->where('1=1')
				->orderBy('id')
				->limit($where['limit'], $where['offset']);
		$result = $builder->getQuery()->execute()->toArray();
		
		
        $paginator = new \Phalcon\Paginator\Adapter\QueryBuilder([
					'builder' => $builder,
					'limit' => $where['limit'],
					'page'  => $where['page']
				]);
		$pager = $paginator->getPaginate();
		
		
        return array(
			'where'  => $where,
			'pager'  => $pager,
			'result' => $result
		);
    }


    public function saveUser(array $user, $mode = 'INSERT', $where = '')
    {
        if (empty($user))
        {
            return false;
        }

        $keys = array_keys($user);
        $values = array_values($user);

        // Request a transaction
        $transaction = $this->getDI()->getTransactions()->get();
        $this->setTransaction($transaction);
        if ('INSERT' == $mode)
        {
            $status = $this->sqlBuilder('Users')->insert($keys, $values)->getQuery()->execute();
        }
        else
        {
            $status = $this->sqlBuilder('Users')->update($keys, $values)->where($where)->getQuery()->execute();
        }
        $transaction->commit();

        if ($res = $status->success())
        {
            if (isset($status->getModel()->id))
                return $status->getModel()->id;
        }

        return $res;
    }


    public function removeUser($id = 0)
    {
        if (empty($id) == 1)
        {
            return false;
        }

        $status = $this->sqlBuilder('Users')->delete()->where('id='.$id)->getQuery()->execute();
        if ($status->success())
            return true;
        else
            return false;
    }

}
