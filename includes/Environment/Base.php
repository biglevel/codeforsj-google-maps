<?php
abstract class Environment_Base
{
  public function strap()
  {
    $methods = get_class_methods($this);
    foreach ($methods as $method)
    {
      if (substr($method,0,3) == 'set' && strlen($method) > 3)
      {
        call_user_func(array($this, $method));
      }
    }
  }

  protected function _set($name, $attributes = array())
  {
    if (isset($this->$name))
    {
      throw new Exception ("You have already register '" . $name . "' as part of your environment settings");
    }

    $object = new Environment_Container;
    foreach ($attributes as $key => $value)
    {
      $object->$key = $value;
    }
    $this->$name = $object;

    // return object to allow for chaining of registers
    return $this;
  }
}