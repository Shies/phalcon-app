<?php
namespace Engine;

use Phalcon\DI;
use Phalcon\Mvc\Model as PhalconModel;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * Abstract Model.
 *
 * @method static findFirstById($id)
 * @method static findFirstByLanguage($name)
 *
 * @method DIBehaviour|\Phalcon\DI getDI()
 */
abstract class AbstractModel extends PhalconModel
{

    public static $_tbprefix = "";

    public function initialize()
    {
        self::$_tbprefix = config('config')['dbMaster']['prefix'];
        $this->setReadConnectionService('dbSlave');//读
        $this->setWriteConnectionService('dbMaster');//写
    }
	

    public function getSource()
    {
		$parts = explode("\\", get_class($this));
        return self::$_tbprefix . strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', end($parts)));
    }

    /**
     * Get table name.
     *
     * @return string
     */
    public static function getTableName()
    {
        $reader = DI::getDefault()->get('annotations');
        $reflector = $reader->get(get_called_class());
        $annotations = $reflector->getClassAnnotations();

        return $annotations->get('Source')->getArgument(0);
    }

    /**
     * Get builder associated with table of this model.
     *
     * @param $table
     * @param string|null $tableAlias Table alias to use in query.
     * @return Builder
     */
    public function getBuilder($table, $tableAlias = null)
    {
		$parts = explode("\\", get_class($this));
		
        $table = $table != end($parts) ? $table : get_called_class();
        $builder = $this->getModelsManager()->createBuilder();
        if (!$tableAlias) {
            $builder->from($table);
        } else {
            $builder->addFrom($table, $tableAlias);
        }

        return $builder;
    }

    /**
     * load curdbuilder set write operation
     */
    public function sqlBuilder($table, $tableAlias = null)
    {
		$parts = explode("\\", get_class($this));
		
        $table = $table != end($parts) ? $table : get_called_class();
        $builder = new \Engine\CurdBuilder();
        if (!$tableAlias) {
            $builder->from($table);
        } else {
            $builder->addFrom($table, $tableAlias);
        }

        return $builder;
    }


    /**
     * get some pagination important params
     */
    public function getWhere(array $where)
    {
		if (!isset($where['page']) || 
			!isset($where['total'])|| 
			!isset($where['limit']))
		{
			throw new \Exception('Sorry, required parameter missing');
		}
		
        extract($where, EXTR_SKIP);
        if ($page <= 0)
        {
            $page = 1;
        }

        $pagecount = ceil($total / $limit);
        if ($pagecount != 0 AND $page >= $pagecount)
        {
            $page = $pagecount;
        }

        if (!isset($offset))
        {
            $offset = ($page - 1) * $limit;
        }

        return ['page' => $page, 'limit' => $limit, 'offset' => $offset, 'pagecount' => $pagecount];
    }

	
	/**
     * get some pagination important params (alias)
     */
	public function _getParams(array $where)
	{
		return $this->getWhere($where);
	}


