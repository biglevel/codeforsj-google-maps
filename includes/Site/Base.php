<?php
abstract class Site_Base
{
  protected function __construct()
  {

  }

  public function boot($method = false)
  {
    if (!$method)
    {
      $this->_loadAll();
    }
  }

  protected function _loadAll()
  {
    try
    {
      foreach (get_class_methods($this) as $method)
      {
        if (substr($method,0,4) == 'init')
        {
          call_user_func(array($this, $method));
        }
      }
    }
    catch (Exception $e)
    {
      throw new Exception("Error found while loading Init for Program");
    }
  }
}