<?php

class Mysql_Instance
{

    public $dbh;
    public $stmt;
    public $firephp;
    public $pool = 'slaves_only'; // 'all', 'master_only', 'slaves_only'
    protected $_user;
    protected $_unix_sock;
    protected $_pass;
    protected $_database;
    protected $_host = 'localhost';
    protected $_port = '3306';
    protected $_last_server;
    protected $_servers = array();
    
    public static $persistent = false;
    
    public function __construct()
    {
        if (class_exists('FirePHP', false))
        {
            $this->firephp = FirePHP::getInstance(true);
            //$this->firephp->group('Mysql Queries');
        }
    }

    public function __destruct()
    {
        if (class_exists('FirePHP', false))
        {
            //$this->firephp->groupEnd();
        }
        $this->disconnect();
    }
    
    public function connect($pool = false)
    {
        // assume master every time if _last_server has not been determined
        if ($pool == 'master' && is_array($this->_host))
        {
            $this->_last_server = $this->_host[0];
        }
        if ($this->_last_server == false)
        {
            $this->_last_server = (is_array($this->_host) && count($this->_host)>0) ? $this->_host[0] : $this->_host;
        }
        try
        {
            if (!empty($this->_unix_sock))
            {
                $string = sprintf($this->_unixSocketString(), $this->_unix_sock, $this->_database);
            }
            else
            {
                $string = sprintf($this->_tcpConnectionString(), $this->_last_server, $this->_port, $this->_database);
            }
            $this->_servers[$this->_last_server] = new PDO($string, $this->_user, $this->_pass, array(PDO::ATTR_PERSISTENT => self::$persistent));
            $this->_servers[$this->_last_server]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->dbh =& $this->_servers[$this->_last_server];
            $this->_firebug(0, "Connect to database > {$this->_last_server}");
        }
        catch (PDOException $e)
        {
            throw new Exception("Database Instance error: " . $e->getMessage());
        }
    }
    
    public function lastServer()
    {
        return $this->_last_server;
    }

    public function set($name, $value)
    {
        $item = '_' . $name;
        $this->$item = $value;
    }

    public function disconnect()
    {
        $this->dbh = null;
    }
    
    public function execute($sql, $data = array())
    {
        $start = $this->_profilerTimestamp();
        $attempts = 0;
        while ($attempts < 3)
        {
            $attempts++;
            try
            {
                $this->_parseQueryForServer($sql);
                $this->connect();
                $this->stmt = $this->dbh->prepare($sql);
                $results = $this->stmt->execute($data);
                break;
            }
            catch (Exception $e)
            {
                if ($attempts == 3)
                {
                    $errors = ($this->stmt != false) ? implode(',', $this->stmt->errorInfo()) : '';
                    throw new exception("[{$this->_last_server}] Failed to execute query: ({$errors}) " . $sql . "\n---\n" . $e->getMessage() . "\n\n");
                    //return false;
                }
            }
        }
        $this->_firebug($start, sprintf(
                "[{$this->_last_server}] Mysql Execute: %s \n Values: %s", $sql, implode(', ', $data)
            ));
        return $results;
    }

    protected function _firebug($start, $message)
    {
        if ($start > 0)
        {
            $stop = $this->_profilerTimestamp();
            $message.= " Runtime: " . ($stop - $start);
        }
        if ($this->firephp != false)
        {
            $this->firephp->log($message);
        }
    }

    protected function _profilerTimestamp()
    {
        $mtime = microtime(true);
        return $mtime;
    }

    public function fetch($sql, $data = array(), $container = null)
    {
        $return = array();
        $start = $this->_profilerTimestamp();
        try
        {
            $this->_parseQueryForServer($sql);
            $this->connect();
            $container = $this->_validateRowObject($container);
            $this->stmt = $this->dbh->prepare($sql);
            $this->stmt->execute($data);
            $this->stmt->setFetchMode(PDO::FETCH_INTO, $container);
            while ($row = $this->stmt->fetch())
            {
                $object = new $container;
                $object = clone $row;
                $return[] = $object;
            }
        }
        catch (Exception $e)
        {
            $errors = ($this->stmt != false) ? implode(',', $this->stmt->errorInfo()) : '';
            throw new exception("[{$this->_last_server}] Failed to execute query fetch(): ({$errors}) " . $sql . "---\n" . $e->getMessage() . "\n\n");
        }
        $this->_firebug($start, sprintf(
                "Mysql Fetch (%s): %s \n Values: %s", 
                $this->_last_server,
                $sql, 
                implode(', ', $data)
            ));
        return $return;
    }

    public function prepare($sql, $data = array(), $container = 'Mysql_Object')
    {
        $this->_parseQueryForServer($sql);
        $this->connect();
        $this->stmt = $this->dbh->prepare($sql);
        $this->stmt->setFetchMode(PDO::FETCH_CLASS, $container);
        $this->stmt->execute($data);
        return $this->stmt;
    }

    public function fetchOne($sql, $data = array(), $container = null)
    {
        $start = $this->_profilerTimestamp();
        $response = $this->fetch($sql, $data, $container);
        if (count($response) > 1)
        {
            throw new Exception("Expected a single object as part of the Mysql results, but received multiple.");
        }
        elseif (count($response) == 1)
        {
            $response = $response[0];
        }
        return $response;
    }

    public function __call($name, $value)
    {
        throw new Exception("While setting '" . $name . "' in '" . __CLASS__ . "' the attribute could not be found.");
    }

    protected function _parseQueryForServer($sql)
    {
        if (!is_array($this->_host))
        {
            $this->_last_server = $this->_host;
            return;
        }
        if ($this->pool == 'master_only')
        {
            $this->_last_server = $this->_host[0];
            return;
        }
        require_once("_parser.php");
        $parser = new PHPSQLParser($sql, false);
        /*
        if (!empty($sql))
        {
            echo "<pre>";
            echo $sql."\n\n--\n\n";
            print_r($parser);
            echo "</pre>";
            echo "<hr />";
        }
         */
        if (isset($parser->parsed['SELECT']) || isset($parser->parsed['SHOW']))
        {
            
            if ($this->pool == 'slaves_only' && count($this->_host)>1)
            {
                $position = rand(1,(count($this->_host)-1));
            }
            else
            {
                $position = rand(0,(count($this->_host)-1));
            }
            $server = $this->_host[$position];
            $this->_last_server = $server;
            return;
        }
        $this->_last_server = $this->_host[0];
        
    }

    protected function _validateRowObject($container)
    {
        if (!empty($container) && class_exists($container))
        {

            if (!$container instanceof Mysql_Object)
            {
                //throw new Exception("Object provided to act as the data container is not an instance of Mysql_Object.");
            }
            $container = new $container;
        }
        else
        {
            $container = new Mysql_Object();
        }
        return $container;
    }

    protected function _tcpConnectionString()
    {
        return "mysql:host=%s;port=%s;dbname=%s";
    }

    protected function _unixSocketString()
    {
        return "mysql:unix_socket=%s;dbname=%s";
    }

}
