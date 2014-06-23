<?php
class Navigation_Object
{
  public $container;
  public $anchor;
  public $list;
  
  protected $_items;

  public function __construct($name)
  {
    $this->container = new Navigation_Attributes;
  }

  public function create($name, $label, $target)
  {
    if (isset($this->_items[$name]))
    {
      throw new Exception("Nav item menu");
    }
  }

}