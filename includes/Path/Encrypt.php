<?php
class Path_Encrypt
{
  protected static $_key = '123456789012345678901234567890123456789012345678901234567890';

  public static function encrypt($value, $key = false)
  {
    self::setKey($key);
    $value = self::_serialize($value);
    $crypted = mcrypt_cbc(MCRYPT_RIJNDAEL_128,substr(self::$_key,0,32) ,$value, MCRYPT_ENCRYPT,substr(self::$_key,32,16));
    $return = base64_encode($crypted);
    return $return;
  }

  public static function decrypt($value, $key = false)
  {
    self::setKey($key);
    $encrypted = base64_decode($value);
    $decrypted = str_replace(chr(0), '', mcrypt_cbc(MCRYPT_RIJNDAEL_128,substr(self::$_key,0,32) ,$encrypted, MCRYPT_DECRYPT,substr(self::$_key,32,16)));
    $value = self::_unserialize($decrypted);
    return $value;
  }

  public static function setKey($key)
  {
    if ($key !== false)
    {
      $this->_key = $key;
    }
  }

  protected static function _serialize($value)
  {
    return (is_array($value)) ? 'serialized://' . serialize($value) : 'unserialized://'.$value;
  }

  protected static function _unserialize($value)
  {
    switch (true)
    {
      case (substr($value,0,13) == 'serialized://'):
        $value = unserialize(substr($value, 13, (strlen($value)-13)));
        break;
      case (substr($value,0,15) == 'unserialized://'):
        $value = substr($value, 15, (strlen($value)-15));
        break;
      default:
        $value = false;
        break;
    }
    return $value;
  }
}