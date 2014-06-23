<?php
class Custom_Controllers_Ajax extends Controller
{
  public $success   = true;
  public $errors    = array();
  public $response  = '';
  
  protected function _output()
  {
    header('Cache-Control: no-cache, must-revalidate');
    header('Content-type: application/json');
    die(json_encode(
      array(
        'success' => $this->success,
        'errors' => $this->errors,
        'response' => $this->response
      )
    ));
  }
}

