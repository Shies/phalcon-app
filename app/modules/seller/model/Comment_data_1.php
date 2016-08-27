<?php

namespace Seller\Models;

use Engine\AbstractModel;
use Phalcon\DI;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class Comment_data_1 extends AbstractModel
{

    public function initialize()
    {
        parent::initialize();
    }


    public function getCommentCount($where = '')
    {
        if (empty($where))
        {
            $where = '1=1';
        }

        $builder = $this->getBuilder('Comment_data_1')->columns('COUNT(*) AS total')->where($where)->limit(1);
        $result = $builder->getQuery()->getSingleResult();
		
        if (isset($result->total))
            return $result->total;
        else 
            return 0;
    }


    public function getCommentList(array $param, $async = false)
    {
		$condi = (array) $param['where'];
		if (!isset($condi['commentid']))
		{
			$condi = 'commentid=\'content_6-11-1\'';
		}
		else
		{
			$condi = '1=1';
		}


        $page = isset($param['page']) ? $param['page'] : 1;
        $limit = isset($limit) ? $limit : 10;


        $total = $this->getCommentCount($condi);
        if ($async)
        {
            $where = $this->asyncPager($total, $page, $limit, strval(null));
        }
        else
        {
            $where = $this->pager($total, $page, $limit, strval(null));
        }


        $offset = isset($where['offset']) ? $where['offset'] : 0;
        $pager = isset($where['pager']) ? $where['pager'] : null;


        // must remove, else appear notice. ready?
        // $where['offset']
        $builder = $this->getBuilder('Comment_data_1')
                ->columns('username, creat_at, ip, status, content')
                ->where($condi)
                ->orderBy('id')
                ->limit($limit, $offset);
        $result = $builder->getQuery()->execute()->toArray();

		
        return array(
            'where'  => $where,
            'pager'  => $pager,
            'result' => $result
        );
    }


    public function saveComment(array $comment, $mode = 'INSERT', $where = '')
    {
        if (empty($comment))
        {
            return false;
        }

        $keys = array_keys($comment);
        $values = array_values($comment);

        // Request a transaction
        $transaction = $this->getDI()->getTransactions()->get();		
        $this->setTransaction($transaction);
        if ($mode == 'INSERT')
        {
            $status = $this->sqlBuilder('Comment_data_1')->insert($keys, $values)->getQuery()->execute();
        }
        else
        {
            $status = $this->sqlBuilder('Comment_data_1')->update($keys, $values)->where($where)->getQuery()->execute();
        }
        // $transaction->commit();

        if ($res = $status->success())
        {
            if (isset($status->getModel()->id))
                return $status->getModel()->id;
        }

		return $res;
    }

}
