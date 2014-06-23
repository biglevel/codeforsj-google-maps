<?php
class Authentication
{
  public $auth;
  public $username;
  public $password;
  protected static $_instance;

  public static function instance()
  {
    if ( self::$_instance === null )
    {
      $object = new self;
      $object->loadAdminDefaults();
      self::$_instance = $object;
    }
    return self::$_instance;
  }

  public function login($username = false, $password = false, $extras = false)
  {
    $this->loadAdminDefaults();
    if ($this->username == $username && $this->password == $password)
    {
      return true;
    }
    return false;
  }

  public function loadAdminDefaults()
  {
    if ($this->auth == false)
    {
      $env = Environment::instance();
      $this->auth = (!isset($env->settings->auth)) ? $this->_default() : $env->settings->auth ;
      $this->username = (!isset($this->auth->username)) ? 'admin' : $this->auth->username;
      $this->password = (!isset($this->auth->password)) ? 'admin' : $this->auth->password;
      return array(
        'username' => $this->username,
        'password' => $this->password
      );
    }
  }

  protected function _default()
  {
    $settings = new stdClass();
    $settings->username = 'admin';
    $settings->password = 'admin';
    return $settings;
  }

}