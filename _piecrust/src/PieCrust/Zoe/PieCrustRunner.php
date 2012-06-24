<?php

namespace PieCrust\Zoe;

use PieCrust\Zoe\PreviewPage as Page;
use PieCrust\Util\HttpHeaderHelper;
use PieCrust\Util\ServerHelper;

class PieCrustRunner extends \PieCrust\Runner\PieCrustRunner {
  protected $pageClass;
  protected $rendererClass;

  public function runUnsafe($uri = null, array $server = null, $extraPageData = null, array &$headers = null) {
    $this->pageClass = 'PieCrust\Zoe\PreviewPage';
    $this->rendererClass = 'PieCrust\Page\PageRenderer';
    return $this->runUnsafeWithClasses($uri,$server,$extraPageData,$headers);
  }
  public function runPageData($uri = null, array $server = null, $extraPageData = null, array &$headers = null) {
    $this->pageClass = 'PieCrust\Page\Page';
    $this->rendererClass = 'PieCrust\Zoe\PageDataRenderer';
    return $this->runUnsafeWithClasses($uri,$server,$extraPageData,$headers);
  }
  public function runUnsafeWithClasses($uri = null, array $server = null, $extraPageData = null, array &$headers = null) {
    $pageClass = $this->pageClass;
    $rendererClass = $this->rendererClass;
    // Remember the time.
    $this->lastRunInfo = array('start_time' => microtime(true));
    
    // No caching
    $this->lastRunInfo['cache_validity'] = null;

    // Store the execution info in the environment.
    $this->pieCrust->getEnvironment()->setLastRunInfo($this->lastRunInfo);

    // Get the resource URI and corresponding physical path.
    if ($server == null) $server = $_SERVER;
    if ($uri == null) $uri = ServerHelper::getRequestUri($server, $this->pieCrust->getConfig()->getValueUnchecked('site/pretty_urls'));

    // Do the heavy lifting.
    $page = $pageClass::createFromUri($this->pieCrust, $uri, false);
    if ($extraPageData != null) $page->setExtraPageData($extraPageData);
    $pageRenderer = new $rendererClass($page, $this->lastRunInfo);
    $output = $pageRenderer->get();
    
    // Set or return the HTML headers.
    HttpHeaderHelper::setOrAddHeaders($rendererClass::getHeaders($page->getConfig()->getValue('content_type'), $server), $headers);
    
    // No caching!
    HttpHeaderHelper::setOrAddHeader('Cache-Control', 'no-cache, must-revalidate', $headers);
    
    // Output with or without GZip compression.
    $gzipEnabled = (($this->pieCrust->getConfig()->getValueUnchecked('site/enable_gzip') === true) and
                    (strpos($server['HTTP_ACCEPT_ENCODING'], 'gzip') !== false));
    if ($gzipEnabled && ($zippedOutput = gzencode($output))) {
      HttpHeaderHelper::setOrAddHeader('Content-Encoding', 'gzip', $headers);
      HttpHeaderHelper::setOrAddHeader('Content-Length', strlen($zippedOutput), $headers);
      echo $zippedOutput;
    } else {
      HttpHeaderHelper::setOrAddHeader('Content-Length', strlen($output), $headers);
      echo $output;
    }
  }
}