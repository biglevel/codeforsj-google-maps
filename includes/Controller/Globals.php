<?php
class Controller_Globals
{
  protected $_namespace;
  protected $_container;
  protected $_type;
  
  protected static $_instances = array();
  
  public static function instance($type)
  {
    if (!isset(self::$_instances[$type]))
    {
      self::$_instances[$type] = new self($type);
    }
    return self::$_instances[$type];
  }
  
  public function __construct($type = false, $namespace = false)
  {
    $this->_type = strtoupper($type);
    
    if ( !in_array($this->_type, array("GET","SESSION","POST","SERVER","FILES") ) )
    {
      throw new Exception("GLOBAL type not accepted: " . $type );
    }
    
    switch ($this->_type)
    {
      case 'GET':     $this->_container = $_GET;      break;
      case 'POST':    $this->_container = $_POST;     break;
      case 'SERVER':  $this->_container = $_SERVER;   break;
      case 'FILES':   $this->_container = $_FILES;    break;
      case 'SESSION': $this->_container = isset($_SESSION) ? $_SESSION : array();  break;
      /*
        $this->_namespace = (!empty($namespace)) ? $namespace : 'default';
        if (!isset($_SESSION[$namespace]))
        {
          $_SESSION[$namespace] = array();
        }
        $this->_container = $_SESSION[$namespace];
      break;
       */
    }
  }

  public function set($name, $value)
  {
    if ($this->_namespace)
    {
      $this->_container[$this->_namespace][$name] = $value;
    }
    else
    {
      $this->_container[$name] = $value;
    }
    switch ($this->_type)
    {
      case 'SESSION': $_SESSION = array_merge($_SESSION, $this->_container); break;
    }
    return true;
  }

  public function destroy($name)
  {
    if ($this->_namespace)
    {
      $this->_container[$this->_namespace][$name] = $value;
    }
    else
    {
      $this->_container[$name] = $value;
    }
    switch ($this->_type)
    {
      case 'SESSION':
        if (isset($this->_container[$name]))
        {
          unset($this->_container[$name]);
        }
        if (isset($_SESSION[$name]))
        {
          unset($_SESSION[$name]);
        }
        break;
    }
    return true;
  }

  public function value($name)
  {
    if ($this->_namespace)
      $container = $this->_container[$this->_namespace];
    else
      $container = $this->_container;

    if (isset($container[$name]))
    {
      //if (!empty($container[$name]))
      return $container[$name];
    }
    return false;
  }

  public function values($namespace = false)
  {
    return $this->_container;
  }

  public function exists($key)
  {
    if (isset($this->_container[$key]))
      return true;
    else
      return false;
  }

  public function fetch()
  {
    return $this->_container;
  }

  public function keys()
  {
    if (is_array($this->_container))
    {
      if (count($this->_container)>0)
        return array_keys($this->_container);
    }
    return false;
  }
}