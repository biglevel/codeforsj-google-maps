<?php

class Admin_Form_Map extends Form
{
    public static $include_data_manipulation = false;

    public function setup()
    {
        $this->create('name', 'text', array(
            'label'      => 'Name',
            'required'   => true,
            'attributes' => array(
                'style' => 'width:250px;'
            )
        ));
        $this->create('type', 'select', array(
            'label'    => 'Map Type',
            'required' => true,
            'options'  => array(
                'HYBRID'    => 'HYBRID',
                'ROADMAP'   => 'ROADMAP',
                'SATELLITE' => 'SATELLITE',
                'TERRAIN'   => 'TERRAIN˝',
            )
        ));
        $this->create('center_latitude', 'text', array(
            'label'      => 'Center Latitude',
            'required'   => true,
            'attributes' => array(
                'style' => 'width:250px;'
            )
        ));
        $this->create('center_longitude', 'text', array(
            'label'      => 'Center Longitude',
            'required'   => true,
            'attributes' => array(
                'style' => 'width:250px;'
            )
        ));
        $this->create('center_zoom', 'select', array(
            'label'    => 'Center Zoom',
            'required' => true,
            'options'  => array(
                3  => '3',
                4  => '4',
                5  => '5',
                6  => '6',
                7  => '7',
                8  => '8',
                9  => '9',
                10 => '10',
                11 => '11',
                12 => '12'
            )
        ));
        $this->create('colors','textarea',array(
            'label' => 'Color Palette',
            'required' => false,
            'attributes' => array(
                'style' => 'height: 100px; width: 300px;'
            )
        ));

        if (self::$include_data_manipulation == true)
        {
            $this->_dataManipulation();
        }
    }

    protected function _dataManipulation()
    {
        $this->create('delimiter', 'text', array(
            'label'      => 'Delimiter',
            'required'   => false,
            'attributes' => array(
                'style' => 'width:100px;'
            )
        ));
        $this->create('contributions', 'textarea', array(
            'label'      => 'Contributions',
            'required'   => false,
            'attributes' => array(
                'style' => 'height: 300px; width: 400px;'
            )
        ));
    }

}
