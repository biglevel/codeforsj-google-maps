<?php

class Main_Controller_Default extends Controller
{
    public $map_id;
    public $map;
    public $max_contribution = 0;
    public $min_contribution = 0;
    public $total_contributions = array();
    public $contributions_by_candidate = array();
    public $tier_colors = array();
    
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
        $data = Main_Model_Contribution::fetch($this->map_id);
        while (($row = array_shift($data)) == true)
        {
            // set lowest contribution from candidates
            if ($row->total > 0) {
                if ($this->min_contribution == 0 || ($row->total < $this->min_contribution)) {
                    $this->min_contribution = $row->total;
                }
                // set high contribution from candidates
                if ($row->total > $this->max_contribution) {
                    $this->max_contribution = $row->total;
                }
            }


            // aggregate totals by candidate and location
            $this->_totalContributions($row->candidate, $row->zip_code, $row->total);
            $this->_contributionsByCandidate($row->candidate, $row->zip_code, $row->total);
        }
        $this->_scaleColors();
    }

    protected function _scaleColors()
    {

        $color_map = explode("\n", $this->map->colors);
        while(($value = array_shift($color_map)) == true) {
            if (!empty($value)) {
                array_push($this->tier_colors, array(
                    'color' => trim($value),
                    'min_val' => 0,
                    'max_val' => 0
                ));
            }
        }
        unset($color_map);
        $tiers = ceil(($this->max_contribution+1)/count($this->tier_colors));
        foreach($this->tier_colors as $i => &$tier) {
            $tier['min_val'] = ($i*$tiers);
            $tier['max_val'] = (($i*$tiers) + $tiers)-1;
        }
    }

    protected function _totalContributions($candidate, $zip_code, $total)
    {
        if (!isset($this->total_contributions[$zip_code]))
        {
            $this->total_contributions[$zip_code] = array();
        }
        if (!isset($this->total_contributions[$zip_code][$candidate]))
        {
            $this->total_contributions[$zip_code][$candidate] = 0;
        }
        $this->total_contributions[$zip_code][$candidate] += $total;
    }

    protected function _contributionsByCandidate($candidate, $zip_code, $total)
    {
        if (!isset($this->total_contributions[$candidate]))
        {
            $this->total_contributions[$candidate] = array();
        }
        if (!isset($this->total_contributions[$candidate][$zip_code]))
        {
            $this->total_contributions[$candidate][$zip_code] = 0;
        }
        $this->total_contributions[$candidate][$zip_code] += $total;
    }
    
}