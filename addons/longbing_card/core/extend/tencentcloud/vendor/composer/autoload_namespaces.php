<?php  $vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);
return array( "Qcloud\\Cos\\" => array( $baseDir . "/src" ), "Guzzle\\Tests" => array( $vendorDir . "/guzzle/guzzle/tests" ), "Guzzle" => array( $vendorDir . "/guzzle/guzzle/src" ) );
?>