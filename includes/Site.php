<?php
class Site
{
  public $controller;

  protected $_layout_path;
  protected $_module_path;
  protected $_settings;
  protected $_path;
  protected $_get = array();
  protected $_controller_object;

  public function __construct($layout_path, $module_path)
  {
    $this->_layout_path = $layout_path;
    $this->_module_path = $module_path;
    $this->_settings = Environment::instance();
  }

  public function route($uri = false)
  {
    $uri = ($uri === false) ? strtok($_SERVER['REQUEST_URI'],'?') : $uri;
    $this->_path = Path::instance();
    $this->_path->setModules($this->_module_path);
    if ($uri == '/' && isset($_GET['q']))
    {
      $uri = $_GET['q'];
    }
    if ($this->_checkPath($uri) == false)
    {
      $decrypted = Path_Mask::decrypt(ltrim($uri,'/'));
      if (substr($decrypted,0,1)=='?')
      {
        $decrypted = '/' . $decrypted;
      }
      $uri = strtok($decrypted, '?');
      $this->_parseGetParams(strtok('?'));
      if ($uri == '/' && isset($this->_get['q']))
      {
        $uri = $this->_get['q'];
      }
      $this->_checkPath($uri);
    }
  }

  public function execute()
  {
    try
    {
      // instantiate controller object
      $this->_controller_object = new $this->_path->module_controller;

      // make sure object is a member of the controller
      if (!($this->_controller_object instanceof Controller))
      {
        throw new Exception('The object that is being requested is not a member of the Controller class');
      }

      // collect globals
      $this->_controller_object->collectGlobals();

      // pass along common variables to be called within controller
      $this->_controller_object->controller = ucfirst($this->_path->controller);
      $this->_controller_object->module     = ucfirst($this->_path->module);
      $this->_controller_object->action     = $this->_path->action;

      // forward paths to controller object for layout to know where shiz is at
      $this->_controller_object->setLayoutPath($this->_layout_path);
      $this->_controller_object->setModulePath($this->_module_path);

      // find out if there are additional get variables that need to be passed a long
      $this->_processDecryptedGetVars();

      // process action
      $this->_controller_object->run();

      // render layout
      $this->_controller_object->render();
    }
    catch(Exception $e)
    {
      throw new Exception($e->getMessage());
    }
  }

  protected function _parseGetParams($vars)
  {
    if (!is_array($vars) && !empty($vars))
    {
      parse_str($vars, $this->_get);
    }
  }

  protected function _processDecryptedGetVars()
  {
    if (is_array($this->_get) && count($this->_get) > 0)
    {
      foreach ($this->_get as $key => $value)
      {
        $this->_controller_object->get->set($key, $value);
      }
    }
  }

  protected function _checkPath($uri)
  {
    try
    {
      $this->_path->parse($uri);
      return true;
    }
    catch (Exception $e)
    {
      $this->_path->module_controller = 'Main_Controller_Error';
      $this->_path->controller  = 'error';
      $this->_path->module      = 'main';
      $this->_path->action      = 'notfound';
    }
    return false;
  }
}
