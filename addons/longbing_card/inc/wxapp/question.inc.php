<?php  define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
global $_GPC;
global $_W;
$uniacid = $_W["uniacid"];
$client_id = $_GPC["client_id"];
$to_uid = $_GPC["user_id"];
if( !$client_id || !$to_uid ) 
{
	return $this->result(-1, "请传入参数", array( ));
}
$info = pdo_get("longbing_card_qn_questionnaire", array( "uniacid" => $uniacid, "status" => 1 ));
if( !$info ) 
{
	return $this->result(0, "", array( ));
}
$questions = pdo_getall("longbing_card_qn_question", array( "uniacid" => $uniacid, "status" => 1, "naire_id" => $info["id"] ), array( "id", "title" ));
$answers = pdo_getall("longbing_card_qn_answer", array( "user_id" => $client_id, "staff_id" => $to_uid, "status" => 1 ), array( "q_id", "answer" ));
foreach( $questions as $index => $item ) 
{
	$questions[$index]["answer"] = "";
	foreach( $answers as $k => $v ) 
	{
		if( $v["q_id"] == $item["id"] ) 
		{
			$questions[$index]["answer"] = $v["answer"];
		}
	}
}
$info["questions"] = $questions;
$info["answers"] = $answers;
return $this->result(0, "请求成功", $info);
?>