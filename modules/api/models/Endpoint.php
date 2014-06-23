<?php
/**
 * Abstract class for REST API Endpoints. This class is designated to act as a
 * model for controllers to interact.
 */
abstract class Api_Model_Endpoint
{
    public $param;
    public $get;
    public $post;
    
    public function authorize()
    {
        return true;
    }
}