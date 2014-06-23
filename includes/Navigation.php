<?php
class Navigation
{
  public $settings;
  protected static $_instance;

  protected function __construct()
  {
    parent::__construct('top');
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