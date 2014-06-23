<?php

class Main_Form_Authenticate extends Form
{

    public function setup()
    {
        $this->create('username', 'text', array(
            'label'    => 'Username',
            'required' => true
        ));
        $this->create('password', 'password', array(
            'label'    => 'Password',
            'required' => true
        ));
    }

}
