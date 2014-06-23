<?php
class Form_Select extends Form_Base
{
  public $options        = array();
  protected $_template   = '<select name="%s" id="%s" class="%s" %s>%s</select>';

  public function validate()
  {
    if (parent::validate())
    {
      if (is_array($this->value))
      {
        foreach ($this->value as $item)
        {
          if (!$this->_valueIsOption($item))
          {
            $this->errors[] = "The value provided for '".$this->label."' was invalid ('".$item."')";
            return false;
          }
        }
      }
      else
      {
        if (in_array($this->value, array_keys($this->options)))
        {
          return true;
        }
        $this->errors[] = "The value provided for '".$this->label."' was invalid ('".$this->value."')";
        return false;
      }
    }
    return false;
  }

  protected function _valueIsOption($value)
  {
    if (in_array($value, array_keys($this->options)))
    {
      return true;
    }
    return false;
  }

  public function field()
  {
    // capture the return from parent so it initiates other fields
    parent::field();
    
    // rewrite parent
    $options = array();
    foreach ($this->options as $key => $value)
    {
      if ($this->allow_multiple == true)
      {
        $selected = (in_array($key, $this->value)) ? ' selected="selected"' : '';
      }
      else
      {
        $selected = ($this->value == $key) ? ' selected="selected"' : '';
      }
      $options[] = sprintf($this->_listItem(), $key, $selected, $value);
    }
    return sprintf($this->_template, ($this->allow_multiple==true) ? $this->name.'[]' : $this->name, $this->id, $this->class, $this->_convertAttributes(), implode("\n", $options));
  }

  protected function _listItem()
  {
    return '<option value="%s"%s>%s</option>';
  }
}