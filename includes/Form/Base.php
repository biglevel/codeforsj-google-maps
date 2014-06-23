<?php
abstract class Form_Base
{
  // field information
  public $name            = false;
  public $allow_multiple  = false;
  public $value           = false;
  // html
  public $id              = false;
  public $class           = false;
  public $label           = false;
  public $description     = false;
  // extras
  public $required        = false;
  public $attributes      = array();
  public $confirm_twice   = false;
  public $errors          = array();
  
  protected $_template;

  public function __construct($name, $allow_multiple = false)
  {
    if (empty($name) && $allow_multiple == false)
    {
      throw new Exception("When creating a new form element a name must be provided.");
    }
    if ($allow_multiple == true)
    {
      $this->value = array();
    }
    $this->allow_multiple = $allow_multiple;
    $this->name = $name;
  }

  public function validate()
  {
    if ($this->required == true && empty($this->value) === true && $this->value != '0')
    {
      $this->errors[] = $this->label . " is a required field.  No value has been provided.";
      return false;
    }
    return true;
  }

  public function set($key, $value)
  {
    if (isset($this->$key))
    {
      $this->$key = $value;
      return true;
    }
    throw new Exception("The option '".$key."' is not available for this element");
  }

  public function label()
  {
    $this->_defaultLabel();
    $return = $this->label;
    if ($this->required == true)
    {
      $return.= "*";
    }
    $return = sprintf(
        $this->_labelTemplate(),
        $this->name,
        count($this->errors)>0 ? $this->_flagError(): '',
        $return
    );
    return $return;
  }

  public function field()
  {
    $this->_defaultLabel();
    $this->_defaultClass();
    $this->_defaultId();
    return sprintf($this->_template, ($this->allow_multiple==true) ? $this->name.'[]' : $this->name, $this->id, $this->class, $this->value, $this->_convertAttributes());
  }

  public function render()
  {
    //if ($this->_template === null)
    {
      throw new Exception("The Form Object does not have a HTML template for rendering");
    }
    
  }

  protected function _convertAttributes()
  {
    $attributes = array();
    if (count($this->attributes)>0 && is_array($this->attributes))
    {
      foreach ($this->attributes as $key => $value)
      {
        if (is_numeric($key))
        {
          $attributes[] = $value;
        }
        else
        {
          $attributes[] = $key . '="' . $value.'"';
        }
      }
    }
    return implode(' ', $attributes);
  }

  protected function _labelTemplate()
  {
    return '<span id="%sLabel"%s>%s</span>';
  }

  protected function _flagError()
  {
    return ' class="formError"';
  }

  protected function _defaultLabel()
  {
    if (empty($this->label))
    {
      $this->label = $this->name;
    }
  }

  protected function _defaultClass()
  {
    if (empty($this->class))
    {
      $this->class = $this->name;
    }
  }

  protected function _defaultId()
  {
    if (empty($this->id))
    {
      $this->id = $this->name;
    }
  }
}