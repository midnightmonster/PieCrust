<?php
require '../_piecrust/version_check.php';
require '../_piecrust/piecrust.php';

piecrust_setup('web');
$parameters = PieCrust\Runner\PieCrustRunner::getPieCrustParameters(array());
$pieCrust = new PieCrust\PieCrust($parameters);
$uri = PieCrust\Util\ServerHelper::getRequestUri($_SERVER, $pieCrust->getConfig()->getValueUnchecked('site/pretty_urls'));
$page = PieCrust\Page\Page::createFromUri($pieCrust, $uri);
$data = PieCrust\Data\DataBuilder::getPageRenderingData($page);
$templateEngineName = $page->getConfig()->getValue('template_engine');
$templateEngine = PieCrust\Util\PieCrustHelper::getTemplateEngine($pieCrust, $templateEngineName);
if (!$templateEngine) {
  throw new PieCrust\PieCrustException("Unknown template engine '{$templateEngineName}'.");
}

$pieces = json_decode(file_get_contents('php://input'),true);

$out = '';
foreach ($pieces as $piece)
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
    $renderedAndFormattedContent = PieCrust\Util\PieCrustHelper::formatText(
        $pieCrust, 
        $renderedContent, 
        $format
    );
    $out .= $renderedAndFormattedContent;
}
print $out;