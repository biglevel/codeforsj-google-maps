<?php
class Form
{
  public $action  = false; // where are we posting the form to?!
  public $method  = false; // post or get
  public $errors  = array();

  protected $_fields = array();

  public function __construct()
  {
    $this->setup();
  }

  public function setup()
  {
    throw new Exception("The form that you've created does not have any elements to process.");
  }

  public function create($name, $type, $options = array(), $allow_multiple = false)
  {
    if (isset($this->$name))
    {
      throw new Exception("The form element '".$name."' already exists.  Element names must be unique");
    }
    $base = 'Form_'. ucfirst($type);
    if (class_exists($base) === false)
    {
      throw new Exception("The form element type '".$type."' for element '".$name."' is trying to use an invalid element type");
    }
    $this->$name = new $base($name, $allow_multiple);
    $object =& $this->$name;
    if (count($options)>0)
    {
      foreach ($options as $key => $value)
      {
        $object->set($key, $value);
      }
    }
    $this->_fields[] = $name;
  }

  public function validate()
  {
    $this->_transferData();
    foreach ($this->_fields as $name)
    {
      $object =& $this->$name;
      if ($object->validate() == false && count($object->errors)>0)
      {
        $this->errors[$name] = $object->errors;
      }
    }
    return (count($this->errors)>0) ? false : true;
  }

  public function addError($field, $message)
  {
    if (isset($this->$field))
    {
      $this->$field->errors[] = $message;
      $this->errors[$field][] = $message;
    }
    else
    {
      $this->errors['custom'][] = $message;
    }
  }

  public function showErrors($display = false)
  {
    $errors = array();
    foreach ($this->errors as $key => $sub)
    {
      foreach ($sub as $error)
      {
        $errors[] =  sprintf($this->_errorItem(), htmlspecialchars($error, ENT_QUOTES, 'UTF-8'));
      }
    }
    $html =  sprintf($this->_errorContainer(), sprintf($this->_errorList(), implode("\n", $errors)))."\n";
    if ($display == true)
    {
      echo (count($errors)>0) ? $html : '';
    }
    return (count($errors)>0) ? $html : '';
  }

  public function  __set($name, $value)
  {
    if ($name == 'data' && is_array($value))
    {
      foreach ($value as $key => $postvar)
      {
        if (isset($this->$key))
        {
          $this->$key->value = $postvar;
        }
      }
    }
    $this->$name = $value;
  }

  protected function _errorContainer()
  {
    return "<div class=\"errorBlock\">%s</div>";
  }

  protected function _errorList()
  {
    return "<ul class=\"errorList\">%s</ul>";
  }

  protected function _errorItem()
  {
    return '<li>%s</li>';
  }

  protected function _transferData()
  {
    if (!isset($this->data))
    {
      throw new Exception("Data provided to form action to validate is not an array.");
    }
  }

}