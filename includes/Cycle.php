<?php
class Cycle
{
  protected $items     = array();
  protected $position  = -1;

  public function __construct($items = array())
  {
    if (!is_array($items) && count($items)==0)
    {
      throw new Exception("The provided attribute for your cycle was not an array.");
    }
    $this->items = array_values($items);
  }

  public function next()
  {
    $this->position++;
    if ($this->position == count($this->items))
    {
      $this->position = 0;
    }
    return (isset($this->items[$this->position])) ? $this->items[$this->position] : false;
  }

  public function rewind()
  {
    $this->position--;
    if ($this->position < 0)
    {
      $this->position = (count($this->items)-1);
    }
    return $this->items[$this->position];
  }
}
