<?php

class Mysql
{

    public static $instances;

    public static function instance($name = 'default', $config = array())
    {
        /*
         * Check to see if name of instance was provided
         * Note: intended to support multiple connections to the database
         */
        $name = (empty($name)) ? 'default' : $name;

        // check to see if config was provided
        if (!is_array($config) || count($config) == 0)
        {
            $config = self::_getDefault();
        }
        // determine instance
        if (!isset(self::$instances[$name]))
        {
            self::$instances[$name] = new Mysql_Instance();
            foreach ($config as $param => $value)
            {
                self::$instances[$name]->set($param, $value);
            }
            self::$instances[$name]->connect();
        }
        return self::$instances[$name];
    }

    protected static function _getDefault()
    {
        try
        {
            $env = Environment::instance();
            if (!isset($env->settings->mysql))
            {
                return false;
            }
            $settings = array();
            foreach ($env->settings->mysql as $key => $value)
            {
                $settings[$key] = $value;
            }
            return $settings;
        }
        catch (Exception $e)
        {
            return false;
        }
    }

}