    /* *
     *    类似TenCent-QQ空间留言板分页算法去计算(2013)
     *    @param $total    总统计
     *    @param $page     当前页
     *    @param $pagesize 每页显示多少数据
     *    @param $uri      你的url
     *    @author  ___Shies
     *
     *    @return  (String) page_and_start
     */
    function pager($total, $page, $pagesize, $uri) {
        // total count
        $total    = $total ? intval($total) : 0;
        // == 0 return
        if ($total <= 0) return false;

        // pagesize;
        $pagesize = max(1, intval($pagesize));
        // url and ajax
        $uri      .= strpos($uri, '?') !== false ? '&' : '?';

        // pager
        $page_and_start = '';
        // page_count
        $page_count = $total > 0 ? @ceil($total / $pagesize) : 0;
        // min page and max page
        $page 		= max(1, min($page_count, intval($page)));

        // offset
        $offset     = $page > $page_count ?
            ($page_count - 1) * $pagesize :
            ($page - 1) * $pagesize;

        // prev page
        if ($page - 1 > 0) {
            $page_and_start .= " <a href=" . $uri . "page=" . ($page - 1) . ">上一页</a> ";
        } else {
            $page_and_start .= " <a href='javascript:;'>上一页</a> ";
        }

        // frist page
        if ($page >= 9) {
            $page_and_start .= " <a href=" . $uri . "page=1>1</a> " . "...";
        }

        if ($page > 0 && $page < 9) {
            $from_start = 1;
            $to_end 	= ($page_count < 9) ? $page_count : 9;
        }
        elseif ($page >= 9 && $page_count - 8 >= $page) {
            $from_start = ($page - 3) < 1 ? 1 : ($page - 3);
            $to_end 	= ($page + 3) > $page_count ? $page_count : ($page + 3);
        }
        else {
            $from_start = $page_count - 8;
            $to_end 	= $page_count;
        }

        // show center page
        for ($i = $from_start; $i <= $to_end; $i ++) {
            if ($i == $page) {
                $page_and_start .= " <a href=" . $uri . "page=$i><strong>$i</strong></a> ";
            } else {
                $page_and_start .= " <a href=" . $uri . "page=$i>$i</a> ";
            }
        }

        // last page
        if ($page_count - 8 >= $page) {
            $page_and_start .= "..." . " <a href=" . $uri . "page=" . $page_count . ">" . $page_count . "</a> ";
        }

        // next page
        if ($page + 1 <= $page_count) {
            $page_and_start .= " <a href=" . $uri . "page=" . ($page + 1) . ">下一页</a> ";
        } else {
            $page_and_start .= " <a href='javascript:;'>下一页</a> ";
        }

        // pager and offset
        return array(
            'offset'     => $offset,
            'pager'      => $page_and_start,
        );
    }


    /**
     *    类似TenCent-QQ空间留言板分页算法去计算(2013)
     *    @param $total    总统计
     *    @param $page     当前页
     *    @param $pagesize 每页显示多少数据
     *    @param $uri      你的url
     *    @author  ___Shies
     *
     *    @return  (String) page_and_start
     */
    function asyncPager($total, $page, $pagesize, $uri) {
        // total count
        $total    = $total ? intval($total) : 0;
        // == 0 return
        if ($total <= 0) return false;

        // pagesize;
        $pagesize = max(1, intval($pagesize));
        // url and ajax
        $uri      .= strpos($uri, '?') !== false ? '&' : '?';

        // pager
        $page_and_start = '';
        // page_count
        $page_count = $total > 0 ? @ceil($total / $pagesize) : 0;
        // min page and max page
        $page 		= max(1, min($page_count, intval($page)));

        // offset
        $offset     = $page > $page_count ?
            ($page_count - 1) * $pagesize :
            ($page - 1) * $pagesize;

        // prev page
        if ($page - 1 > 0) {
            $page_and_start .= " <a href='JavaScript:asyncSearch(".($page - 1).")'>上一页</a> ";
        } else {
            $page_and_start .= " <a href='JavaScript:;'>上一页</a> ";
        }

        // frist page
        if ($page >= 9) {
            $page_and_start .= " <a href='JavaScript:asyncSearch(1)'>1</a> " . "...";
        }

        if ($page > 0 && $page < 9) {
            $from_start = 1;
            $to_end 	= ($page_count < 9) ? $page_count : 9;
        }
        elseif ($page >= 9 && $page_count - 8 >= $page) {
            $from_start = ($page - 3) < 1 ? 1 : ($page - 3);
            $to_end 	= ($page + 3) > $page_count ? $page_count : ($page + 3);
        }
        else {
            $from_start = $page_count - 8;
            $to_end 	= $page_count;
        }

        // show center page
        for ($i = $from_start; $i <= $to_end; $i ++) {
            if ($i == $page) {
                $page_and_start .= " <a href='JavaScript:asyncSearch(".$i.")'><strong>$i</strong></a> ";
            } else {
                $page_and_start .= " <a href='JavaScript:asyncSearch(".$i.")'>$i</a> ";
            }
        }

        // last page
        if ($page_count - 8 >= $page) {
            $page_and_start .= "..." . " <a href='JavaScript:asyncSearch(".$page_count.")'>" . $page_count . "</a> ";
        }

        // next page
        if ($page + 1 <= $page_count) {
            $page_and_start .= " <a href='JavaScript:asyncSearch(".($page + 1).")'>下一页</a> ";
        } else {
            $page_and_start .= " <a href='JavaScript:;'>下一页</a> ";
        }

        // pager and offset
        return array(
            'offset'     => $offset,
            'pager'      => $page_and_start,
        );
    }

}
