<?php  define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
global $_GPC;
global $_W;
$uniacid = $_W["uniacid"];
$uid = $_GPC["user_id"];
$id = $_GPC["id"];
$pwd = $_GPC["pwd"];
if( !$uid || !$id || !$pwd ) 
{
	return $this->result(-1, "require", array( ));
}
$user = pdo_get("longbing_card_user", array( "id" => $uid ));
$config = pdo_get("longbing_card_config", array( "uniacid" => $uniacid ));
if( !$config || !$config["order_pwd"] ) 
{
	return $this->result(-1, "后台没有设置核销密码", array( ));
}
if( $pwd != $config["order_pwd"] ) 
{
	return $this->result(-1, "核销密码错误", array( ));
}
$order = pdo_get("longbing_card_shop_order", array( "id" => $id ));
if( !$order ) 
{
	return $this->result(-1, "没有找到订单", array( ));
}
if( $order["pay_status"] != 1 ) 
{
	return $this->result(-1, "订单未支付, 无法核销", array( ));
}
if( $order["order_status"] == 1 ) 
{
	return $this->result(-1, "订单已取消, 无法核销", array( ));
}
if( 2 < $order["order_status"] ) 
{
	return $this->result(-1, "订单已完成, 无法核销", array( ));
}
$result = pdo_update("longbing_card_shop_order", array( "order_status" => 3, "write_off_id" => $uid, "update_time" => time() ), array( "id" => $id ));
if( $result ) 
{
	changewater($id);
	return $this->result(0, "核销成功", array( "user" => $user ));
}
return $this->result(-1, "核销失败", array( ));
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