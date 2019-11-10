<?php  define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
global $_GPC;
global $_W;
$id = $_GPC["id"];
if( !$id ) 
{
	return $this->result(-1, "请传入参数", array( ));
}
$uniacid = $_W["uniacid"];
$where = array( "uniacid" => $uniacid, "id" => $id );
$info = pdo_get("lb_appoint_record", $where);
if( !$info ) 
{
	return $this->result(-2, "未找到预约记录", array( ));
}
if( $info["status"] != 1 ) 
{
	return $this->result(-2, "该记录未处于未完成状态", array( ));
}
$result = pdo_update("lb_appoint_record", array( "status" => 2, "update_time" => time() ), $where);
if( $result ) 
{
	return $this->result(0, "操作成功", array( ));
}
return $this->result(-2, "操作失败", array( ));
?>