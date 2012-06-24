<?php

namespace PieCrust\Zoe;

class PageDataRenderer extends \PieCrust\Page\PageRenderer {
  public function render(){
    $page = $this->page->getConfig()->get();
    $contents = $this->page->getContentSegments(false);
    $data = array('page'=>$page,'segments'=>$contents);
    die(json_encode($data));
  }
}