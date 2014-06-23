<?php
class Path_Mask
{
  protected static $_key = '123456789012345678901234567890123456789012345678901234567890';
  
  public static function encrypt($value, $key = false)
  {
    self::setKey($key);
    $crypted = self::_rc4(self::$_key, $value);
    $return = base64_encode($crypted);
    return $return;

  }

  public static function decrypt($value, $key = false)
  {
    self::setKey($key);
    $decoded = base64_decode($value);
    $return = self::_rc4(self::$_key, $decoded);
    return $return;
  }

  public static function setKey($key)
  {
    if ($key !== false)
    {
      $this->_key = $key;
    }
  }
  
  protected static function _rc4($key, $data)
  {
    static $SC;
    $swap = create_function('&$v1, &$v2', '$v1 = $v1 ^ $v2; $v2 = $v1 ^ $v2; $v1 = $v1 ^ $v2;');
    $ikey = crc32($key);
    if (!isset($SC[$ikey]))
    {
      $S = range(0, 255);
      $j = 0;
      $n = strlen($key);
      for ($i = 0; $i < 255; $i++)
      {
        $char = ord($key{$i % $n});
        $j = ($j + $S[$i] + $char) % 256;
        $swap($S[$i], $S[$j]);
      }
      $SC[$ikey] = $S;
    }
    else
    {
      $S = $SC[$ikey];
    }
    $n = strlen($data);
    $data = str_split($data, 1);
    $i = $j = 0;
    for ($m = 0; $m < $n; $m++)
    {
      $i = ($i + 1) % 256;
      $j = ($j + $S[$i]) % 256;
      $swap($S[$i], $S[$j]);
      $char = ord($data[$m]);
      $char = $S[($S[$i] + $S[$j]) % 256] ^ $char;
      $data[$m] = chr($char);
    }
    return implode('', $data);
  }
}