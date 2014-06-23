<?php
class Form_Textarea extends Form_Base
{
  protected $_template = '<textarea name="%s" id="%s" class="%s" %s>%s</textarea>';

  public function field()
  {
    // capture the return from parent so it initiates other fields
    $this->_defaultLabel();
    $this->_defaultClass();
    $this->_defaultId();
    // return button
    $attributes = $this->_convertAttributes();
    return sprintf($this->_template, ($this->allow_multiple==true) ? $this->name.'[]' : $this->name, $this->id, $this->class, $attributes, $this->value);
  }
}