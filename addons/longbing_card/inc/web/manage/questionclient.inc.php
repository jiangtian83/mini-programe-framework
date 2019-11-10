<?php  global $_GPC;
global $_W;
define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
$uniacid = $_W["uniacid"];
$module_name = $_W["current_module"]["name"];
$id = $_GPC["id"];
$info = pdo_get("longbing_card_qn_questionnaire", array( "uniacid" => $uniacid, "status" => 1 ));
if( $info ) 
{
	$question = pdo_getall("longbing_card_qn_question", array( "uniacid" => $uniacid, "status" => 1, "naire_id" => $info["id"] ));
	$answer = pdo_getall("longbing_card_qn_answer", array( "uniacid" => $uniacid, "status" => 1, "user_id" => $id ));
	foreach( $question as $index => $item ) 
	{
		$question[$index]["answer"] = "暂无记录";
		foreach( $answer as $k => $v ) 
		{
			if( $item["id"] == $v["q_id"] ) 
			{
				$question[$index]["answer"] = $v["answer"];
				break;
			}
		}
	}
	$info["question"] = $question;
}
else 
{
	$info["question"] = array( );
}
load()->func("tpl");
include($this->template("manage/questionClient"));
?>