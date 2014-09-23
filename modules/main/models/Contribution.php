<?php

class Main_Model_Contribution
{

    public static function fetch($map_id)
    {
        $query = new Mysql_Query();
        $query->select("
             LPAD(`map_data`.`zip`,5,0) as `zip_code`,
            `map_data`.*
        ")
        ->from("`map_data`")
        ->where ("`map_data`.`map_id` = {$map_id}")
        ->order("`map_data`.`zip` desc");
        return $query->fetch();
    }
}
