<?php

abstract class Controller extends Template
{

    public $get;
    public $post;
    public $session;
    public $server;
    public $files;
    public $auth;
    public $authentication;
    public $authenticated = false;
    public $ajax = false;
    public $env;

    /**
     * 
     */
    public function __construct()
    {
        $this->env = Environment::instance();
    }

    /**
     * The intended purpose of this method is to pull in all of common php 
     * globals that are accessed (GET, POST, SERVER, SESSION, FILES).  The global
     * variables are contained in the Controller_Globals class.
     * 
     */
    public function collectGlobals()
    {
        $this->get = Controller_Globals::instance('get');
        $this->post = Controller_Globals::instance('post');
        $this->server = Controller_Globals::instance('server');
        $this->session = Controller_Globals::instance('session');
        $this->files = Controller_Globals::instance('files');
    }

    /**
     * Defined action for initialization of controllers.  This method will be
     * called before any action is called upon.  It is safer to use this instead
     * of the built in php __construct magic method. 
     */
    public function bootstrap()
    {
        
    }

    /**
     * Defined action for initialization of controllers.  This method will be
     * called after any action is called upon.  A better option for using the
     * built in __destruct()
     */
    public function destruct()
    {
        
    }

    public function run($action = false)
    {
        if ($action !== false && $action !== 'bootstrap')
        {
            $this->action = $action;
        }
        $this->confirmAuthorization();
        $this->_detecthRequestType();
        $this->_isActionSet();
        $this->_actionExists();
        $this->_timezoneAdjustment();
        $this->bootstrap();
        call_user_func(array($this, $this->action));
        $this->destruct();
    }

    public function flash($message, $page_loads)
    {
        
    }

    public function execute($uri, $params = array())
    {
        $site = new Site(SOURCE . '/layouts', SOURCE . '/modules', true);
        $site->route($uri);
        return $site->execute($params);
    }

    public function redirect($url)
    {
        if (substr($url, 0, 4)=='http')
        {
            header("Location: {$url}");
            exit();
        }
        $base = $this->base();
        if (substr($url, 0, strlen($base)) == $base)
        {
            $redirect = $url;
        }
        else
        {
            $redirect = str_replace('//', '/', "{$base}{$url}");
        }
        header("Location: {$redirect}");
        exit();
    }

    public function base()
    {
        $base = trim(SITE_BASE);
        $base = trim($base, '/');
        return (!empty($base)) ? "/{$base}/" : "/";
    }

    public function metaBase()
    {
        return sprintf('http%s://%s%s', ($this->server->value('HTTPS') !== false || $this->server->value('SERVER_PORT') == '443') ? 's' : '', $this->server->value('HTTP_HOST'), $this->base()
        );
    }

    public function isPost()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            return true;
        }
        return false;
    }

    public function authenticate($username, $password, $extras = false)
    {
        $this->_fetchAuthSettings();
        $auth = $this->authentication->login($username, $password, $extras);
        if ($auth !== false)
        {
            $this->authenticated = true;
            $this->session->set('user_id', $username);
            return $auth;
        }
        return false;
    }

    public function confirmAuthorization()
    {
        $user_id = $this->session->value('user_id');
        if (!empty($user_id))
        {
            $this->authenticated = true;
        }
    }

    public function lock()
    {
        $this->confirmAuthorization();
        if ($this->authenticated == false)
        {
            // set URL to return to after successful authentication
            $return_url = $this->server->value('REQUEST_URI');
            $this->session->set('return_url', $return_url);
            // redirect to auth module
            $this->redirect('/authenticate/login');
        }
    }

    protected function _timezoneAdjustment()
    {
        $timezone = $this->session->value('timezone');
        if (!empty($timezone))
        {
            date_default_timezone_set($timezone);
        }
    }

    protected function _fetchAuthSettings()
    {
        $env = Environment::instance();
        $this->auth_class = (isset($env->settings->auth->class)) ? $env->settings->auth->class : 'Authentication';
        $this->_checkAuthModule($this->auth_class);
    }

    protected function _checkAuthModule($object)
    {
        if (!class_exists($object))
        {
            throw new Exception("Object provided ('" . $object . "') for the authentication model does not exist.");
        }
        $this->authentication = new $object;
        if (!($this->authentication instanceof Authentication))
        {
            throw new Exception("The authentication object provided is not an instance of the Authentication class.");
        }
    }

    protected function _detecthRequestType()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'))
        {
            $this->ajax = true;
        }
    }

    protected function _isActionSet()
    {
        if (!isset($this->action))
        {
            throw new Exception("Action was not provided in controller");
        }
    }

    protected function _actionExists()
    {
        if (method_exists($this, $this->action) === false)
        {
            throw new Exception("The action '" . $this->action . "' does not exist in controller '" . $this->controller . "' from module '" . $this->module . "'");
        }
    }

}