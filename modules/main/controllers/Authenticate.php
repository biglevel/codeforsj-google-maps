<?php

class Main_Controller_Authenticate extends Controller
{

    public $authenticated = false;

    public function bootstrap()
    {
        $this->setLayout('auth');
    }

    public function index()
    {
        $this->redirect('/authenticate/login');
    }

    public function bookmark()
    {
        if ($this->authenticated == true)
        {
            $this->session->set('bookmark', true);
            $this->redirect(sprintf(
                            '/authenticate/login?username=%s&password=%s', rawurlencode($this->session->value('username')), rawurlencode($this->session->value('password'))
                    ));
        }
        $this->redirect('/authenticate/login');
    }

    public function login()
    {
        // check to see if already auth'd
        $this->book = $this->session->value('bookmark');
        if ($this->authenticated == true && $this->book == false)
        {
            $this->redirect("/admin");
        }
        // authenticate
        $this->form = new Main_Form_Authenticate;
        $post = array(
            'username' => $this->_request('username'),
            'password' => $this->_request('password')
        );
        if ($this->isPost() || (!empty($post['username']) && (!empty($post['password']) && $this->book == false)))
        {
            $this->form->data = $post;
            if ($this->form->validate())
            {
                if ($this->authenticate($post['username'], $post['password'], false))
                {
                    $this->session->set('username', $post['username']);
                    $this->session->set('user_id', $post['username']);
                    $this->session->set('password', $post['password']);
                    $this->redirect('/admin');
                }
                else
                {
                    $this->form->addError('username', 'The username and/or password is incorrect.');
                }
            }
        }
        if ($this->book == true)
        {
            unset($_SESSION['bookmark']);
        }
    }

    protected function _request($var)
    {
        $get = $this->get->value($var);
        if ($get != false)
        {
            return $get;
        }
        return $this->post->value($var);
    }

    public function logout()
    {
        session_destroy();
    }

}