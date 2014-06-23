<?php

abstract class Mysql_Table
{

    public $database;
    public $table;
    public $unique;
    public $error;
    public static $last_query; // last query ran
    public static $last_values; // values used for last insert
    protected $_id;
    protected $_keys = array();
    protected $_columns = array();
    protected $_updates = array();
    protected $_override;
    protected $_autoincrement;
    protected $_instance;
    protected $_statement;

    public function __construct()
    {
        $this->setup();
    }

    public function getColumns()
    {
        $columns = array_keys($this->_columns);
        return $columns;
    }

    public function setInstance($instance)
    {
        if ($instance instanceof Mysql_Instance)
        {
            $this->_instance = $instance;
        }
        else
        {
            throw new Exception("Instance provided for table is not an instance of Mysql_Instance");
        }
    }

    public function setup()
    {
        throw new Exception("Table structure has not been set for " . __CLASS__);
    }

    public function set($name, $type, $properties = array())
    {
        // ensure that the columns are unique
        if (isset($this->_columns[$name]))
        {
            throw new Exception("The column '" . $name . "' has already been configured.  Column names must be unique");
        }

        // validate data type
        if (!in_array($type, array('string', 'integer', 'date', 'datetime', 'inet', 'enum', 'bool', 'geometry')))
        {
            throw new Exception("You have defined the column '" . $name . "' with an invalid data type of '" . $type . "' for table '{$this->table}'");
        }

        // clean properties
        $properties = $this->_scrubProperties($properties);

        // determine properties & settings based on properties
        extract($properties);

        // make sure required properties for data types are provided
        switch ($type)
        {
            case 'enum':
                if ($values == false)
                {
                    throw new Exception($name . " has been specified as an enum.  Enums require values that are accepted for model.");
                }
                break;
        }

        // if only 1 integer key is provided, chances are it's set to auto increment.
        if ($primary)
        {
            $this->_determineintegerPrimaryKey($name, $type);
        }

        // append autoincrement column
        $this->_saveAutoincrement($name, $autoincrement);

        // register column and it's properties
        $this->_columns[$name] = array(
            'type'       => $type,
            'properties' => $properties
        );
    }

    public function onDuplicateKey($updates)
    {
        if (is_array($updates) && count($updates) > 0)
        {
            foreach ($updates as $name => $value)
            {
                if (!isset($this->_columns[$name]))
                {
                    throw new Exception("The column '" . $name . "' for on Duplicate Key does not exist in this table.");
                }
                $type = $this->_columns[$name]['type'];
                extract($this->_columns[$name]['properties']);
                
                switch ($type)
                {
                    case 'inet':
                        $value = "inet_aton('" . $value . "')";
                        break;
                    case 'geometry':
                        $value = "geomfromtext('" . $value . "')";
                        break;
                    case 'md5':
                        $value = "md5('" . $value . "')";
                        break;
                }
                $this->_updates[$name] = $value;
            }
        }
    }

    public function save()
    {
        $start = $this->_profilerTimestamp(); // for FirePHP profiling
        $this->_confirmInstance();
        $this->_prepareInsert();
        
        $this->_statement->execute();

        if (class_exists('FirePHP', false))
        {
            $stop = $this->_profilerTimestamp(); // for FirePHP profiling
            $firephp = FirePHP::getInstance(true);
            $message = sprintf(
                    "[{$this->_instance->lastServer()}] Mysql Execute: %s \n Values: %s", self::$last_query, implode(', ', self::$last_values)
            );
            $firephp->log($message . "\n Runtime: " . ($stop - $start));
        }
        $this->unique = ($this->_statement->rowCount() == 2) ? false : true;
        $id = $this->_instance->dbh->lastInsertId();
        return $id;
    }

    protected function _profilerTimestamp()
    {
        $mtime = microtime();
        $mtime = explode(" ", $mtime);
        $mtime = $mtime[1] + $mtime[0];
        return $mtime;
    }

    public function getSql()
    {
        return $this->_buildSql();
    }

