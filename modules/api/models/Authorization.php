<?php

/**
 * ScrubKit specific items should be added here.
 * - authorization
 * - 
 */
class Api_Model_Authorization extends Api_Model_Endpoint
{
    /*
     * Write authorization in here at some point.. right now rely on session.
     */
    public function authorize()
    {
        return true;
    }
}