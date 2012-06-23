<?php

namespace PieCrust\Zoe;

use PieCrust\Zoe\PreviewPage as Page;
use PieCrust\Page\PageRenderer;
use PieCrust\Util\HttpHeaderHelper;
use PieCrust\Util\ServerHelper;

class PieCrustRunner extends \PieCrust\Runner\PieCrustRunner {
  public function runUnsafe($uri = null, array $server = null, $extraPageData = null, array &$headers = null) {
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
    $page = Page::createFromUri($this->pieCrust, $uri, false);
    if ($extraPageData != null) $page->setExtraPageData($extraPageData);
    $pageRenderer = new PageRenderer($page, $this->lastRunInfo);
    $output = $pageRenderer->get();
    
    // Set or return the HTML headers.
    HttpHeaderHelper::setOrAddHeaders(PageRenderer::getHeaders($page->getConfig()->getValue('content_type'), $server), $headers);
    
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