    protected function _prepareInsert()
    {
        if ($this->_statement === null)
        {
            $this->_instance->connect('master');
            self::$last_query = $this->_buildSql();
            $this->_statement = $this->_instance->dbh->prepare(self::$last_query);
        }
        // bind insert columns
        self::$last_values = array();
        foreach ($this->_columns as $name => $settings)
        {
            extract($settings['properties']);
            if (isset($this->_override[$name]))
            {
                if (!empty($this->_override[$name]))
                {
                    $this->_statement->bindParam(':' . $name, $this->_override[$name]);
                    self::$last_values[':' . $name] = $this->_override[$name];
                }
            }
            elseif ($autoincrement === false && isset($this->$name) && empty($this->$name) == false)
            {
                $this->_statement->bindParam(':' . $name, $this->$name);
                self::$last_values[':' . $name] = $this->$name;
            }
        }

        // append on duplicate update if updates should run
        if (count($this->_updates) > 0)
        {
            foreach ($this->_updates as $name => $value)
            {
                if (isset($this->_override['update_' . $name]))
                {
                    if (!empty($this->_override['update_' . $name]) || $this->_override['update_' . $name] === 0 || $this->_override['update_' . $name] === '0')
                    {
                        $this->_statement->bindParam(':' . $name, $this->_override['update_' . $name]);
                        self::$last_values[':' . $name] = $this->_override[$name];
                    }
                }
                elseif ($autoincrement === false)
                {
                    if (!empty($this->_updates[$name]) && !is_numeric($value))
                    {
                        $this->_statement->bindParam(':update_' . $name, $value);
                        self::$last_values[':update_' . $name] = $value;
                    }
                }
            }
        }
    }

    protected function _errorCheck()
    {
        foreach ($this->_columns as $name => $settings)
        {
            if (!isset($this->$name) && $settings['properties']['required'] == true && $settings['properties']['autoincrement'] == false)
            {
                if ($settings['properties']['default'] !== false)
                {
                    $this->$name = $settings['properties']['default'];
                    continue;
                }
                else
                {
                    throw new Exception("The field '" . $name . "' has been flagged as required, but no value was specified.");
                }
            }
        }
    }

    protected function _confirmInstance()
    {
        $this->_instance = Mysql::instance();
    }

    protected function _buildSql()
    {
        $this->_errorCheck();
        $columns = array();
        $holders = array();
        foreach ($this->_columns as $name => $settings)
        {
            extract($settings['properties']);
            if ($autoincrement == false && isset($this->$name))
            {
                $value = $this->$name;
                // build up list of columns
                $columns[] = '`' . $name . '`';
                // auto determine if mysql function is being utilized
                $function = $this->_detectFunction($value);
                if (count($function) > 0)
                {
                    $holders[] = empty($function[2]) ? $function[0] : $function[1] . '(:' . $name . ')';
                    $this->_override[$name] = str_replace("'", '', $function[2]);
                }
                // no function called.. use regular value
                else
                {
                    if (empty($value) === false)
                    {
                        $holders[] = ':' . $name;
                    }
                    else
                    {
                        $holders[] = "'" . $value . "'";
                    }
                }
            }
        }
        if (empty($this->table))
        {
            throw new Exception("Database table has not been specified for " . __CLASS__);
        }
        // prepare basic sql statement
        $sql = "INSERT INTO `" . $this->table . "` (" . implode(',', $columns) . ") VALUES (" . implode(' , ', $holders) . ")";
        // append on duplicate update if updates should run
        if (count($this->_updates) > 0)
        {
            $updates = array();
            foreach ($this->_updates as $name => $value)
            {
                // auto determine if mysql function is being utilized
                $function = $this->_detectFunction($value);
                if (count($function) > 0)
                {
                    $placeholder = empty($function[2]) ? $function[0] : $function[1] . '(:' . $name . ')';
                    $updates[] = "`" . $name . "` = " . $placeholder;
                    $this->_override['update_' . $name] = str_replace("'", '', $function[2]);
                }
                else
                {
                    if (!empty($value) && !is_numeric($value) && $value !== 0)
                    {
                        $updates[] = "`{$name}` = :update_{$name}";
                    }
                    elseif (is_numeric($value) || $value === 0)
                    {
                        $updates[] = "`{$name}` = {$value}";
                    }
                    else
                    {
                        $updates[] = "`{$name}` = '{$value}'";
                    }
                }
            }
            if ($this->_autoincrement)
            {
                $updates[] = $this->_autoincrement . ' = LAST_INSERT_ID(' . $this->_autoincrement . ')';
            }
            $sql.=" ON DUPLICATE KEY UPDATE " . implode(' , ', $updates) . ";\n";
        }
        return $sql;
    }

