<?php

class Main_Controller_Error extends Controller
{

    public function __construct()
    {
        
    }

    public function index()
    {
        
    }

    public function notfound()
    {
        $this->setLayout('404');
        header("HTTP/1.0 404 Not Found");
    }

}