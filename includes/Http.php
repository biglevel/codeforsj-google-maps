<?php
class Http
{
  public $cli;
  protected $_logger;

  public function __construct($domain = false)
  {
    $this->cli = curl_init();
    curl_setopt( $this->cli, CURLOPT_RETURNTRANSFER,  true);
    curl_setopt( $this->cli, CURLOPT_HEADER,          false);
    curl_setopt( $this->cli, CURLOPT_SSL_VERIFYPEER,  false );
    curl_setopt( $this->cli, CURLOPT_FOLLOWLOCATION,  true );
    curl_setopt( $this->cli, CURLOPT_AUTOREFERER,     true );
    curl_setopt( $this->cli, CURLOPT_CONNECTTIMEOUT,  600 );
    curl_setopt( $this->cli, CURLOPT_TIMEOUT,         600 );
    curl_setopt( $this->cli, CURLOPT_MAXREDIRS,       3 );
    if (!empty($domain))
    {
      $this->domain = $domain;
    }
  }

  public function  __destruct()
  {
    curl_close ($this->cli);
  }

  public function get($url)
  {
    curl_setopt($this->cli,   CURLOPT_URL, $url);
    curl_setopt($this->cli,   CURLOPT_POST, false);
    $response = curl_exec ( $this->cli );
    $this->_log($url, 'get', $response);
    return $response;
  }

  public function post($url, $postfields)
  {
    curl_setopt( $this->cli, CURLOPT_POST, 1 );
    curl_setopt( $this->cli, CURLOPT_URL, $url );
    curl_setopt( $this->cli, CURLOPT_POSTFIELDS, $postfields );
    $response = curl_exec ( $this->cli );
    $this->_log($url, 'post', $response, $postfields);
    return $response;
  }
  
  public function errors()
  {
    return $this->_getCurlErrors();
  }
  
  public function setLogger($logger)
  {
    if ($logger instanceof Http_Logger)
    {
      $this->_logger = $logger;
      return true;
    } else
    {
      throw new Exception("The object provided is not an instance of Http_Logger");
    }
  }

  protected function _getCurlErrors()
  {
    $curl_response = array(
        'errno' => curl_errno($this->cli),
        'error' => curl_error($this->cli)
    );
    return $curl_response;
  }

  protected function _log($url, $type, $response, $post_fields = array())
  {
    if ($this->_logger != false)
    {
      $this->_logger->record($url, $type, $this->_getCurlErrors(), $response, print_r($post_fields,true));
    }
  }
}

