<?php
class Socket
{
  // socket details
  public $connected = false;
  public $host = false;
  public $port = false;
  public $timeout = '5';
  public $type = 'tcp';

  // message handler
  public $error_num = false;
  public $error_txt = false;

  // socket handler
  protected $_sock = false;

  /*
  public function __construct($host = false, $port = false, $timeout = 5, $type = 'tcp')
  {
    if ($host) $this->host = $host;
    if ($port && is_numeric($port)) $this->port = $port;
    if ($timeout && is_numeric($timeout)) $this->timeout = $timeout;
    if ($type == 'tcp' || $type == 'udp') $this->type = $type;
    if ($this->host && $this->port && $this->timeout && $this->type) 
    {
      $this->connect();
    }
  }
   */

  public function __destruct()
  {
    if ($this->connected)
    {
      $this->connected = false;
      fclose($this->_socket);
    }
  }

  public function connect()
  {
    if ( !( $this->_sock = socket_create( AF_INET, SOCK_STREAM, SOL_TCP ) ) )
    {
      die("Error: Could not create socket, error code is: ".socket_last_error( ).", error message is: ".socket_strerror( socket_last_error( ) ) );
    }
    $err = socket_connect( $this->_sock, $this->host, $this->port );
    if ( !$err )
    {
      die("Error: Could not connect to PMTA server\n");
    }
    return true;
  }

  public function disconnect()
  {
    if ($this->connected)
    {
      $this->connected = false;
      socket_close( $this->_sock );
    }
  }

  public function put($content = false)
  {
    socket_write( $this->_sock, $content );
  }

  public function get()
  {
    $idx = 0;
    $str = '';
    while ( $buffer = socket_read( $this->_sock, 1024, PHP_BINARY_READ ) )
    {
      $str .= $buffer;
      if ( !( "" == trim( $buffer ) ) || $buffer )
      {
        break;
      }
    }
    return $str;
  }

  protected function _check()
  {
  }

}