    public function exists($param)
    {
        return (isset($this->_columns[$param])) ? true : false;
    }

    // __set() is run when writing data to inaccessible properties.
    public function __set($name, $value)
    {
        if (!isset($this->_columns[$name]))
        {
            throw new Exception("Cannot set column '" . $name . "' because it has not been defined as part of the table structure");
        }
        else
        {
            extract($this->_columns[$name]);

            // validate value against data type
            if ($this->_validate($name, $value) === false)
            {
                // data type failed, double check to make sure value wasn't provided with mysql function
                $function = $this->_detectFunction($value);
                if (count($function) == 0)
                {
                    throw new Exception($name . " must validate for data type '" . $type . "'. The value '" . $value . "' does not pass this test");
                }
            }

            switch ($type)
            {
                case 'inet':
                    $this->$name = "inet_aton(" . $value . ")";
                    break;
                case 'geometry':
                    $this->$name = "GeomFromText(" . $value . ")";
                    break;
                case 'md5':
                    $this->$name = "md5(" . $value . ")";
                    break;
                default:
                    $this->$name = $value;
                    break;
            }
        }
    }

    protected function _validate($name, $value)
    {
        $type = $this->_columns[$name]['type'];
        extract($this->_columns[$name]['properties']);
        switch ($type)
        {
            case 'integer':
                if (!is_numeric($value) && $value !== 0)
                {
                    return false;
                }
                break;
            case 'string':
                if ($minimum !== false && strlen($value) < $minimum)
                {
                    return false;
                }
                if ($maximum !== false && strlen($value) > $maximum)
                {
                    return false;
                }
                break;
            case 'date':
                break;
            case 'datetime':
                break;
            case 'inet':
                break;
            case 'geometry':
                break;
            case 'md5':
                break;
            case 'bool':

                break;
            case 'enum':
                if (!in_array($value, $values))
                {
                    return false;
                }
                break;
        }
        return true;
    }

