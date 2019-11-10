<?php  global $_GPC;
global $_W;
define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
$uniacid = $_W["uniacid"];
$module_name = $_W["current_module"]["name"];
$page = intval($_GPC["page"]);
$limit = intval($_GPC["limit"]);
if( !$page ) 
{
	$page = 1;
}
if( !$limit ) 
{
	$limit = 10;
}
$limit = array( $page, $limit );
$where = array( "uniacid" => $_W["uniacid"], "order_status !=" => 1 );
$curr = $page;
$type = 0;
$statusArr = array( "全部订单", "未支付", "待发货", "已发货", "已完成" );
if( isset($_GPC["key"]) && isset($_GPC["key"]["type"]) && $_GPC["key"]["type"] ) 
{
	$type = $_GPC["key"]["type"];
	$type = intval($type);
	switch( $type ) 
	{
		case 1: $where["pay_status"] = 0;
		$where["order_status"] = 0;
		break;
		case 2: $where["pay_status"] = 1;
		$where["order_status"] = 0;
		break;
		case 3: $where["pay_status"] = 1;
		$where["order_status"] = 2;
		break;
		case 4: $where["pay_status"] = 1;
		$where["order_status >"] = 2;
		break;
	}
}
if( isset($_GPC["key"]) && isset($_GPC["key"]["keyword"]) && $_GPC["key"]["keyword"] ) 
{
	$keyword = $_GPC["key"]["keyword"];
	$keyword2 = "%" . $_GPC["key"]["keyword"] . "%";
	$goods = pdo_fetchall("SELECT id FROM " . tablename("longbing_card_goods") . "  where uniacid = " . $_W["uniacid"] . " && status > -1 && `name` LIKE '" . $keyword2 . "' ORDER BY recommend DESC, top DESC, id DESC");
	$item_arr = array( );
	if( $goods ) 
	{
		foreach( $goods as $index => $item ) 
		{
			array_push($item_arr, $item["id"]);
		}
		$items = pdo_getall("longbing_card_shop_order_item", array( "uniacid" => $_W["uniacid"], "goods_id in" => $item_arr ), array( ), "", array( "order_id" ));
		if( $items ) 
		{
			$where["id in"] = array( );
			foreach( $items as $index => $item ) 
			{
				array_push($where["id in"], $item["order_id"]);
			}
		}
		else 
		{
			$where["id in"] = NULL;
		}
	}
	else 
	{
		$where["id in"] = NULL;
	}
}
if( isset($_GPC["key"]) && isset($_GPC["key"]["transaction_id"]) && $_GPC["key"]["transaction_id"] ) 
{
	$transaction_id = $_GPC["key"]["transaction_id"];
	$transaction_id2 = "%" . $_GPC["key"]["transaction_id"] . "%";
	$where["transaction_id like"] = $transaction_id2;
}
$list = pdo_getslice("longbing_card_shop_order", $where, $limit, $count, array( ), "", array( "id desc" ));
foreach( $list as $k => $v ) 
{
	$items = pdo_getall("longbing_card_shop_order_item", array( "order_id" => $v["id"] ));
	$list[$k]["items"] = $items;
	$names = "";
	$list[$k]["is_self"] = 0;
	$list[$k]["address_total"] = $v["name"] . $v["phone"] . $v["address"] . $v["address_detail"];
	foreach( $items as $k2 => $v2 ) 
	{
		$names .= "; " . $v2["name"] . ": " . $v2["content"] . "，数量：" . $v2["number"];
		$goods = pdo_get("longbing_card_goods", array( "id" => $v2["goods_id"] ));
		if( $goods && $goods["is_self"] ) 
		{
			$list[$k]["is_self"] = 1;
		}
	}
	$names = trim($names, "; ");
	$list[$k]["names"] = $names;
	$list[$k]["user_info"] = pdo_get("longbing_card_user", array( "id" => $v["user_id"] ));
	$list[$k]["user_info_nickName"] = $list[$k]["user_info"]["nickName"];
	if( $v["to_uid"] ) 
	{
		$list[$k]["staff_info"] = pdo_get("longbing_card_user_info", array( "fans_id" => $v["to_uid"] ));
		$list[$k]["staff_info_name"] = $list[$k]["staff_info"]["name"];
	}
	else 
	{
		$list[$k]["staff_info"] = array( );
		$list[$k]["staff_info_name"] = "";
	}
	$list[$k]["collage_check"] = 1;
	if( $v["type"] == 1 ) 
	{
		$list[$k]["collage_info"] = pdo_get("longbing_card_shop_collage_list", array( "id" => $v["collage_id"] ));
		if( $list[$k]["collage_info"] && $list[$k]["collage_info"]["left_number"] != 0 ) 
		{
			$list[$k]["collage_check"] = 0;
		}
	}
	$list[$k]["create_time"] = date("Y-m-d H:i", $v["create_time"]);
	$list[$k]["write_off_user"] = "";
	$list[$k]["write_off_user_nickName"] = "";
	if( $v["write_off_id"] ) 
	{
		$list[$k]["write_off_user"] = pdo_get("longbing_card_user", array( "id" => $v["write_off_id"] ));
		$list[$k]["write_off_user_nickName"] = $list[$k]["write_off_user"]["nickName"];
	}
	$list[$k]["t_id"] = ($v["transaction_id"] ? $v["transaction_id"] : $v["out_trade_no"]);
	$list[$k]["t_id"] = "单号:" . $list[$k]["t_id"];
	$list[$k]["type_text"] = ($v["type_"] == 1 ? "拼团订单" : "普通订单");
	$list[$k]["item_status"] = "";
	if( $v["pay_status"] == 0 ) 
	{
		$list[$k]["item_status"] = "未支付";
	}
	else 
	{
		if( $v["order_status"] == 0 ) 
		{
			$list[$k]["item_status"] = "未发货";
		}
		if( $v["order_status"] == 2 ) 
		{
			$list[$k]["item_status"] = "已发货";
		}
		if( $v["order_status"] == 3 ) 
		{
			$list[$k]["item_status"] = "已完成";
		}
		if( $v["order_status"] == 4 ) 
		{
			$list[$k]["item_status"] = "";
		}
	}
}
$perPage = $limit[1];
$returnData["code"] = 0;
$returnData["data"] = $list;
$returnData["msg"] = "success";
$returnData["count"] = $count;
echo json_encode($returnData, JSON_UNESCAPED_UNICODE);
?>