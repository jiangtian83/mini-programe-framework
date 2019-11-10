<?php  define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
global $_GPC;
global $_W;
$id = $_GPC["id"];
$to_uid = $_GPC["to_uid"];
if( !$id ) 
{
	return $this->result(-1, "请传入参数", array( ));
}
$uniacid = $_W["uniacid"];
$where = array( "uniacid" => $uniacid, "status" => 1, "id" => $id );
$info = pdo_get("lb_appoint_project", $where);
if( !$info ) 
{
	return $this->result(-2, "未找到该服务或者服务已关闭", array( ));
}
$info["cover"] = tomedia($info["cover"]);
$tmp = $info["carousel"];
$tmp = explode(",", $tmp);
$info["carousel"] = array( );
foreach( $tmp as $k => $v ) 
{
	array_push($info["carousel"], tomedia($v));
}
$info["times_start"] = explode(",", $info["times_start"]);
$info["times_end"] = explode(",", $info["times_end"]);
$range = array( );
foreach( $info["times_start"] as $index => $item ) 
{
	$tmp = array( "value" => $info["times_start"][$index] . "-" . $info["times_end"][$index], "appointed" => 0 );
	array_push($range, $tmp);
}
$info["range"] = $range;
$info["days"] = explode(",", $info["days"]);
$info["days_week"] = formatWeek($info["days"]);
$info["date"] = array( );
for( $c = 0; count($info["date"]) < 7 && !empty($info["days"]);
$c++ ) 
{
	$count = count($info["date"]);
	if( $count == 7 ) 
	{
		break;
	}
	$time = ($c == 0 ? time() : strtotime("+" . $c . " day"));
	$w = date("w", $time);
	if( $w == 0 ) 
	{
		$w = 7;
	}
	if( in_array($w, $info["days"]) ) 
	{
		array_push($info["date"], date("Y-m-d", $time));
	}
}
$info["content"] = $this->toWXml($info["content"]);
$classify = pdo_get("lb_appoint_classify", array( "id" => $info["classify_id"] ), array( "id", "title" ));
$info["classify_title"] = $classify["title"];
if( !is_dir(ATTACHMENT_ROOT . "/" . "images") ) 
{
	mkdir(ATTACHMENT_ROOT . "/" . "images");
}
if( !is_dir(ATTACHMENT_ROOT . "/" . "images/longbing_card") ) 
{
	mkdir(ATTACHMENT_ROOT . "/" . "images/longbing_card");
}
if( !is_dir(ATTACHMENT_ROOT . "/" . "images/longbing_card/" . $_W["uniacid"] . "/") ) 
{
	mkdir(ATTACHMENT_ROOT . "/" . "images/longbing_card/" . $_W["uniacid"] . "/");
}
$destination_folder = ATTACHMENT_ROOT . "/images" . "/longbing_card/" . $_W["uniacid"];
$image = $destination_folder . "/" . $_W["uniacid"] . "-" . $to_uid . "-" . $info["id"] . "-appoint.png";
$path = "longbing_card/reserve/pages/detail/detail?uid=" . $to_uid . "&id=" . $info["id"];
$create = false;
if( !file_exists($image) ) 
{
	$create = true;
}
else 
{
	$size = @filesize($image);
	if( $size < 51220 ) 
	{
		$create = true;
	}
}
if( $create ) 
{
	$at = $this->getAccessToken();
	$res = $this->curlPost("https://api.weixin.qq.com/wxa/getwxacode?access_token=" . $at, json_encode(array( "path" => $path )));
	if( $res ) 
	{
		file_put_contents($image, $res);
	}
}
$info["qr"] = $_W["siteroot"] . $_W["config"]["upload"]["attachdir"] . "/" . "images" . "/longbing_card/" . $_W["uniacid"] . "/" . $_W["uniacid"] . "-" . $to_uid . "-" . $info["id"] . "-appoint.png";
$staff_company_id = 0;
if( $to_uid ) 
{
	$staff_info = pdo_get("longbing_card_user_info", array( "fans_id" => $to_uid ));
	if( $staff_info ) 
	{
		$staff_company_id = $staff_info["company_id"];
	}
}
if( $staff_company_id ) 
{
	$staff_company_info = pdo_get("longbing_card_company", array( "id" => $staff_company_id, "status" => 1 ));
	if( !$staff_company_info ) 
	{
		$staff_company_info = pdo_get("longbing_card_company", array( "uniacid" => $uniacid, "status" => 1 ));
	}
}
else 
{
	$staff_company_info = pdo_get("longbing_card_company", array( "uniacid" => $uniacid, "status" => 1 ));
}
$info["staff_company_info"] = $staff_company_info;
$where = array( "status >" => 0, "project_id" => $id );
$limit = array( 1, 10 );
$record = pdo_getslice("lb_appoint_record", $where, $limit, $count, array( "id" ));
$info["record_count"] = $count;
$info["coverLocal"] = $this->transImage($info["cover"]);
return $this->result(0, "", $info);
function formatWeek($data) 
{
	foreach( $data as $index => $item ) 
	{
		switch( $item ) 
		{
			case 1: $data[$index] = "星期一";
			break;
			case 2: $data[$index] = "星期二";
			break;
			case 3: $data[$index] = "星期三";
			break;
			case 4: $data[$index] = "星期四";
			break;
			case 5: $data[$index] = "星期五";
			break;
			case 6: $data[$index] = "星期六";
			break;
			case 7: $data[$index] = "星期天";
			break;
		}
	}
	return $data;
}
?>