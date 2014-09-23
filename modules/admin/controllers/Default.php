<?php

class Admin_Controller_Default extends Controller
{
    public $maps;
    public $map_id;
    public $form;
    protected $_writes = 0;
    protected $_files = array();

    public function bootstrap()
    {
        
    }

    public function index()
    {
        $this->maps = Main_Model_Map::fetch();
    }

    public function candidates() {

    }

    public function download() {
        $this->map_id = $this->get->value('map_id');
        if (!is_numeric($this->map_id)) {
            die("No map could be found by that ID.");
        }
        $source = SOURCE."/data/shapes/{$this->map_id}";
        if (!file_exists($source)) {
            mkdir($source, 0777, true);
        }
        else {
            $files = glob("{$source}/*"); // get all file names
            foreach($files as $file){ // iterate files
                if(is_file($file)){
                    unlink($file); // delete file
                }
            }
        }
        // PUll data from database
        $data = Main_Model_Zip::shapes($this->map_id);
        if (count($data)==0) {
            die("No data could be found for this map_id.");
        }

        // Loop through and write json files that have zip code shapes
        $buffer = array();
        foreach($data as $row) {
            array_push($buffer, $row);
            if (count($buffer)>=50) {
                $this->_writeCache($source, $buffer);
                $buffer = array();
            }
        }
        if (count($buffer)>0) {
            $this->_writeCache($source, $buffer);
        }

        // Archive shape json files into zip format.
        $zip = new ZipArchive;
        $archive_file = "shapes{$this->map_id}.zip";
        $archive = "{$source}/{$archive_file}";
        if ($zip->open($archive, ZipArchive::CREATE) === TRUE) {
            foreach($this->_files as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
        } else {
            die("Failed to zip archive... :(");
        }
        // Clean up json files since they'll be archived
        foreach($this->_files as $file) {
            unlink($file); // delete file
        }

        // Prepare download
        $fp = fopen($archive, 'rb');
        // send the right headers
        header("Content-Type: application/zip");
        header("Content-Disposition: attachment; filename={$archive_file}");
        header("Pragma: no-cache");
        header("Content-Length: " . filesize($archive));
        // dump the picture and stop the script
        fpassthru($fp);
        die();
    }

    protected function _writeCache($save_path, $data) {
        $file = "{$save_path}/zips_{$this->_writes}.json";
        file_put_contents($file, json_encode($data));
        array_push($this->_files, $file);
        $this->_writes++;
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
