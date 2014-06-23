<?php
/**
 * <h3>Description</h3>
 * <p>
 *      Get list of polygons for map_id.
 * </p>
 * 
 * @package    Polygon_Model
 * @endpoint   /api/polygon
 */
class Api_Model_Polygon extends Api_Model_Authorization
{
    
    /**
     * GET list of Polygons by map_id
     * 
     * @get map_id|integer|Identification number for map being viewed
     * @get limit|integer|Result lmiit (Default: 100)
     * @get last_id|integer|Where `domain_id` is greated than `last_id`
     * @return mixed
     */
    public function action_get()
    {
        if (empty($this->get['map_id']))
        {
            throw new Exception("Map_id was not provided.", 400);
        }
        if (!is_numeric($this->get['map_id']))
        {
            throw new Exception("Invalid map_id.", 400);
        }

        return Main_Model_Zip::fetch($this->get['map_id']);
    }
    
}