<?php

class Env_Production extends Environment_Base
{

    public function setTimezone()
    {
        date_default_timezone_set('America/Los_Angeles');
    }

    public function setPhpSettings()
    {
        ini_set('display_errors', 'On');
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
            'company' => 'Your Company Name',
            'name'    => 'Your Email',
            'email'   => 'email@domain.com'
        ));
    }

    public function setSqlite()
    {
        $this->_set('sqlite', array(
            'location' => SOURCE . '/data/db/campaigns.db'
        ));
    }

    public function setCampaign()
    {
        $this->_set('campaign', array(
            'api_host' => 'login.envyusmedia.com'
        ));
    }

}