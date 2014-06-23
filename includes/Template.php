<?php
class Template
{
  public $action;
  public $controller;
  public $module;

  protected $_path;
  protected $_modules;
  protected $_layout = 'index';
  
  protected $_cycle_items     = array();
  protected $_cycle_iterator  = 0;

  protected $_scripts = array();

  public function setLayoutPath($path = false)
  {
    if (!file_exists($path) && $path !== false)
    {
      throw new Exception ("The layout path '".$path."' does not exists");
    }
    $this->_path = $path;
  }

  public function head($type, $script)
  {
    $templates = $this->_templates(true);
    if (!in_array($type, array_keys($templates)))
    {
      throw new Exception("Template head does not accept the type '".$type."'");
    }
    $this->_scripts[] = sprintf($templates[$type], $script);
  }

  public function renderHead()
  {
    return implode("\n", $this->_scripts);
  }

  protected function _templates()
  {
    return array(
      'java'  => '<script type="text/javascript" src="%s"></script>',
      'style' => '<link type="text/css" href="%s" rel="stylesheet" />'
    );
  }

  public function setModulePath($path = false)
  {
    if (!file_exists($path) && $path !== false)
    {
      throw new Exception ("The layout path '".$path."' does not exists");
    }
    $this->_modules = $path;
  }

  public function setLayout($layout)
  {
    if (substr($layout, strlen($layout)-5, 4) == '.php')
    {
      $layout = substr($layout, -4);
    }
    $this->_requireLayout($layout);
    $this->_layout = $layout;
  }

  public function disableLayout()
  {
    $this->_layout = false;
  }

  public function render()
  {
    if ($this->_layout != false)
    {
      $this->_requireLayout($this->_layout);
      require_once($this->_path . '/' . $this->_layout . '.php');
    }
  }

  public function view()
  {
    $view = $this->_modules . '/' . strtolower($this->module) .'/views/' . strtolower($this->controller) . '/' . strtolower($this->action) .'.php';
    $this->_requireView($view);
    require_once($view);
  }

  public function cycle($items = array())
  {
    if (!is_array($items))
    {
      throw new Exception("The provided attribute for your cycle was not an array.");
    }
    if (count(array_diff($this->_cycle_items, $items)) > 0 || count($this->_cycle_items)==0)
    {
      $this->_cycle_items = $items;
      $this->_cycle_iterator = 0;
    }
    else
    {
      $this->_cycle_iterator++;
      if ($this->_cycle_iterator == count($this->_cycle_items))
      {
        $this->_cycle_iterator = 0;
      }
    }
    return $this->_cycle_items[$this->_cycle_iterator];
  }

  protected function _requireLayout($layout)
  {
    $this->_guessLayoutPath();
    if (!file_exists($this->_path . '/' . $layout .  '.php'))
    {
      throw new Exception ("The layout '".$layout.".php' does not exist in " . $this->_path);
    }
  }

  protected function _guessLayoutPath()
  {
    if ($this->_path == null)
    {
      if (defined('SOURCE'))
      {
        $this->_path = SOURCE .'/layouts';
      }
    }
  }

  protected function _requireView($view)
  {
    if (!file_exists($view))
    {
      throw new Exception ("The view for action '" . $this->action . "' does not exist in module path.");
    }
  }
  
}