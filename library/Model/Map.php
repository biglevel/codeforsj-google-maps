<?php
/*
 CREATE TABLE `map` (
  `map_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `type` varchar(24) NOT NULL DEFAULT 'ROADMAP',
  `center_latitude` decimal(7,7) NOT NULL DEFAULT '0.0000000',
  `center_longitude` decimal(7,7) NOT NULL DEFAULT '0.0000000',
  `center_zoom` tinyint(4) NOT NULL DEFAULT '10',
  PRIMARY KEY (`map_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 */
class Model_Map extends Mysql_Table
{

    public $table = 'map';

    public function setup()
    {
        $this->set('map_id', 'integer', array(
            'primary'       => true,
            'autoincrement' => true
        ));

        $this->set('name', 'string', array(
            'required' => false
        ));
        $this->set('type', 'string', array(
            'required' => false
        ));
        $this->set('center_latitude', 'integer', array(
            'required' => true
        ));
        $this->set('center_longitude', 'integer', array(
            'required' => true
        ));
        $this->set('center_zoom', 'integer', array(
            'required' => true
        ));
        $this->set('colors', 'string', array(
            'required' => true,
            'default' => ''
        ));
    }

}