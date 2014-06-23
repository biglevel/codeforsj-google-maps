<?php

class Main_Model_Authenticate extends Authentication
{

    public function login($username = false, $password = false, $extras = false)
    {
        $this->loadAdminDefaults();
        return parent::login($username, $password);
    }

    public static function filterByHost()
    {
        $instance = self::instance();
        if ($instance->auth->host == HOST)
        {
            return true;
        }
        return false;
    }

}