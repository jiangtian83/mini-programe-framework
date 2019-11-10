<?php  global $_GPC;
global $_W;
if( $_GPC["s"] ) 
{
	$_GET["s"] = $_GPC["s"];
}
else 
{
	$_GET["s"] = "index/index/index";
}
define("APP_PATH", ADDON_PATH . "/core/application/");
define("APP_STATIC_PATH", "/addons/" . APP_NAME . "/core/public/static/");
require(ADDON_PATH . "/core/thinkphp/start.php");
?>