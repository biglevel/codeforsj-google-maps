<?php
class Form_Checkbox extends Form_Base
{
  protected $_template = '<input type="checkbox" name="%s" id="%s" class="%s" %s />';

  public function field()
  {
    // capture the return from parent so it initiates other fields
    parent::field();

    // rewrite parent
    if (!empty($this->value) || $this->value == true )
    {
      $this->attributes['checked'] = true;
    }
    
    return sprintf($this->_template, ($this->allow_multiple==true) ? $this->name.'[]' : $this->name, $this->id, $this->class, $this->_convertAttributes());
  }
}