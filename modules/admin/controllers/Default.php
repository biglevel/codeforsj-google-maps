<?php

class Admin_Controller_Default extends Controller
{
    public $maps;
    public $map_id;
    public $form;

    public function bootstrap()
    {
        
    }

    public function index()
    {
        $this->maps = Main_Model_Map::fetch();
    }

    public function map()
    {
        $this->map_id = $this->get->value('map_id');

        if (is_numeric($this->map_id))
        {
            Admin_Form_Map::$include_data_manipulation = true;
        }

        $this->form = new Admin_Form_Map();

        // Load form with defaults
        if ($this->isPost() == false)
        {
            if ($this->map_id == false)
            {
                $this->form->data = array(
                    'type'             => 'ROADMAP',
                    'center_latitude'  => '37.3393900',
                    'center_longitude' => '-121.8949600',
                    'center_zoom'      => 10,
                    'colors'           => $this->_defaultColors(),
                    'delimiter'        => ','
                );
            }
            else
            {
                $record = Main_Model_Map::fetch($this->map_id);
                if (!isset($record->name))
                {
                    $this->redirect("/admin");
                }
                $this->form->data = array(
                    'name'             => $record->name,
                    'type'             => $record->type,
                    'center_latitude'  => $record->center_latitude,
                    'center_longitude' => $record->center_longitude,
                    'center_zoom'      => $record->center_zoom,
                    'colors'           => $record->colors,
                    'delimiter'        => ','
                );
            }
        }
        // Process form
        if ($this->isPost())
        {
            $data = $this->post->values();
            $this->form->data = $data;
            if ($this->form->validate())
            {
                if ($this->map_id != false)
                {
                    Main_Model_Map::update($this->map_id, $data);
                }
                else
                {
                    $model = new Model_Map();
                    foreach ($data as $key => $value)
                    {
                        if ($model->exists($key))
                        {
                            $model->$key = $value;
                        }
                    }
                    $model->save();
                }
                $this->redirect("/admin");
            }
        }
    }

    private function _defaultColors()
    {
        return <<<EOF
#ffffb2
#fed976
#feb24c
#fd8d3c
#fc4e2a
#e31a1c
#b10026
EOF;

    }

}
