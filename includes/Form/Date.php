<?php
class Form_Date extends Form_Select
{
  public $year_start  = false;
  public $year_end    = false;

  public function validate()
  {
    // check for required fields
    if ($this->required == true && $this->_anyEmptyUnit() == true)
    {
      $this->errors[] = $this->label . " is a required field.  Incomplete date has been provided";
      return false;
    }
    // validate data if something was provided
    if (!empty($this->value))
    {

    }
    return true;
  }

  protected function _anyEmptyUnit()
  {
    $fields = array('month','day','year');
    foreach ($fields as $suffix)
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
    // capture the return from parent so it initiates other fields
    $this->_defaultLabel();
    $this->_defaultClass();
    $this->_defaultId();
    // rewrite parent
    $fields = array('month','day','year');
    $options = array();
    foreach ($fields as $suffix)
    {
      $this->value[$suffix] = (isset($this->value[$suffix])) ? $this->value[$suffix] : '';
      $options[] = $this->_select($suffix);
    }
    return implode(' / ', $options);
  }

  protected function _select($suffix)
  {
    $options = array();
    $method = '_' . $suffix;
    $values = $this->$method();
    foreach ($values as $key => $value)
    {
      if (isset($this->value[$suffix]))
      {
        $selected = ($this->value[$suffix] == $key) ? ' selected="selected"' : '';
      }
      $options[] = sprintf($this->_listItem(), $key, (isset($selected)) ? $selected : '', $value);
    }
    return sprintf($this->_template, ($this->allow_multiple==true) ? $this->name.'['.$suffix.'][]' : $this->name.'['.$suffix.']', $this->id.'_'.$suffix, $this->class.'_'.$suffix, $this->_convertAttributes(), implode("\n", $options));
  }

  protected function _month()
  {
    return array(
      '1' => 'Jan',
      '2' => 'Feb',
      '3' => 'Mar',
      '4' => 'Apr',
      '5' => 'May',
      '6' => 'Jun',
      '7' => 'Jul',
      '8' => 'Aug',
      '9' => 'Sep',
      '10' => 'Oct',
      '11' => 'Nov',
      '12' => 'Dec'
    );
  }

  protected function _day()
  {
    $days = array();
    for ($i=1; $i <= 31; $i++)
    {
      $days[$i] = sprintf("%02d", $i);
    }
    return $days;
  }

  protected function _year()
  {
    $year_start = ($this->year_start==null) ? (int) date('Y') : $this->year_start;
    $year_end   = ($this->year_end==null) ? (date('Y')+6) : $this->year_end;
    $years = array();
    for ($i=$year_start; $i<=$year_end; $i++)
    {
      $years[$i] = $i;
    }
    return $years;
  }
}