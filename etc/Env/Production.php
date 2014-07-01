<?php

class Env_Production extends Environment_Base
{

    public function setTimezone()
    {
        date_default_timezone_set('America/Los_Angeles');
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
            'database' => '',
            'host' => '127.0.0.1',
            'user' => '',
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