<?php
class Viewer
{
  public $referrer;
  public $geoip;
  public $agent;

  public function referrer($referrer = false)
  {
    $this->referrer  = new Viewer_Referrer();
    return $this->referrer->parse($referrer);
  }

  public function browser($ua = false)
  {
    $this->agent = new Viewer_Browser();
    return $this->agent->parse($ua);
  }

  public function geoip($ip = false)
  {
    $this->geoip = new Viewer_Geoip();
    return $this->geoip->parse($ip);
  }

}