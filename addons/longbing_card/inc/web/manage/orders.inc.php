<?php  global $_GPC;
global $_W;
define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
$uniacid = $_W["uniacid"];
$module_name = $_W["current_module"]["name"];
if( $_GPC["action"] == "send" ) 
{
	$item = pdo_get("longbing_card_shop_order", array( "id" => $_GPC["id"] ));
	if( !$item || empty($item) ) 
	{
		message("未找到该数据", "", "error");
	}
	$result = pdo_update("longbing_card_shop_order", array( "order_status" => 2, "courier_number" => $_GPC["courier_number"], "express_company" => $_GPC["express_company"], "express_phone" => $_GPC["express_phone"], "update_time" => time() ), array( "id" => $_GPC["id"] ));
	if( $result ) 
	{
		sendmsg($item);
		message("编辑成功", $this->createWebUrl("manage/orders"), "success");
	}
	message("编辑失败", "", "error");
}
if( $_GPC["action"] == "self" ) 
{
	$item = pdo_get("longbing_card_shop_order", array( "id" => $_GPC["id"] ));
	if( !$item || empty($item) ) 
	{
		message("未找到该数据", "", "error");
	}
	$result = pdo_update("longbing_card_shop_order", array( "order_status" => 3, "update_time" => time() ), array( "id" => $_GPC["id"] ));
	if( $result ) 
	{
		changewater($_GPC["id"]);
		sendmsg($item);
		message("编辑成功", $this->createWebUrl("manage/orders"), "success");
	}
	message("编辑失败", "", "error");
}
$statusArr = array( "全部订单", "未支付", "待发货", "已发货", "已完成" );
$src = "//" . $_SERVER["HTTP_HOST"] . "/app/index.php?i=" . $uniacid . "&t=0&v=1.0&from=wxapp&c=entry&a=wxapp&m=" . $module_name . "&do=manageorder";
load()->func("tpl");
include($this->template("manage/orders3"));
return false;
function getFormId($to_uid) 
{
	$beginTime = mktime(0, 0, 0, date("m"), date("d") - 6, date("Y"));
	pdo_delete("longbing_card_formId", array( "create_time <" => $beginTime ));
	$formId = pdo_get("longbing_card_formId", array( "user_id" => $to_uid ), array( ), "", "id asc");
	if( !$formId ) 
	{
		return false;
	}
	if( $formId["create_time"] < $beginTime ) 
	{
		pdo_delete("longbing_card_formId", array( "id" => $formId["id"] ));
		getFormId($to_uid);
	}
	else 
	{
		pdo_delete("longbing_card_formId", array( "id" => $formId["id"] ));
		return $formId["formId"];
	}
}
function getAccessToken() 
{
	global $_GPC;
	global $_W;
	$appid = $_W["account"]["key"];
	$appsecret = $_W["account"]["secret"];
	$appidMd5 = md5($appid);
	if( !is_file(IA_ROOT . "/data/tpl/web/" . $appidMd5 . ".txt") && is_dir(ATTACHMENT_ROOT . "/" . "images/longbing_card/" . $_W["uniacid"] . "/") ) 
	{
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $appsecret;
		$data = ihttp_get($url);
		$data = json_decode($data["content"], true);
		if( !isset($data["access_token"]) ) 
		{
			return false;
		}
		$access_token = $data["access_token"];
		file_put_contents(IA_ROOT . "/data/tpl/web/" . $appidMd5 . ".txt", json_encode(array( "at" => $access_token, "time" => time() + 6200 )));
		return $access_token;
	}
	if( is_file(IA_ROOT . "/data/tpl/web/" . $appidMd5 . ".txt") ) 
	{
		$fileInfo = file_get_contents(IA_ROOT . "/data/tpl/web/" . $appidMd5 . ".txt");
		if( !$fileInfo ) 
		{
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $appsecret;
			$data = ihttp_get($url);
			$data = json_decode($data["content"], true);
			if( !isset($data["access_token"]) ) 
			{
				return false;
			}
			$access_token = $data["access_token"];
			file_put_contents(IA_ROOT . "/data/tpl/web/" . $appidMd5 . ".txt", json_encode(array( "at" => $access_token, "time" => time() + 6200 )));
			return $access_token;
		}
		$fileInfo = json_decode($fileInfo, true);
		if( $fileInfo["time"] < time() ) 
		{
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $appsecret;
			$data = ihttp_get($url);
			$data = json_decode($data["content"], true);
			if( !isset($data["access_token"]) ) 
			{
				return false;
			}
			$access_token = $data["access_token"];
			file_put_contents(IA_ROOT . "/data/tpl/web/" . $appidMd5 . ".txt", json_encode(array( "at" => $access_token, "time" => time() + 6200 )));
			return $access_token;
		}
		return $fileInfo["at"];
	}
	return false;
}
function sendMsg($item) 
{
	global $_GPC;
	global $_W;
	$uid = $item["user_id"];
	if( !$uid ) 
	{
		return false;
	}
	$appid = $_W["account"]["key"];
	$appsecret = $_W["account"]["secret"];
	$client = pdo_get("longbing_card_user", array( "id" => $uid ));
	if( !$client ) 
	{
		return false;
	}
	$openid = $client["openid"];
	$name = $client["nickName"];
	$date = date("Y-m-d H:i");
	$config = pdo_get("longbing_card_config", array( "uniacid" => $_W["uniacid"] ), array( "mini_template_id", "notice_switch", "notice_i", "min_tmppid" ));
	if( $config["notice_switch"] == 1 && false ) 
	{
	}
	else 
	{
		if( !$config["mini_template_id"] ) 
		{
			return false;
		}
		$form = getformid($uid);
		if( !$form ) 
		{
			return false;
		}
		$access_token = getaccesstoken();
		if( !$access_token ) 
		{
			return false;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=" . $access_token;
		$page = "longbing_card/pages/uCenter/order/orderList/orderList?currentTab=3";
		if( $item["type"] === 1 ) 
		{
			$items = pdo_get("longbing_card_shop_order_item", array( "order_id" => $item["id"] ));
			$page = "longbing_card/pages/shop/releaseCollage/releaseCollage?id=" . $items["goods_id"] . "&status=toShare&to_uid=" . $item["to_uid"] . "&collage_id=";
		}
		$postData = array( "touser" => $openid, "template_id" => $config["mini_template_id"], "page" => $page, "form_id" => $form, "data" => array( "keyword1" => array( "value" => $name ), "keyword2" => array( "value" => "您的订单已发货" ), "keyword3" => array( "value" => $date ) ) );
		$postData = json_encode($postData);
		load()->func("communication");
		$response = ihttp_post($url, $postData);
	}
	return true;
}
function changeWater($id) 
{
	$time = time();
	$list = pdo_getall("longbing_card_selling_water", array( "order_id" => $id, "waiting" => 1 ));
	pdo_update("longbing_card_selling_water", array( "waiting" => 2, "update_time" => $time ), array( "order_id" => $id, "waiting" => 1 ));
	foreach( $list as $index => $item ) 
	{
		$money = ($item["price"] * $item["extract"]) / 100;
		$money = sprintf("%.2f", $money);
		$profit = pdo_get("longbing_card_selling_profit", array( "user_id" => $item["user_id"] ));
		if( $profit ) 
		{
			if( $money <= $profit["waiting"] ) 
			{
				$waiting = $profit["waiting"] - $money;
				$total_profit = $profit["total_profit"] + $money;
				$profit_money = $profit["profit"] + $money;
				$waiting = sprintf("%.2f", $waiting);
				$total_profit = sprintf("%.2f", $total_profit);
				$profit_money = sprintf("%.2f", $profit_money);
			}
			else 
			{
				$waiting = 0;
				$money = $profit["waiting"];
				$total_profit = $profit["total_profit"] + $profit["waiting"];
				$profit_money = $profit["profit"] + $profit["waiting"];
				$total_profit = sprintf("%.2f", $total_profit);
				$profit_money = sprintf("%.2f", $profit_money);
			}
			pdo_update("longbing_card_selling_profit", array( "waiting" => $waiting, "total_profit" => $total_profit, "profit" => $profit_money ), array( "id" => $profit["id"] ));
			$user = pdo_get("longbing_card_user", array( "id" => $item["source_id"] ));
			if( $user ) 
			{
				$create_money = $user["create_money"] + $money;
				$create_money = sprintf("%.2f", $create_money);
				pdo_update("longbing_card_user", array( "create_money" => $create_money, "update_time" => $time ), array( "id" => $user["id"] ));
			}
		}
	}
	return true;
}
?>