    protected function _detectFunction($value)
    {
        if (!empty($value))
        {
            $match = "/^([^\s]*)\((.*)\)$/";
            preg_match($match, $value, $matches);
            if (isset($matches[1]))
            {
                switch (strtolower($matches[1]))
                {
                    case 'geomfromtext':
                    case 'ascii':
                    case 'bin':
                    case 'bit_length':
                    case 'char_length':
                    case 'char':
                    case 'character_length':
                    case 'concat_ws':
                    case 'concat':
                    case 'elt':
                    case 'export_set':
                    case 'field':
                    case 'find_in_set':
                    case 'format':
                    case 'hex':
                    case 'insert':
                    case 'instr':
                    case 'lcase':
                    case 'left':
                    case 'length':
                    case 'locate':
                    case 'lower':
                    case 'lpad':
                    case 'ltrim':
                    case 'make_set':
                    case 'mid':
                    case 'ord':
                    case 'position':
                    case 'quote':
                    case 'replace':
                    case 'reverse':
                    case 'right':
                    case 'rlike':
                    case 'rtrim':
                    case 'soundex':
                    case 'space':
                    case 'strcmp':
                    case 'substr':
                    case 'substring_index':
                    case 'substring':
                    case 'trim':
                    case 'ucase':
                    case 'unhex':
                    case 'upper':
                    case 'adddate':
                    case 'addtime':
                    case 'convert_tz':
                    case 'curdate':
                    case 'current_date':
                    case 'current_time':
                    case 'current_timestamp':
                    case 'curtime':
                    case 'date_add':
                    case 'date_format':
                    case 'date_sub':
                    case 'date':
                    case 'datediff':
                    case 'day':
                    case 'dayname':
                    case 'dayofmonth':
                    case 'dayofweek':
                    case 'dayofyear':
                    case 'extract':
                    case 'from_days':
                    case 'from_unixtime':
                    case 'get_format':
                    case 'hour':
                    case 'localtimestamp':
                    case 'makedate':
                    case 'microsecond':
                    case 'minute':
                    case 'month':
                    case 'monthname':
                    case 'now':
                    case 'period_add':
                    case 'period_diff':
                    case 'quarter':
                    case 'sec_to_time':
                    case 'second':
                    case 'str_to_date':
                    case 'subdate':
                    case 'subtime':
                    case 'sysdate':
                    case 'time_format':
                    case 'time_to_sec':
                    case 'time':
                    case 'timediff':
                    case 'timestamp':
                    case 'timestampadd':
                    case 'timestampdiff':
                    case 'to_days':
                    case 'unix_timestamp':
                    case 'utc_date':
                    case 'utc_time':
                    case 'utc_timestamp':
                    case 'week':
                    case 'weekday':
                    case 'weekofyear':
                    case 'year':
                    case 'yearweek':
                    case 'abs':
                    case 'acos':
                    case 'asin':
                    case 'atan2':
                    case 'atan':
                    case 'atan':
                    case 'ceil':
                    case 'ceiling':
                    case 'conv':
                    case 'cos':
                    case 'cot':
                    case 'crc32':
                    case 'degrees':
                    case 'exp':
                    case 'floor':
                    case 'ln':
                    case 'log10':
                    case 'log2':
                    case 'log':
                    case 'mod':
                    case 'oct':
                    case 'pi':
                    case 'pow':
                    case 'power':
                    case 'radians':
                    case 'rand':
                    case 'round':
                    case 'sign':
                    case 'sin':
                    case 'sqrt':
                    case 'tan':
                    case 'truncate':
                    case 'if':
                    case 'ifnull':
                    case 'nullif':
                    case 'aes_decrypt':
                    case 'aes_encrypt':
                    case 'compress':
                    case 'decode':
                    case 'des_decrypt':
                    case 'des_encrypt':
                    case 'encode':
                    case 'encrypt':
                    case 'md5':
                    case 'old_password':
                    case 'password':
                    case 'sha1':
                    case 'sha':
                    case 'uncompress':
                    case 'uncompressed_length':
                    case 'benchmark':
                    case 'charset':
                    case 'coercibility':
                    case 'collation':
                    case 'connection_id':
                    case 'current_user':
                    case 'database':
                    case 'found_rows':
                    case 'last_insert_id':
                    case 'row_count':
                    case 'schema':
                    case 'session_user':
                    case 'system_user':
                    case 'user':
                    case 'version':
                    case 'default':
                    case 'get_lock':
                    case 'inet_aton':
                    case 'inet_ntoa':
                    case 'is_free_lock':
                    case 'is_used_lock':
                    case 'master_pos_wait':
                    case 'name_const':
                    case 'rand':
                    case 'release_lock':
                    case 'sleep':
                    case 'uuid':
                    case 'values':
                    case 'default':
                    case 'get_lock':
                    case 'inet_aton':
                    case 'inet_ntoa':
                    case 'is_free_lock':
                    case 'is_used_lock':
                    case 'master_pos_wait':
                    case 'name_const':
                    case 'rand':
                    case 'release_lock':
                    case 'sleep':
                    case 'uuid':
                    case 'values':
                        return $matches;
                        break;
                }
            }
        }
        return array();
    }

    protected function _scrubProperties($properties)
    {
        if (!is_array($properties))
        {
            throw new Exception("Properties must be provided in an array format");
        }

        $cleaned = array();
        foreach ($this->_propertyStructure() as $property)
        {
            $cleaned[$property] = isset($properties[$property]) ? $properties[$property] : false;
        }

        return $cleaned;
    }

    protected function _determineintegerPrimaryKey($name, $type)
    {
        $this->_keys[] = $name;
        if (count($this->_keys) == 1 && $type == 'integer')
        {
            $this->_id = $name;
        }
        elseif (count($this->_keys) > 1)
        {
            $this->_id = false;
        }
    }

    protected function _saveAutoincrement($name, $auto)
    {
        if ($auto == true)
        {
            if ($this->_autoincrement !== null)
            {
                throw new Exception("Column with autoincrement has already been defined.  To prevent confusion, only 1 column should be provided to auto increment");
            }
            $this->_autoincrement = $name;
        }
    }

    protected function _propertyStructure()
    {
        return array(
            'validators',
            'required',
            'default',
            'primary',
            'autoincrement',
            'values',
            'minimum',
            'maximum'
        );
    }

}