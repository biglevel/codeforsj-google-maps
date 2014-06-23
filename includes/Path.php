<?php
class Path
{
  public $module;
  public $controller;
  public $action;
  public $uri;
  public $module_controller;

  protected $_modules;
  protected $_controllers;
  protected $_available_modules = array();
  protected $_available_controllers = array();
  
  protected $_default_module;
  protected $_default_controller;
  protected $_default_action;
  
  protected static $_env;
  protected static $_instance;

  protected function __construct()
  {
    self::$_env = Environment::instance();
    $this->_default_module     = isset(self::$_env->settings->home->module) ? self::$_env->settings->home->module : 'main';
    $this->_default_controller = isset(self::$_env->settings->home->controller) ? self::$_env->settings->home->controller : 'default';
    $this->_default_action     = isset(self::$_env->settings->home->action) ? self::$_env->settings->home->action : 'index';
  }

  public static function instance()
  {
    if ( !self::$_instance )
    {
      self::$_instance = new self;
    }
    return self::$_instance;
  }

  public function setModules($path)
  {
    $this->_requireModules($path);
    $this->_modules = $path;
  }

  public function parse($uri)
  {
    $this->_requireModules();
    $this->uri = ltrim($uri,'/');
    if ($this->_parseUriElements($this->uri) == false)
    {
      throw new Exception("The URI being requested is malformed.  Check that you're loading the correct address.");
    }
  }

  protected function _requireModulePath($directory)
  {
    if (file_exists($this->_modules. PATH_SEPARATOR . $directory))
    {
      return $this->_modules. PATH_SEPARATOR . $directory;
    }
    return false;
  }

  protected function _parseUriElements($uri)
  {
    $this->_buildAvailableModules();
    $uri = rtrim($uri,'/');
    $matches = explode('/',$uri);
    $combinations = array();
    if (count($matches) == 3)
    {
      $combinations[] = array(
        'module'      => $matches[0],
        'controller'  => $matches[1],
        'action'      => $matches[2]
      );
    }
    elseif(count($matches) == 2)
    {
      $combinations[] = array(
        'module'      => $matches[0],
        'controller'  => $matches[1],
        'action'      => $this->_default_action
      );
      $combinations[] = array(
        'module'      => $matches[0],
        'controller'  => $this->_default_controller,
        'action'      => $matches[1]
      );
      $combinations[] = array(
        'module'      => $this->_default_module,
        'controller'  => $matches[0],
        'action'      => $matches[1]
      );
    }
    elseif(count($matches) == 1)
    {
      if (!empty($matches[0]))
      {
        $combinations[] = array(
          'module'      => $matches[0],
          'controller'  => $this->_default_controller,
          'action'      => $this->_default_action
        );
        $combinations[] = array(
          'module'      => $this->_default_module,
          'controller'  => $matches[0],
          'action'      => $this->_default_action
        );
        $combinations[] = array(
          'module'      => $this->_default_module,
          'controller'  => $this->_default_controller,
          'action'      => $matches[0]
        );
      }

      else
      {
        $combinations[] = array(
          'module'      => $this->_default_module,
          'controller'  => $this->_default_controller,
          'action'      => $this->_default_action
        );
      }
    }
    else
    {
      /*
      $combinations[] = array(
        'module'      => $this->_default_module,
        'controller'  => $this->_default_controller,
        'action'      => $this->_default_action
      );
       */
    }
    $follow = false;
    foreach ($combinations as $row)
    {
      extract($row);
      if ($this->_detectRequestPath($module, $controller, $action) == true)
      {
        $follow = true;
        return true;
      }
    }
    return false;
  }

  protected function _checkMaskedLocations($uri)
  {
    return false;
  }

  protected function _detectRequestPath($module, $controller, $action)
  {
    try
    {
      $this->_moduleExists($module, true);
      $this->_controllerExists($controller);
      $this->_actionExists($action);
      return true;
    }
    catch(Exception $e)
    {
      return false;
    }
  }

  protected function _moduleExists($module, $force = false)
  {
    // make sure module is part of available modules
    if (in_array($module, $this->_available_modules) === false)
    {
      throw new Exception("The module specified in request could not be found.");
    }
    // set module
    $this->module = $module;
    // build up list of available controllers
    $this->_buildAvailableControllers($force);
  }

  protected function _controllerExists($controller)
  {
    // make sure controller is in the list of available controllers
    if (in_array($controller, $this->_available_controllers) === false)
    {
      throw new Exception("The module controller specified in request could not be found.");
    }
    $this->controller = $controller;
    $this->module_controller = ucfirst($this->module).'_Controller_'.ucfirst($this->controller);
    if (class_exists($this->module_controller) === false)
    {
      throw new Exception("The object or controller do not exist within the module that you have specified. (".$this->module_controller.")");
    }
  }

  protected function _actionExists($action)
  {
    if (method_exists($this->module_controller, $action) === false)
    {
      throw new Exception("The action '" .$action. "' does not exist");
    }
    $this->action = $action;
  }

  protected function _buildAvailableControllers($force = false)
  {
    if (count($this->_available_controllers) == 0 || $force !== false)
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
    }
    if (count($this->_available_controllers) == 0)
    {
      throw new Exception ("While trying to build a list of module controllers, there were none that could be found.  No controllers exist in the module '".$this->module."' path");
    }
  }

  protected function _buildAvailableModules()
  {
    if (count($this->_available_modules) == 0)
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
    }
    if (count($this->_available_modules) == 0)
    {
      throw new Exception ("While trying to build a list of modules, there were none that could be found.  This is either because the module path is incorrect or the module directory is empty.");
    }
  }

  protected function _requireModules($path = false)
  {
    if ($path !== false)
    {
      if (!file_exists($path))
      {
        throw new Exception("The path to your modules '".$path."' does not exist");
      }
    }
    if ($this->_modules === false)
    {
      throw new Exception("There was no module directory set.  Your module directory contain controllers, modules, and views");
    }
  }
}
