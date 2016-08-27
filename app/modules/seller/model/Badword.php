<?php

namespace Seller\Models;

use Engine\AbstractModel;
use Phalcon\DI;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class Badword extends AbstractModel
{

    public function initialize()
    {
        parent::initialize();
    }


    public function getBadWord(array $param = array(), $condi = '')
    {
        if (empty($condi))
        {
            $condi = "1=1";
        }

        $limit = isset($param['limit']) ? intval($param['limit']) : 10;
        $offset = isset($param['offset']) ? intval($param['offset']) : 0;

        $builder = $this->getBuilder('Badword')->columns('*')->where($condi)->orderBy('badid')->limit($limit, $offset);
        $result = $builder->getQuery()->execute()->toArray();

        if (empty($result))
        {
            return false;
        }

        return $result;
    }
}