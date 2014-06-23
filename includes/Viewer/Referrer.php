<?php
class Viewer_Referrer
{
  protected $_referrer;
  protected $_keys = array(
    'scheme',
    'host',
    'user',
    'pass',
    'path',
    'query',
    'fragment'
  );

  public function parse($referrer = false)
  {
    $this->_referrer = $this->_determineReferrer($referrer);
    $return = new stdClass();
    $elements = parse_url($this->_referrer);
    foreach ($this->_keys as $key)
    {
      $return->$key = (isset($elements[$key])) ? $elements[$key] : false;
    }
    return $return;
  }

  protected function _determineReferrer($referrer)
  {
    if ($referrer != false)
    {
      return $referrer;
    }
    else
    {
      if (isset($_SERVER['HTTP_REFERER']))
      {
        return $_SERVER['HTTP_REFERER'];
      }
    }
    return '';
  }
}