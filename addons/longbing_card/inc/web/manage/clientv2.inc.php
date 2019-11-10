<?php  global $_GPC;
global $_W;
define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
$uniacid = $_W["uniacid"];
$module_name = $_W["current_module"]["name"];
$src = "//" . $_SERVER["HTTP_HOST"] . "/app/index.php?i=" . $uniacid . "&t=0&v=1.0&from=wxapp&c=entry&a=wxapp&m=" . $module_name . "&do=manageclientv2";
$mark_arr = array( array( "id" => 3, "value" => "未跟进" ), array( "id" => 1, "value" => "跟进中" ), array( "id" => 2, "value" => "已成交" ) );
$deal_arr = array( array( "id" => 1, "value" => "未成交" ), array( "id" => 2, "value" => "已成交" ) );
load()->func("tpl");
include($this->template("manage/clientv2"));
return false;
?>