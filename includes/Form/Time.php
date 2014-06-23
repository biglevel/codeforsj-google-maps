<?php
class Form_Time extends Form_Select
{
  public $options        = array();
  protected $_template   = '<select name="%s" id="%s" class="%s" %s>%s</select>';

  public function validate()
  {
    if (parent::validate())
    {
      if (in_array($this->value, array_keys($this->options)))
      {
        return true;
      }
    }
    $this->errors[] = "The value provided for '".$this->label."' was invalid ('".$this->value."')";
    return false;
  }

  public function field()
  {
    // capture the return from parent so it initiates other fields
    $this->_defaultLabel();
    $this->_defaultClass();
    $this->_defaultId();
    // rewrite parent
    $options = array();
    foreach ($this->options as $key => $value)
    {
      $selected = ($this->value == $key) ? ' selected="selected"' : '';
      $options[] = sprintf($this->_listItem(), $key, $selected, $value);
    }
    return sprintf($this->_template, ($this->allow_multiple==true) ? $this->name.'[]' : $this->name, $this->id, $this->class, $this->_convertAttributes(), implode("\n", $options));
  }

  protected function _listItem()
  {
    return '<option value="%s"%s>%s</option>';
  }
}