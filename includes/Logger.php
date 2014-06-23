<?php
class Logger
{
  public $verbose = 0;
  
  protected $_messages = array();

  public function add($level, $message)
  {
    $this->_messages[$level] = $message;
    if ($this->verbose)
    {
      $this->_output($message);
    }
  }

  protected function _output($message)
  {
    echo $message."\n";
  }
}