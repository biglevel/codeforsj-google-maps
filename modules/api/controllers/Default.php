<?php

class Api_Controller_Default extends Controller
{

    public $allowed_methods = array('get', 'put', 'post', 'delete');
    public $response;
    private $_model;

    /**
     * Make sure everything is setup prior to running controller action. 
     * Important for making sure classes and objects are prepared prior to run-
     * time.
     */
    public function bootstrap()
    {
        parent::bootstrap();
        //$this->lock();
        $this->api = Api_Model_Response::instance();
        $format = $this->get->value('format');
        if (!empty($format))
        {
            Api_Model_Response::$format = $format;
        }
    }

    /**
     * After controller action makes definitions, the actual model will be 
     * called automatically to process action and render API output.
     */
    public function destruct()
    {
        parent::destruct();
        $this->_run();
        // avoid template errors
        die();
    }

    /**
     * Required method to be called to use REST controller. This enables this
     * class to know which API model we're workin with.
     */
    protected function _register($api)
    {
        $this->_model = $api;
    }

    /**
     * Run method acts as a route between the controller and registered api 
     * models.
     * @throws Exception
     */
    protected function _run()
    {
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        if (!in_array($method, $this->allowed_methods))
        {
            throw new Exception("Request method provided is not understood.", 400);
        }
        if ($this->_model == false)
        {
            throw new Exception("Object handler has not been registered for this endpoint.", 500);
        }
        if (!method_exists($this->_model, "action_" . $method))
        {
            throw new Exception("{$_SERVER['REQUEST_METHOD']} does not exist for this endpoint.", 404);
        }
        $this->_model->get = $this->get->values();
        $this->_model->param = array();
        $this->_model->post = $this->post->values();
        if (in_array($method, array('put', 'post')))
        {
            $this->api->setCode(201);
        }
        /***************************************************************************************************
         * 
         * Validation needs to be completed.
         * - validate authorization
         *   - make re-usable
         *   - maybe extend / overlap classes to create a man in the middle idea
         *   - 
         * - validate params required in model
         *   - get paramters need to be provided and validated
         *   - post paramters need to be provided and validated
         * 
         * - error output
         *   - need to include validation errors
         *   - codes need to be determined (use http errors)
         * 
         ****************************************************************************************************
         */

        /**
         * Handle authorization
         */
        if (method_exists($this->_model, "authorize"))
        {
            $success = call_user_func(array($this->_model, "authorize"));
            if ($success == false)
            {
                throw new Exception("Authorization failure", 401);
            }
        }

        /*
         * Execution time!
         * Any data being returned my method will be added to the data 
         * parameter for the response.
         */
        $this->api->data = call_user_func(array($this->_model, "action_" . $method));
        /*
         * Render the output!
         */
        $this->api->render();
    }

}