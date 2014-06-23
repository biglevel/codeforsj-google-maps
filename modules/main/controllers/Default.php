<?php

class Main_Controller_Default extends Controller
{
    public $map_id;
    public $map;
    
    public function bootstrap()
    {
        $this->map_id = $this->get->value('map_id');
    }
    
    public function index()
    {
        if (empty($this->map_id))
        {
            $this->redirect("/error/notfound");
        }
        $this->map = Main_Model_Map::fetch($this->map_id);
        if (!isset($this->map->map_id))
        {
            $this->redirect("/error/notfound");
        }
        
    }
    
}