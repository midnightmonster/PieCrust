<?php

namespace PieCrust\Zoe;
use PieCrust\IPage;
use PieCrust\Page\PageConfiguration;

class PreviewPage extends \PieCrust\Page\Page implements IPage {
  protected $previewData = null;
  public function wasCached(){ return false; }
  protected function loadPreviewData($data=null){
    if(null===$data) {
      if(null!==$this->previewData) return $this->previewData;
      $data = json_decode(file_get_contents('php://input'),true);
    }
    return $this->previewData = $data;
  }
  protected function ensureConfigLoaded(){
    $this->loadPreviewData();
    if($this->config == null) {
      $this->config = new PageConfiguration($this, array(), false);
      $this->config->set(empty($this->previewData['page']) ? array() : $this->previewData['page']);
      $this->contents = empty($this->previewData['segments']) ? array() : $this->previewData['segments'];
      foreach ($this->contents as $key => $segment) {
        $this->config->appendValue('segments',$key);
      }
      $this->didFormatContents = false;
    }
    return $this->config;
  }
}