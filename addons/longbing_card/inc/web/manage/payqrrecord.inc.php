<?php  global $_GPC;
global $_W;
define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
$uniacid = $_W["uniacid"];
$module_name = $_W["current_module"]["name"];
$src = "//" . $_SERVER["HTTP_HOST"] . "/app/index.php?i=" . $uniacid . "&t=0&v=1.0&from=wxapp&c=entry&a=wxapp&m=" . $module_name . "&do=managepayqrrecord";
load()->func("tpl");
include($this->template("manage/payqrRecord"));
return false;
?>