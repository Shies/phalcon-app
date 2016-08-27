<?php
namespace Phalcon\Mvc\Model\Query;

use Phalcon\DI;
use Phalcon\Mvc\Model\Query\Builder as QueryBuilder;
use Phalcon\Mvc\Model\Query as ModelQuery;

/*
     # INSERT INTO Cars (price, type) VALUES (:price:, :type:)
     $result = $this->modelsManager->createBuilder()->from('Member')
     ->insert(array('price', 'type'), array(15000.00, 'Sedan'))
     ->getQuery()
     ->execute();

     # UDPATE Cars SET price = :price, type = :type: WHERE id = :id:
     $result = $this->modelsManager->createBuilder()->from('')
     ->update(array('price', 'type'), array(15000.00, 'Sedan'))
     ->where('id = :id:', array('id' => 100))
     ->getQuery()
     ->execute();

      # DELETE FROM Cars WHERE id = :id:
     $result = $this->modelsManager->createBuilder()
     ->delete()
     ->where('id = :id:', array('id' => 100))
     ->getQuery()
     ->execute();
     */

class sqlBuilder extends QueryBuilder
{
    protected $_Phql = NULL;
    protected $_type = NULL;


    /**
     * @param array $keys
     * @param array $values
     * @return $this
     * @throws \Exception
     * @internal param bool $ignore
     */
    public function insert(array $keys = array(), array $values = array())
    {
        if (empty($keys) ||
            empty($values)
        ) {
            throw new \Exception('The frist params or second params must is array');
        }

        if (count($keys) !== count($values)) {
            throw new \Exception('The keys neq values params number');
        }

        $args = func_get_args();
        if (func_num_args() < 2) {
            throw new \Exception('Sorry, please input your params');
        }

        $ignore = false;
        $fields = array_shift($args);
        $values = array_slice($args, 0);

        $format_keys = NULL;
        foreach ($fields AS $val)
            $format_keys .= '' . $val . ',';

        $valueList = [];
        foreach ($values AS $val) {
            if (empty($val) ||
                !is_array($val) ||
                count($val) !== count($keys)
            ) {
                continue;
            }
            $str = '(\'' . implode('\',\'', array_values($val)) . '\')';
            $valueList[] = $str;
            unset($str);
        }

        if (!$ignore) {
            $sql = "INSERT INTO [%s] (%s) VALUES " . join(',', $valueList);

        } else {
            $sql = "INSERT IGNORE INTO [%s] (%s) VALUES " . join(',', $valueList);
        }

        $this->_Phql = sprintf($sql, $this->_models, rtrim($format_keys, ','));
        $this->_type = 306;
        unset($ignore, $fields, $values, $format_keys, $valueList);

        return $this;
    }


    /**
     * @param array $keys
     * @param array $values
     * @return $this
     * @throws \Exception
     */
    public function update(array $keys = array(), array $values = array())
    {
        if (empty($keys) ||
            empty($values)
        ) {
            throw new \Exception('The frist params or second params must is array');
        }

        if (count($keys) !== count($values)) {
            throw new \Exception('The keys neq values params number');
        }

        $setVal = NULL;
        foreach ($keys AS $key => $val)
            $setVal = "$val =  '$values[$key]',";

        $sql = "UPDATE [%s] SET %s ";

        $this->_Phql = sprintf($sql, $this->_models, rtrim($setVal, ','));
        $this->_type = 300;

        return $this;
    }


    /**
     * @return $this
     */
    public function delete()
    {
        // set query type method for executeDelete
        $this->_type = 303;
        $this->_Phql = "DELETE FROM [%s] ";
        $this->_Phql = sprintf($this->_Phql, $this->_models);

        return $this;
    }


    /**
     * @return ModelQuery
     */
    public function getQuery()
    {
        $where = $this->getWhere();
        if (!empty($where)) {
            $this->_Phql .= (' WHERE ' . $where);
        }

        $limit = $this->getLimit();
        if (!empty($limit)) {
            $this->_Phql .= (' LIMIT ' . $limit);
        }

        $query = new ModelQuery($this->_Phql);
        $query->setType($this->_type);
        $query->setDI(Di::getDefault());

        return $query;
    }

}

