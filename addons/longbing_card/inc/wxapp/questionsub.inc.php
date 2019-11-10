<?php  define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
global $_GPC;
global $_W;
$uniacid = $_W["uniacid"];
$to_uid = $_GPC["user_id"];
$client_id = $_GPC["client_id"];
$question = array( );
foreach( $_GPC as $index => $item ) 
{
	if( strpos($index, "id_") !== false ) 
	{
		$id_arr = explode("_", $index);
		$id_arr_tmp[] = $id_arr[1];
		$tmp = array( "id" => $id_arr[1], "answer" => $item );
		array_push($question, $tmp);
	}
}
if( !$client_id || !$to_uid ) 
{
	return $this->result(-1, "请传入参数", array( ));
}
$sql = "INSERT INTO " . tablename("longbing_card_qn_answer") . " (`q_id`, `user_id`, `staff_id`, `answer`, `uniacid`, `status`, `create_time`, `update_time`) values ";
$time = time();
if( is_array($question) ) 
{
	foreach( $question as $index => $item ) 
	{
		$tmp = "(" . $item["id"] . ", " . $client_id . ", " . $to_uid . ", '" . $item["answer"] . "', " . $uniacid . ", 1, " . $time . ", " . $time . "),";
		$sql .= $tmp;
	}
}
$sql = trim($sql, ",");
$result = false;
if( is_array($question) && count($question) ) 
{
	pdo_update("longbing_card_qn_answer", array( "status" => 0 ), array( "user_id" => $client_id, "staff_id" => $to_uid, "q_id in" => $id_arr_tmp ));
	$result = pdo_query($sql);
}
if( $result ) 
{
	return $this->result(0, "请求成功", array( ));
}
return $this->result(-1, "请求失败", array( $question ));
?>