<?php  global $_GPC;
global $_W;
define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
$uniacid = $_W["uniacid"];
$module_name = $_W["current_module"]["name"];
$page = intval($_GPC["page"]);
$limit = intval($_GPC["limit"]);
$id = intval($_GPC["id"]);
if( $id ) 
{
	if( !is_dir(ATTACHMENT_ROOT . "/" . "images") ) 
	{
		mkdir(ATTACHMENT_ROOT . "/" . "images");
	}
	if( !is_dir(ATTACHMENT_ROOT . "/" . "images/longbing_card") ) 
	{
		mkdir(ATTACHMENT_ROOT . "/" . "images/longbing_card");
	}
	if( !is_dir(ATTACHMENT_ROOT . "/" . "images/longbing_card/" . $_W["uniacid"]) ) 
	{
		mkdir(ATTACHMENT_ROOT . "/" . "images/longbing_card/" . $_W["uniacid"]);
	}
	$destination_folder = ATTACHMENT_ROOT . "/images" . "/longbing_card/" . $_W["uniacid"];
	$image = $destination_folder . "/" . $_W["uniacid"] . "-" . $id . "-payqr.png";
	$path = "longbing_card/pages/pay/pay/pay?uid=" . $id;
	$at = $this->getAccessToken();
	$res = $this->curlPost("https://api.weixin.qq.com/wxa/getwxacode?access_token=" . $at, json_encode(array( "path" => $path )));
	if( $res ) 
	{
		file_put_contents($image, $res);
	}
	$destination_folder = "images" . "/longbing_card/" . $uniacid;
	$image = $destination_folder . "/" . $uniacid . "-" . $id . "-payqr.png";
	$image = $_W["siteroot"] . $_W["config"]["upload"]["attachdir"] . "/" . $image;
	pdo_update("longbing_card_user", array( "pay_qr" => "images" . "/longbing_card/" . $uniacid . "/" . $uniacid . "-" . $id . "-payqr.png" ), array( "id" => $id ));
	message("请求成功", $this->createWebUrl("manage/payqrImg"), "success");
}
if( !$page ) 
{
	$page = 1;
}
if( !$limit ) 
{
	$limit = 10;
}
$limit = array( $page, $limit );
$where = array( "uniacid" => $uniacid, "is_staff" => 1 );
$curr = 1;
$keyword = "";
$mark_value = "";
$deal_value = "";
if( isset($_GPC["key"]) ) 
{
	if( $_GPC["key"]["keyword"] ) 
	{
		$keyword = $_GPC["key"]["keyword"];
		$where["name like"] = "%" . $keyword . "%";
	}
	$list = pdo_getslice("longbing_card_user_info", $where, $limit, $count, array( ), "", array( "top desc" ));
}
else 
{
	$list = pdo_getslice("longbing_card_user_info", $where, $limit, $count, array( ), "", array( "top desc" ));
}
foreach( $list as $k => $v ) 
{
	$list[$k]["avatar"] = tomedia($v["avatar"]);
	$destination_folder = "images" . "/longbing_card/" . $uniacid;
	$image = $destination_folder . "/" . $uniacid . "-" . $v["fans_id"] . "-payqr.png";
	$image = $_W["siteroot"] . $_W["config"]["upload"]["attachdir"] . "/" . $image;
	$list[$k]["payqrImg"] = "";
	$list[$k]["img"] = $image;
	$destination_folder = ATTACHMENT_ROOT . "/images" . "/longbing_card/" . $_W["uniacid"];
	if( file_exists($destination_folder . "/" . $uniacid . "-" . $v["fans_id"] . "-payqr.png") ) 
	{
		$size = @filesize($destination_folder . "/" . $uniacid . "-" . $v["fans_id"] . "-payqr.png");
		if( 51220 < $size ) 
		{
			$list[$k]["payqrImg"] = $image;
		}
	}
}
$perPage = 15;
$returnData["code"] = 0;
$returnData["data"] = $list;
$returnData["msg"] = "success";
$returnData["count"] = $count;
echo json_encode($returnData, JSON_UNESCAPED_UNICODE);
?>