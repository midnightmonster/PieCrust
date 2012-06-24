<?php

namespace PieCrust\Zoe;

class SegmentRenderer {
  protected $segment;
  public function __construct($pieCrust,$segment=null){
    $this->pieCrust = $pieCrust;
    $this->segment = $segment===null ? json_decode(file_get_contents('php://input'),true) : $segment;
  }
  public function run(){
    $uri = \PieCrust\Util\ServerHelper::getRequestUri($_SERVER, $this->pieCrust->getConfig()->getValueUnchecked('site/pretty_urls'));
    $page = \PieCrust\Page\Page::createFromUri($this->pieCrust, $uri);
    $data = \PieCrust\Data\DataBuilder::getPageRenderingData($page);
    $templateEngineName = $page->getConfig()->getValue('template_engine');
    $templateEngine = \PieCrust\Util\PieCrustHelper::getTemplateEngine($this->pieCrust, $templateEngineName);
    if (!$templateEngine) {
      throw new \PieCrust\PieCrustException("Unknown template engine '{$templateEngineName}'.");
    }
    $out = '';
    foreach ($this->segment as $piece)
    {
      $content = $piece['content'];
      $format = empty($piece['format']) ? null : $piece['format'];
      ob_start();
      try
      {
        $templateEngine->renderString($content, $data);
        $renderedContent = ob_get_clean();
      }
      catch (Exception $e)
      {
        ob_end_clean();
        throw $e;
      }

      if(!$format) $format = $page->getConfig()->getValue('format');
      $renderedAndFormattedContent = \PieCrust\Util\PieCrustHelper::formatText(
        $this->pieCrust, 
        $renderedContent, 
        $format
      );
      $out .= $renderedAndFormattedContent;
    }
    print $out;
  }
}