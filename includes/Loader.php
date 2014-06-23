<?php
class Loader
{
  public $source;
  
  protected $_namespaces = array();
  protected static $_instance;

  protected function __construct()
  {
    spl_autoload_register(array(__CLASS__, 'autoload'));
  }

  public static function instance()
  {
    if ( self::$_instance === null )
    {
      $object = new self;
      $object->source = SOURCE;
      self::$_instance = $object;
    }
    return self::$_instance;
  }

  public static function autoload($class)
  {
    $self = self::$_instance;
    $file = $self->parseClassName($class);

    if ($self->_checkReserved($class) === false)
    {
      foreach ($self->getNamespaces() as $namespace => $path)
      {
        if ($namespace == substr($class, 0, strlen($class)))
        {
          if (file_exists($path . '/' . $file))
          {
            include_once($path . '/' . $file);
            return true;
          }
        }
      }
      foreach (explode(':',get_include_path()) as $path)
      {
        if (file_exists($path . '/' . $file))
        {
          include_once( $path . '/' . $file );
          return true;
        }
      }
      throw new Exception ("Could not find class '" . $class ."'.  Namespace for class may not be setup correctly.");
    }
  }

  public function register($namespace, $path = false)
  {
    if (isset($this->_namespaces[$namespace]))
    {
      throw new Exception("Namespace " . $namespace . " is already defined in Loader.  Unique names are needed for Autoloader.");
    }
    $this->_namespaces[$namespace] = $path;
  }

  public function getNamespaces()
  {
    return $this->_namespaces;
  }

  public function parseClassName($className)
  {
    return str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
  }

  protected function _checkReserved($class)
  {
    $types = array(
      'Controller'  => $this->source . '/modules/%s/controllers',
      'Model'       => $this->source . '/modules/%s/models',
      'Form'        => $this->source . '/modules/%s/forms'
    );
    foreach ($types as $seed => $path)
    {
      preg_match('/(.*)_' . $seed . '_(.*)/', $class, $matches);
      if (count($matches) == 3)
      {
        list($pattern, $module, $file) = $matches;
        $path = sprintf($path, strtolower($module));
        if ($this->_checkModuleDir($module, $path))
        {
          $file = $path.'/' . ucfirst($this->parseClassName($file));
          if (file_exists($file))
          {
            require_once($file);
            return true;
          }
        }
        break;
      }
    }
    return false;
  }

  protected function _checkModuleDir($module, $working_dir)
  {
    $working_dir = strtolower($working_dir);
    if (file_exists($working_dir) && is_dir($working_dir))
    {
      return true;
    }
    throw new Exception("Module directory '" . $module . "' could not be found in framework path");
  }

  /*
  protected function _buildAvailableModules()
  {
    if (is_dir($this->_modules))
    {
      $d = dir($this->_modules);
      while (false !== ($entry = $d->read()))
      {
        if (substr($entry,0,1)!='.')
        {
          $this->_available_modules[] = $entry;
        }
      }
      $d->close();
    }
    if (count($this->_available_modules) == 0)
    {
      throw new Exception ("While trying to build a list of modules, there were none that could be found.  This is either because the module path is incorrect or the module directory is empty.");
    }
  }

  protected function _buildAvailableControllers()
  {
    $path = $this->_modules . '/' . $this->module .'/controllers';
    if (is_dir($path))
    {
      $this->_controllers = $path;
      $d = dir($path);
      while (false !== ($entry = $d->read()))
      {
        if (substr($entry,0,1)!='.')
        {
          $this->_available_controllers[$entry] = strtolower(rtrim($entry,".php"));
        }
      }
      $d->close();
    }
    if (count($this->_available_controllers) == 0)
    {
      throw new Exception ("While trying to build a list of module controllers, there were none that could be found.  No controllers exist in the module '".$this->module."' path");
    }
  }
   * 
   */
}
