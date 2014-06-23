<?php
class Sqlite_Instance
{
  public $dbh;
  public $stmt;

  protected $_location;

  public function  __destruct()
  {
    $this->disconnect();
  }

  public function connect()
  {
    try
    {
      $this->dbh = new PDO("sqlite:" . $this->_location);
    }
    catch (PDOException $e)
    {
      throw new Exception ("Database Instance error: " . $e->getMessage());
    }
  }

  public function set($name, $value)
  {
    $item = '_' . $name;
    $this->$item = $value;
  }

  public function disconnect()
  {
    $this->dbh = null;
  }

  public function execute($sql, $data = array())
  {
    $this->connect();
    $this->stmt = $this->dbh->prepare($sql);
    $response = $this->stmt->execute($data);
    return $response;
  }

  public function fetch($sql, $data = array(), $container = null)
  {
    $this->connect();
    $container = $this->_validateRowObject($container);
    $this->stmt = $this->dbh->prepare($sql);
    $this->stmt->execute($data);
    $this->stmt->setFetchMode(PDO::FETCH_INTO, $container);
    $data = array();
    while ($row = $this->stmt->fetch())
    {
      $object = new $container;
      $object = clone $row;
      $data[] = $object;
    }
    return $data;
  }

  public function fetchOne($sql, $data = array(), $container = null)
  {
    $this->connect();
    $data = $this->fetch($sql, $data, $container);
    if (count($data) > 1)
    {
      throw new Exception("Expected a single object as part of the Mysql results, but received multiple.");
    }
    elseif (count($data)==1)
    {
      $data = $data[0];
    }
    return $data;
  }

  public function __call($name, $value)
  {
    throw new Exception("While setting '".$name."' in '".__CLASS__."' the attribute could not be found.");
  }
  
  protected function _validateRowObject($container)
  {
    if ($container !== null && is_object($container) == true)
    {
      if (!$container instanceof Sqlite_Object)
      {
        throw new Exception("Object provided to act as the data container is not an instance of Mysql_Object.");
      }
    }
    else
    {
      $container = new Sqlite_Object();
    }
    return $container;
  }
}