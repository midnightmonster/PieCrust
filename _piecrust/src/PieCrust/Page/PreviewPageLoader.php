<?php

namespace PieCrust\Page;

class PreviewPageLoader extends PieCrust\Page\PageLoader {
  protected function loadUnsafe(){
    $content = parent::loadUnsafe();
  }
}