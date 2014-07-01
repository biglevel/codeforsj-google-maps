<?php

class Env_Ckennedy extends Env_Production
{

    public function setPhpSettings()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', true);
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
    
}
