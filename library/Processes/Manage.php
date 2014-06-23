<?php
class Processes_Manage
{
  protected static $_instance;

  protected function __construct()
  {
  }

  public static function instance()
  {
    if (self::$_instance == null)
    {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  public static function running($pid_list)
  {
    if (!is_array($pid_list))
    {
      throw new Exception("PID list must be in array format");
    }
    $manage = self::instance();
    $running = array();
    foreach ($pid_list as $pid)
    {
      if ($manage->checkPid($pid))
      {
        $running[] = $pid;
      }
    }
    return $running;
  }

  public function checkPid($pid)
  {
    if (posix_kill($pid, 0) == true)
    {
      return true;
    }
    return false;
  }
}