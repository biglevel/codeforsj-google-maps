<?php
class Ssh
{
  public $host = false;
  public $port = 22;
  public $user = false;
  public $pass = false;

  public $opened      = false;
  public $response    = '';
  public $error       = '';

  public $connection  = false;
  public $stream      = false;
  public $stream_err  = false;

  protected $_callbacks = array(
    'ignore'      => array('self', 'ignore'),
    'debug'       => array('self', 'debug'),
    'macerror'    => array('self', 'macerror'),
    'disconnect'  => array('self', 'disconnect')
  );

  public function __construct()
  {
    if (!function_exists('ssh2_connect'))
    {
      throw Exception ("The PECL SSH2 has not been installed on this server.  Enable this feature before running this class");
    }
  }
  
  public function connect($host = false, $port = false, $user = false, $pass = false)
  {
    if ($host != false) $this->host = $host;
    if ($port != false) $this->port = $port;
    if ($user != false) $this->user = $user;
    if ($pass != false) $this->pass = $pass;

    if ($this->opened == false)
    {
      $open = $this->_open();
      if ($this->_open())
      {
        $this->_auth();
      }
    }
    return $this->opened;
  }

  protected function _open()
  {
    if ($this->host == false || is_numeric($this->port) == false)
    {
      $this->error = "Both a username and password are required";
    }
    else
    {
      if ($this->connection !== false)
      {
        return true;
      }
      if ($this->connection == false && $this->host != false)
      {
        $methods = array(
          /*
          'kex'     => 'diffie-hellman-group-exchange-sha1',
          'hostkey' => 'ssh-rsa',
          'kex' => 'diffie-hellman-group1-sha1',
          'client_to_server' => array(
            'crypt' => '3des-cbc',
            'comp' => 'none'),
          'server_to_client' => array(
            'crypt' => 'aes256-cbc,aes192-cbc,aes128-cbc',
            'comp' => 'none')
           */
        );
        $this->connection = @ssh2_connect($this->host, $this->port, $methods, $this->_callbacks);
        if ($this->connection !== false)
        {
          return true;
        }
      }
    }
    if (empty($this->error))
    {
      $this->error = "Could not connect to host " . $this->host . ":" . $this->port;
    }
    return false;
  }

  protected function _auth()
  {
    if ($this->user == false || $this->pass == false)
    {
      $this->error = "Both a username and password are required";
    }
    else
    {
      if ($this->connection !== false)
      {
        $auth = @ssh2_auth_password($this->connection, $this->user, $this->pass);
        if ($auth != false)
        {
          $this->opened = true;
          return true;
        }
      }
    }
    if (empty($this->error))
    {
      $this->error = "Could not authenticate username / password";
    }
    return false;
  }

  public function run($cmd)
  {
    $this->connect();
    if ($this->opened)
    {
      $shell = ssh2_exec($this->connection, $cmd);
      $error = ssh2_fetch_stream($shell, SSH2_STREAM_STDERR);
      stream_set_blocking($shell,  true);
      stream_set_blocking($error, true);
      $this->error    = trim(stream_get_contents($error));
      $this->response = trim(stream_get_contents($shell));
      return (empty($this->error)) ? $this->response : false;
    }
    return false;
  }

  public function files($path, $callback = false)
  {
    $open = $this->connect();
    if ($open == true)
    {
      $sftp = ssh2_sftp($this->connection);
      $base = "ssh2.sftp://$sftp/" . ltrim($path, '/');
      if (file_exists($base) && is_dir($base))
      {
        $files = array();
        $dh = @opendir($base);
        if ($dh == false)
        {
          $this->error = "Could not open dir . " . $path;
          return false;
        }
        if ($callback==true)
        {
          while (($file = readdir($dh)) !== false)
          {
            $response = call_user_func($callback, rtrim($base,'/') . '/' . $file);
            if ($response != false)
            {
              $files[] = $response;
            }
          }
        }
        else
        {
          while (($file = readdir($dh)) !== false)
          {
            if ($file != '.' && $file != '..')
            {
              $files[] = $file;
            }
          }
        }
        closedir($dh);
        return $files;
      }
    }
    $this->error = "Could not open dir . " . $path;
    return false;
  }

  public function ignore($message)
  {
    /*
    out("Callback: Ignore");
    out("Message > " . $message);
     */
    die("Need to finish this.");
  }

  public function debug($message, $language, $always_display)
  {
    /*
    out("Callback: Debug");
    out("Message > " . $message);
    out("Language > " . $language);
    out("Always Display > " . $always_display);
     */
    die("Need to finish this.");
  }

  public function macerror($packet)
  {
    /*
    out("Callback: Macerror");
    out("Packet > " . $packet);
     */
    die("Need to finish this.");
  }

  public function disconnect($reason, $message, $language)
  {
    /*
    out("Callback: Disconnect");
    out("Reason > " . $reason);
    out("Message > " . $message);
    out("Language > " . $language);
     */
    die("Need to finish this.");
  }

}