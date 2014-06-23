<?php
class Viewer_Browser
{
  public $agent;

  public function parse($ua = false)
  {
    if ($this->_isGetBrowserAvailable())
    {
      $this->agent = $this->_determineUserAgent($ua);
      if (!empty($this->agent))
      {
        return get_browser($this->agent, false);
      }
    }
    return false;
  }

  protected function _isGetBrowserAvailable()
  {
    if (ini_get('browscap') != '')
    {
      return true;
    }
    return false;
  }

  protected function _determineUserAgent($agent)
  {
    if ($agent !== false)
    {
      return $agent;
    }
    else
    {
      if (isset($_SERVER['HTTP_USER_AGENT']))
      {
        return $_SERVER['HTTP_USER_AGENT'];
      }
    }
    return '';
  }
}