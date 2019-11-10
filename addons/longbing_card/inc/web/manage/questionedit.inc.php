<?php  global $_GPC;
global $_W;
define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
$uniacid = $_W["uniacid"];
$module_name = $_W["current_module"]["name"];
$id = $_GPC["id"];
if( $_GPC["action"] == "edit" ) 
{
	$time = time();
	if( isset($_GPC["id"]) && $_GPC["id"] ) 
	{
		$id = $_GPC["id"];
		$data = array( "title" => $_GPC["title"], "update_time" => $time );
		$result = pdo_update("longbing_card_qn_questionnaire", $data, array( "id" => $id ));
		if( !$result ) 
		{
			message("保存失败", "", "error");
		}
		pdo_update("longbing_card_qn_question", array( "status" => 0 ), array( "naire_id" => $id ));
		foreach( $_GPC["old"] as $index => $item ) 
		{
			$data = array( "title" => $item, "update_time" => $time, "status" => 1 );
			pdo_update("longbing_card_qn_question", $data, array( "id" => $index ));
		}
	}
	else 
	{
		$where = array( "uniacid" => $_W["uniacid"], "status >" => -1, "title" => $_GPC["title"] );
		$item = pdo_getall("longbing_card_qn_questionnaire", $where);
		if( $item ) 
		{
			message("已经存在该内容了", "", "error");
		}
		$data = array( "uniacid" => $_W["uniacid"], "status" => 0, "title" => $_GPC["title"], "create_time" => $time, "update_time" => $time );
		$result = pdo_insert("longbing_card_qn_questionnaire", $data);
		if( !$result ) 
		{
			message("保存失败", "", "error");
		}
		$id = pdo_insertid();
	}
	foreach( $_GPC["completion"] as $index => $item ) 
	{
		$data = array( "naire_id" => $id, "title" => $item, "uniacid" => $_W["uniacid"], "status" => 1, "create_time" => $time, "update_time" => $time );
		pdo_insert("longbing_card_qn_question", $data);
	}
	message("操作成功", $this->createWebUrl("manage/question"), "success");
}
if( $id == 0 ) 
{
	$data["info"] = array( );
	$data["questions"] = array( );
}
else 
{
	$info = pdo_get("longbing_card_qn_questionnaire", array( "id" => $id ));
	$data["info"] = $info;
	$questions = pdo_getall("longbing_card_qn_question", array( "naire_id" => $id, "status" => 1 ));
	$data["questions"] = ($questions ? $questions : array( ));
}
load()->func("tpl");
include($this->template("manage/questionEdit"));
?>