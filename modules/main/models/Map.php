<?php

class Main_Model_Map
{
    public static function fetch($map_id = false)
    {
        if ($map_id !== false)
        {
            if (!is_array($map_id))
            {
                $map_id = array($map_id);
            }
        }
        $query = new Mysql_Query();
        $query->select("*")
        ->from("`map`");
        if ($map_id !== false)
        {
            $query->in('`map_id`', $map_id);
            if (count($map_id) == 1)
            {
                return $query->fetchOne();
            }
        }
        return $query->fetch();
    }
    
    /*
     * CREATE TABLE `map` (
        `map_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(64) NOT NULL DEFAULT '',
        `type` varchar(24) NOT NULL DEFAULT 'ROADMAP',
        `center_latitude` decimal(16,7) NOT NULL DEFAULT '0.0000000',
        `center_longitude` decimal(16,7) NOT NULL DEFAULT '0.0000000',
        `center_zoom` tinyint(4) NOT NULL DEFAULT '10',
        PRIMARY KEY (`map_id`)
      ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
     */
    public static function update($map_id, $data)
    {
        $model = new Model_Map();
        if (!is_numeric($map_id))
        {
            return false;
        }
        $sql = "
            update `map`
            set %s
            where `map_id` = {$map_id}

        ";
        $set = array();
        $values = array();
        foreach ($data as $key => $value)
        {
            if ($model->exists($key))
            {
                array_push($set, "`{$key}` = ?");
                array_push($values, $value);
            }
        }
        if (count($set)>0)
        {
            Mysql::instance()->execute(sprintf($sql, implode(",\n", $set)), $values);
        }

        if (!empty($data['delimiter']) && !empty($data['contributions']))
        {
            $data['contributions'] = str_replace("\r", "", $data['contributions']);
            $rows = explode("\n", $data['contributions']);

            // Purge previous map data
            Mysql::instance()->execute("delete from `map_data` where `map_id` = {$map_id}");

            // Update map data
            $map_data = new Mysql_Bulk("insert into `map_data` values %s on duplicate key update `total` = values (`total`)");
            foreach ($rows as $row)
            {
                if (empty($row))
                {
                    continue;
                }
                $columns = explode($data['delimiter'], $row);
                if (count($columns)!=3)
                {
                    continue;
                }
                list($zip, $contribution, $candidate) = $columns;
                if (!is_numeric($zip))
                {
                    continue;
                }
                if (!is_numeric($contribution))
                {
                    continue;
                }
                $map_data->add(array($map_id, $zip, "'{$candidate}'", $contribution));

            }
            $map_data->save();
        }
        return true;
    }
}