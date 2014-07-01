<?php

/*
 * Error Handling Codes:
 * 
 * 200 OK                       This response code indicates that the request 
 *                              was successful.
 * 201 Created                  This indicates the request was successful and a 
 *                              resource was created. It is used to confirm 
 *                              success of a PUT or POST request.
 * 400 Bad Request              The request was malformed. This happens 
 *                              especially with POST and PUT requests, when the 
 *                              data does not pass validation, or is in the 
 *                              wrong format.
 * 404 Not Found                This response indicates that the required 
 *                              resource could not be found. This is generally 
 *                              returned to all requests which point to a URL 
 *                              with no corresponding resource.
 * 401 Unauthorized             This error indicates that you need to perform 
 *                              authentication before accessing the resource.
 * 405 Method                   Not Allowed The HTTP method used is not 
 *                              supported for this resource.
 * 409 Conflict                 This indicates a conflict. For instance, you 
 *                              are using a PUT request to create the same 
 *                              resource twice.
 * 500 Internal Server Error    When all else fails; generally, a 500 response 
 *                              is used when processing fails due to 
 *                              unanticipated circumstances on the server side, 
 *                              which causes the server to error out.
 */

class Api_Model_Response
{
    /*
     * contains the HTTP response status code as an integer.
     */

    public $code = '200';

    /*
     * contains the text: “success”, “fail”, or “error”. Where “fail” is for 
     * HTTP status response values from 500-599, “error” is for statuses 
     * 400-499, and “success” is for everything
     * else (e.g. 1XX, 2XX and 3XX responses).
     */
    public $status = 'success';

    /*
     * message – only used for “fail” and “error” statuses to contain the 
     * error message. For internationalization (i18n) purposes, this could 
     * contain a message number or code, either alone or contained within 
     * delimiters.
     */
    public static $format = 'json';
    private static $_instance;

    public static function instance()
    {
        if (self::$_instance == false)
        {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public static function setCode($code, $message = false)
    {
        $instance = self::instance();
        $instance->code = $code;
        if (!empty($message))
        {
            $instance->message = $message;
        }
        return self::$_instance;
    }

    private function __construct()
    {
        
    }

    public function render()
    {
        if ($this->code != 200 && $this->code != 201)
        {
            if (substr($this->code, 0, 1) == 5)
            {
                $this->status = 'failure';
            }
            if (substr($this->code, 0, 1) == 4)
            {
                $this->status = 'error';
            }
        }
        if (self::$format == 'json')
        {
            ob_start('ob_gzhandler');
            header('Content-type: application/json');
            echo json_encode($this);
        }
        elseif (self::$format == 'xml')
        {
            header("Content-Type: text/xml");
            echo $this->toXml($this)->asXML();
        }
        elseif (self::$format == 'csv')
        {
            $filename = "export";
            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename={$filename}.csv");
            header("Pragma: no-cache");
            header("Expires: 0");
            if (!isset($this->data))
            {
                $this->data = array();
            }
            if (!is_array($this->data))
            {
                $this->data = array($this->data);
            }
            foreach ($this->data as $row)
            {
                echo $this->_arrayToCsv($row)."\n";;
            }
        }
    }

    /*
     * XML Output
     */

    private function toXml($object)
    {
        $xmlResult = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><response></response>");
        $this->_iteratechildren($object, $xmlResult);
        return $xmlResult;
    }

    private function _iteratechildren($object, $xml)
    {
        if (is_object($object) || is_array($object))
        {
            foreach ($object as $name => $value)
            {
                if (is_string($value) || is_numeric($value) || is_bool($value))
                {
                    if (is_bool($value))
                    {
                        $xml->$name = ($value) ? 1 : 0;
                    }
                    else
                    {
                        $xml->$name = utf8_encode($value);
                    }
                }
                else
                {
                    $xml->$name = null;
                    $this->_iteratechildren($value, $xml->$name);
                }
            }
        }
    }

    /**
     * CSV Output 
     */
    private function _arrayToCsv(array &$fields, $delimiter = ',', $enclosure = '"', $encloseAll = false, $nullToMysqlNull = false)
    {
        $delimiter_esc = preg_quote($delimiter, '/');
        $enclosure_esc = preg_quote($enclosure, '/');

        $output = array();
        foreach ($fields as $field)
        {
            if ($field === null && $nullToMysqlNull)
            {
                $output[] = 'NULL';
                continue;
            }

            // Enclose fields containing $delimiter, $enclosure or whitespace
            if ($encloseAll || preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field))
            {
                $output[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
            }
            else
            {
                $output[] = $field;
            }
        }

        return implode($delimiter, $output);
    }

}