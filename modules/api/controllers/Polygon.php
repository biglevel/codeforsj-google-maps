<?php

class Api_Controller_Polygon extends Api_Controller_Default
{
    public function index()
    {
        $this->_register(new Api_Model_Polygon());
    }

}