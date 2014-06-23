<?php
class Environment
{
  public $settings;
  
  protected static $_environment;
  protected static $_instance;

  protected function __construct()
  {
    self::$_environment = defined('ENVIRONMENT') ? ENVIRONMENT : 'production';
    $env = 'Env_' . ucfirst(self::$_environment);
    if (!class_exists($env))
    {
      throw new Exception ("Environment '".$env."' could not be found.");
    }
    $this->settings = new $env;
    $this->settings->strap();
  }

  public static function instance()
  {
    if ( !self::$_instance )
    {
      self::$_instance = new self;
    }
    return self::$_instance;
  }

}