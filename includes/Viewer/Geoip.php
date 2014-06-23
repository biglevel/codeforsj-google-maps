<?php
class Viewer_Geoip
{
  public $ip;
  protected $_opened = array();

  public function parse($ip = false)
  {
    if ($this->_isGeoipAvailable())
    {
      $this->ip = $this->_determineIp($ip);
      return $this->_fetchGeoipDetails();
    }
    return false;
  }

  protected function _fetchGeoipDetails()
  {
    $return = false;
    if (function_exists('geoip_record_by_name'))
    {
      $array = @geoip_record_by_name($this->ip);
      if (is_array($array) && count($array) > 0)
      {
        $return = new stdClass();
        foreach ($array as $key => $value)
        {
          $return->$key = $value;
        }
        return $return;
      }
    }
    elseif (function_exists('geoip_record_by_addr'))
    {
      $return = @geoip_record_by_addr($this->_opened['GeoIPCity.dat'], $this->ip);
      
      if (function_exists('geoip_record_by_addr') && isset($this->_opened['GeoIPOrg.dat']))
      {
        $return->provider = geoip_org_by_addr($this->_opened['GeoIPOrg.dat'], $this->ip);
      }
    }
    
    if (function_exists('geoip_name_by_addr') && isset($this->_opened['GeoIPNetspeed.dat']))
    {
      $return->netspeed = geoip_name_by_addr($this->_opened['GeoIPNetspeed.dat'], $this->ip);
      $return->netspeedtxt = false;
      if (!is_numeric($return->netspeed))
      {
        switch ($return->netspeed)
        {
          case "Cable/DSL":
            $return->netspeedtxt = $return->netspeed;
            $return->netspeed = GEOIP_CABLEDSL_SPEED;
            break;
          case "Dailup";
            $return->netspeedtxt = $return->netspeed;
            $return->netspeed = GEOIP_DIALUP_SPEED;
            break;
          case "Corporate":
            $return->netspeedtxt = $return->netspeed;
            $return->netspeed = GEOIP_CORPORATE_SPEED;
            break;
          case 'Unknown':
          default:
            $return->netspeedtxt = $return->netspeed;
            $return->netspeed = GEOIP_UNKNOWN_SPEED;
            break;
        }
      }
      else
      {
        switch ($return->netspeed)
        {
          case GEOIP_CABLEDSL_SPEED:
            $return->netspeedtxt = "Cable/DSL";
            break;
          case GEOIP_DIALUP_SPEED;
            $return->netspeedtxt = "Dailup";
            break;
          case GEOIP_CORPORATE_SPEED:
            $return->netspeedtxt = "Corporate";
            break;
          case GEOIP_UNKNOWN_SPEED:
          default:
            $return->netspeedtxt = 'Unknown';
            break;
        }
      }
    }
    
    return $return;
  }

  protected function _isGeoipAvailable()
  {
    if (function_exists('geoip_record_by_name'))
    {
      return true;
    }
    else
    {
      if ($this->_includeGeoipFunctions())
      {
        if (function_exists('geoip_record_by_addr') && isset($this->_opened['GeoIPCity.dat']))
        {
          return true;
        }
      }
    }
    return false;
  }

  protected function _includeGeoipFunctions()
  {
    require_once("Geoip/geoip.inc");
    require_once("Geoip/geoipcity.inc");
    return $this->_includeAvailableDatabases();
  }

  protected function _includeAvailableDatabases()
  {
    $loaded = false;
    $env = Environment::instance();
    $path = (isset($env->settings->geoip->path)) ? $env->settings->geoip->path : '';
    if (isset($env->settings->geoip->databases))
    {
      $files = $env->settings->geoip->databases;
      // Loop through array and load all databases
      if (is_array($files) && count($files) > 0)
      {
        foreach ($files as $file)
        {
          if ($this->_loadDatabase($path, $file))
          {
            $loaded = true;
          }
        }
      }
      // Load 1 database
      elseif (!empty($files))
      {
        if ($this->_loadDatabase($path, $file))
        {
          $loaded = true;
        }
      }
      else
      {
        if ($this->_loadDatabase($path, 'GeoIPCity.dat'))
        {
          $loaded = true;
        }
      }
    }
    return $loaded;
  }

  protected function _loadDatabase($path, $database)
  {
    if (!isset($this->_opened[$database]) && !empty($database))
    {
      $file = !empty($path) ? $path.'/' : '';
      $file.= $database;
      if (file_exists($file))
      {
        switch ($database)
        {
          case 'GeoIP.dat':
            //$this->_opened[$database] = geoip_open( $file ,GEOIP_STANDARD);
            break;
          case 'GeoIPCity.dat':
            $this->_opened[$database] = geoip_open( $file ,GEOIP_STANDARD);
            return true;
            break;
          case 'GeoIPOrg.dat':
            $this->_opened[$database] = geoip_open( $file ,GEOIP_STANDARD);
            return true;
            break;
          case 'GeoIPNetspeed.dat':
            $this->_opened[$database] = geoip_open( $file ,GEOIP_STANDARD);
            return true;
            break;
        }
      }
    }
    return false;
  }

  protected function _determineIp($ip)
  {
    if ($ip != false)
    {
      return $ip;
    }
    else
    {
      if (isset($_SERVER['REMOTE_ADDR']))
      {
        return $_SERVER['REMOTE_ADDR'];
      }
    }
    return '';
  }
}