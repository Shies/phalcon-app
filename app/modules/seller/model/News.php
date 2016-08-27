<?php

namespace Seller\Models;

use Engine\AbstractModel;
use Phalcon\DI;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class News extends AbstractModel
{

    public function initialize()
    {
        parent::initialize();
    }


    public function getArticleInfo($id)
    {
        $article_id = intval($id);

        $builder = $this->getBuilder('News')->columns('id, title, thumb')->where('id='.$article_id)->orderBy('id')->limit(1);
        $result = $builder->getQuery()->execute()->current();

        if (empty($result))
        {
            return false;
        }

        $result->title = stripslashes($result->title);
        $result->thumb = is_file($result->thumb) ? $result->thumb : '';

        return $result;
    }

}