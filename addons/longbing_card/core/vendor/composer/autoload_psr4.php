<?php  $vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);
return array( "think\\composer\\" => array( $vendorDir . "/topthink/think-installer/src" ), "think\\" => array( $baseDir . "/thinkphp/library/think" ), "app\\" => array( $baseDir . "/application" ) );
?>