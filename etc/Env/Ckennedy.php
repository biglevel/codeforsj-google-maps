<?php

class Env_Ckennedy extends Production
{

    public function setTimezone()
    {
        date_default_timezone_set('America/Los_Angeles');
    }

    public function setPhpSettings()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', true);
    }

    public function setAuthentication()
    {
        $this->_set('auth', array(
            'class'    => 'Main_Model_Authenticate',
            'username' => 'admin',
            'password' => 'admin'
        ));
    }

    public function setSite()
    {
        $this->_set('owner', array(
            'company' => '',
            'name'    => '',
            'email'   => ''
        ));
    }
    
    public function setDatabase()
    {
        $this->_set('mysql', array(
            'database' => 'codeforsj',
            'host' => //'127.0.0.1',
                array(
                    // MySQL Master (all writes should update on this server)
                    'mysql-a', // AWS Zone A
                    // MySQL Slave (most reads will query against this server(s))
                    'mysql-b' // AWS Zone B
                ),
            'user' => 'root',
            'pass' => '',
            'port' => '3306'
        ));
    }

    public function setSqlite()
    {
        $this->_set('sqlite', array(
            'location' => SOURCE . '/data/db/zip.db'
        ));
    }
    
}
