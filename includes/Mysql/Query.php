<?php
class Mysql_Query
{
  protected $_columns;
  protected $_from;
  protected $_from_values       = array();
  protected $_joins             = array();
  protected $_join_values       = array();
  protected $_where             = array();
  protected $_where_values      = array();
  protected $_group             = array();
  protected $_having            = array();
  protected $_having_values     = array();
  protected $_order             = array();
  protected $_limit             = 0;
  protected $_offset            = 0;

  public static $filters          = array();
  public static $default_filter   = false;
  public static $sortable         = array();
  public static $default_sort     = false;

  public function __construct()
  {
    return $this;
  }

  public function select($columns)
  {
    $this->_columns.= $columns;
    return $this;
  }

  public function from($table, $data = false)
  {
    $this->_from = $table;
    if ($data !== false)
    {
      $this->_from_values = array_merge($this->_from_values, (!is_array($data)) ? array($data) : $data);
    }
    return $this;
  }

  public function join($type, $table, $condition, $data = false)
  {
    $this->_joins[$table] = sprintf("%s join %s %s",
        $type,
        $table,
        $condition
        );
    if ($data !== false)
    {
      $this->_join_values = array_merge($this->_join_values, (!is_array($data)) ? array($data) : $data);
    }
    return $this;
  }

  public function innerJoin($table, $condition, $data = false)
  {
    $this->join('inner', $table, $condition, $data);
    return $this;
  }

  public function leftJoin($table, $condition, $data = false)
  {
    $this->join('left', $table, $condition, $data);
    return $this;
  }

  public function rightJoin($table, $condition, $data = false)
  {
    $this->join('right', $table, $condition, $data);
    return $this;
  }

  public function where($evaluation, $value = false)
  {
    $this->_where[] = $evaluation;
    if ($value !== false)
    {
      $this->_where_values = array_merge($this->_where_values, (!is_array($value)) ? array($value) : $value);
    }
    return $this;
  }

  public function whereAnd($evaluation, $value = false)
  {
    $this->where($evalutation, $value);
    return $this;
  }

  public function whereBetween($column, $start, $end)
  {
    $this->where($column . ' between ? and ?', array($start, $end));
    return $this;
  }

  public function between($column, $start, $end)
  {
    $this->whereBetween($column, $start, $end);
    return $this;
  }

  public function whereIn($column, $values = array())
  {
    if (is_array($values) && count($values)>0)
    {
      $this->where($column . ' IN (' . rtrim(str_repeat('?,',count($values)),',') . ')', $values);
    }
    elseif (!is_array($values) && !empty($values))
    {
      $this->where($column . ' = ? ', $values);
    }
    return $this;
  }

  public function whereNotIn($column, $values = array())
  {
    if (is_array($values) && count($values)>0)
    {
      $this->where($column . ' NOT IN (' . rtrim(str_repeat('?,',count($values)),',') . ')', $values);
    }
    elseif (!is_array($values) && !empty($values))
    {
      $this->where($column . ' <> ? ', $values);
    }
    return $this;
  }

  public function in($column, $values = array())
  {
    $this->whereIn($column, $values);
    return $this;
  }

  public function notIn($column, $values = array())
  {
    $this->whereNotIn($column, $values);
    return $this;
  }

  public function group($column)
  {
    $columns = (is_array($column) && count($colulmn) > 0) ? $column : explode(",", $column);
    $this->_group = array_merge($this->_group, $columns);
    return $this;
  }

  public function having($evaluation, $value = false)
  {
    $this->_having[] = $evaluation;
    if ($value !== false)
    {
      $this->_having_values = array_merge($this->_having_values, (!is_array($value)) ? array($value) : $value);
    }
    return $this;
  }

  public function order($column)
  {
    if (!empty($column))
    {
      $this->_order[] = $column;
    }
    return $this;
  }

  public function orderBy($column)
  {
    $this->order($column);
    return $this;
  }

  public function limit($count)
  {
    $this->_limit = $count;
    return $this;
  }

  public function offset($start)
  {
    $this->_offset = $start;
    return $this;
  }

  public function getSql()
  {
    return sprintf("select %s\nfrom %s %s %s %s %s %s %s %s
      ",
        $this->_columns,
        $this->_from,
        (count($this->_joins)>0) ? "\n" . implode("\n", $this->_joins) : '',
        (count($this->_where)>0) ? "\nwhere " . implode("\n and ", $this->_where) : '',
        (count($this->_group)>0) ? "\ngroup by " . implode(", ", $this->_group) : '',
        (count($this->_having)>0) ? "\nhaving " . implode("\n and ", $this->_having) : '',
        (count($this->_order)>0) ? "\norder by " . implode(", ", $this->_order) : '',
        (is_numeric($this->_limit) && $this->_limit > 0) ? "\nlimit " . $this->_limit : '',
        (is_numeric($this->_offset) && $this->_offset > 0) ? "\noffset " . $this->_offset : ''
    );
  }

  public function getValues()
  {
    return array_merge($this->_join_values, $this->_where_values, $this->_having_values);
  }

  public function fetch($container = null)
  {
    $sql = $this->getSql();
    $values = array_merge($this->_from_values, $this->_join_values, $this->_where_values, $this->_having_values);
    return Mysql::instance()->fetch($sql, $values, $container);
  }
  
  public function fetchOne($container = null)
  {
    $sql = $this->getSql();
    $values = array_merge($this->_from_values, $this->_join_values, $this->_where_values, $this->_having_values);
    return Mysql::instance()->fetchOne($sql, $values, $container);
  }
  
  public function getSqlAndValues()
  {
      return array(
        'sql' => $this->getSql(),
        'values' => array_merge($this->_from_values, $this->_join_values, $this->_where_values, $this->_having_values)
      );
  }

  public function clear()
  {
    $this->_columns           = '';
    $this->_from              = '';
    $this->_from_values       = array();
    $this->_joins             = array();
    $this->_join_values       = array();
    $this->_where             = array();
    $this->_where_values      = array();
    $this->_group             = array();
    $this->_having            = array();
    $this->_having_values     = array();
    $this->_order             = array();
    $this->_limit             = 0;
    $this->_offset            = 0;
    gc_collect_cycles();
  }
}