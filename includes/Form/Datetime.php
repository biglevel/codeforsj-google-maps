<?php
class Form_Datetime extends Form_Date
{
  protected $_fields = array('hour','minute','offset');

  public function validate()
  {
    if (parent::validate())
    {
      // check for required fields
      if ($this->required == true && ($this->_anyEmptyUnit() == true || parent::_anyEmptyUnit() == true))
      {
        $this->errors[] = $this->label . " is a required field.  Incomplete date and/or time has been provided";
        return false;
      }
    }
    /*
    $this->errors[] = "The value provided for '".$this->label."' was invalid ('".$this->value."')";
    return false;
     * 
     */
    return true;
  }

  protected function _anyEmptyUnit()
  {
    foreach ($this->_fields as $suffix)
    {
      if (!isset($this->value[$suffix]))
      {
        return true;
      }
    }
    return false;
  }

  public function field()
  {
    $date = parent::field();
    // build up time
    $time = array();
    foreach ($this->_fields as $suffix)
    {
      $value = 'value_' . $suffix;
      $this->$value = (isset($this->$value)) ? $this->$value : '';
      $time[] = $this->_select($suffix);
    }
    return implode(' &nbsp;   Time: ', array(
      $date,
      implode(' : ', $time)
    ));
  }

  protected function _hour()
  {
    $hours = array();
    for ($i=1; $i <= 12; $i++)
    {
      $hours[$i] = sprintf("%02d", $i);
    }
    return $hours;
  }

  protected function _minute()
  {
    $increment = (empty($this->minute_increment)) ? 15 : $this->minute_increment;
    $mins = array();
    for ($i=0; $i <= 60; $i += $increment)
    {
      $mins[$i] = sprintf("%02d", $i);
    }
    return $mins;
  }

  protected function _offset()
  {
    return array(
      'am' => 'am',
      'pm' => 'pm'
    );
  }
}