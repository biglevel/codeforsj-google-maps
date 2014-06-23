<?php
class Http_Logger
{
  protected $_events = array();

  public function __construct()
  {
    
  }

  public function record($url, $type, $codes, $response, $post_data = false)
  {
    $this->_events[$type][] = array(
      'response'  => $response,
       'error'    => $codes['error'],
       'errno'    => $codes['errno'],
      'data'      => $post_data
    );
  }
}
