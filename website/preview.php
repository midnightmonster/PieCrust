<?php
require '../_piecrust/version_check.php';
require '../_piecrust/piecrust.php';
piecrust_setup('web');
$parameters = PieCrust\Runner\PieCrustRunner::getPieCrustParameters(array());
$pieCrust = new PieCrust\PieCrust($parameters);
$runner = new PieCrust\Zoe\PieCrustRunner($pieCrust);
$runner->run(null);