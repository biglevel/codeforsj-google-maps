<?php

class Main_Model_Zip
{
    
    public static function fetch($map_id, $offset, $limit)
    {
        $query = new Mysql_Query();
        $query->select("
             LPAD(`map_data`.`zip_code`,5,0) as `zip_code`,
            sum(`map_data`.`total`) as `total`,
            `shapes`.`geoid10` as `geo_id`,
            AsText(`shapes`.`SHAPE`) as `shape`
        ")
        ->from("`map_data`")
        ->leftJoin("`shapes`", "on (`shapes`.`zcta5ce10` = LPAD(`map_data`.`zip_code`,5,0))")
        ->where ("`map_data`.`map_id` = {$map_id}")
        ->group("`map_data`.`zip_code`")
        ->order("`map_data`.`zip_code` desc")
        ->offset($offset)
        ->limit($limit);
        $results = $query->fetch();
        foreach ($results as &$row)
        {
            $row->total = number_format($row->total, 2);
            $row->shape = self::parsePolygon($row->shape);
        }
        return $results;
    }
    
    public static function parsePolygon($shape)
    {
        $shape = str_replace(array("MULTIPOLYGON(((", ")))"), "", $shape);
        $entries = explode(",", $shape);
        $points = array();
        foreach ($entries as $point)
        {
            if (strstr($point, " "))
            {
                list($longitude, $latitude) = explode(" ", $point);
                $point = new stdClass();
                $point->long = $longitude;
                $point->lat = $latitude;
                array_push($points, $point);
            }
        }
        gc_collect_cycles();
        return $points;
    }
    
    protected static function _toArray($input)
    {
        $input = trim($input);
        $input = str_replace(array(",", "\r"), "", $input);
        return explode("\n", $input);
    }

}
