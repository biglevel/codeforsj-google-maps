<?php
class Processes_Forker
{
  public $children  = array();
  public $history   = array();
  protected $_max_children = 1;
  protected $_last_pid = false;
  protected static $_instance;
  
  protected function __construct($max_children)
  {
    if (!is_numeric($max_children))
    {
      throw new Exception("The amount of children specified must be numeric");
    }
    $this->_max_children = $max_children;
  }

  public static function setup($max_children)
  {
    if (self::$_instance == null)
    {
      self::$_instance = new self($max_children);
    }
    return self::$_instance;
  }

  public function create()
  {
    $pid = pcntl_fork();
    if( $pid == -1 )
    {
      throw new Exception("Unable to fork. exit the script");
    }
    elseif ( $pid ) 
    {
      $this->history[]  = $pid;
      $this->children[] = $pid;
      if(count($this->children) >= $this->_max_children)
      {
        // get the oldest worker child
        $pid = array_shift($this->children);
        pcntl_waitpid($pid, $status);
      }
      return false;
    }
    else
    {
      return true;
    }
  }
}