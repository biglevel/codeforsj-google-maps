<?php
/*
 * CREATE TABLE `map_data` (
  `map_id` int(11) unsigned NOT NULL,
  `zip_code` int(11) unsigned NOT NULL,
  `candidate` varchar(255) NOT NULL,
  `total` decimal(16,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`map_id`,`zip_code`,`candidate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 */
class Model_Map_Data extends Mysql_Table
{

    public $table = 'map_data';

    public function setup()
    {
        $this->set('map_id', 'integer', array(
            'primary'       => true
        ));

        $this->set('zip_code', 'integer', array(
            'maximum'  => 255,
            'required' => false
        ));
        $this->set('candidate', 'string', array(
            'maximum'  => 255,
            'required' => false
        ));
        $this->set('total', 'integer', array(
            'maximum'  => 255,
            'required' => true
        ));
    }

}