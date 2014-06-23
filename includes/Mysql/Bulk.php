<?php

class Mysql_Bulk
{

    CONST SAVE_RECORDS_AT = 500;

    protected $_sql;
    protected $_data = array();

    public function __construct($base_sql)
    {
        $this->_sql = $base_sql;
    }

    public function __destruct()
    {
        $this->save();
    }

    public function add($data)
    {
        array_push($this->_data, "(" . implode(", ", $data) . ")");
        if (count($this->_data) == self::SAVE_RECORDS_AT)
        {
            $this->save();
        }
    }

    public function save()
    {
        if (count($this->_data) > 0)
        {
            $sql = sprintf($this->_sql, implode(",", $this->_data));
            Mysql::instance()->execute($sql);
        }
        $this->_data = array();
    }

}