<?php
require '../_piecrust/version_check.php';
require '../_piecrust/piecrust.php';
piecrust_setup('web');
$parameters = PieCrust\Runner\PieCrustRunner::getPieCrustParameters(array());
$pieCrust = new PieCrust\PieCrust($parameters);

switch($_SERVER['REQUEST_METHOD']){
  case 'RENDER':
    $renderer = new PieCrust\Zoe\SegmentRenderer($pieCrust);
    $renderer->run();
    break;
  case 'PAGEDATA':
    $runner = new PieCrust\Zoe\PieCrustRunner($pieCrust);
    $runner->runPageData();
    break;
  case 'PREVIEW':
    $runner = new PieCrust\Zoe\PieCrustRunner($pieCrust);
    $runner->run(null);
    break;
  case 'PUT':
    $writer = new PieCrust\Zoe\PageWriter($pieCrust);
    $writer->write();
    break;
}
