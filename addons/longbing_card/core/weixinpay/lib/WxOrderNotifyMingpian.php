<?php  ini_set("date.timezone", "Asia/Shanghai");
error_reporting(1);
require_once("WxPay.Api.php");
require_once("WxPay.Notify.php");
class WxOrderNotifyMingpian extends WxPayNotify 
{
	public function Queryorder($transaction_id) 
	{
		file_put_contents("./weixinQuery.txt", "in_query", FILE_APPEND);
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
		if( array_key_exists("return_code", $result) && array_key_exists("result_code", $result) && $result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS" ) 
		{
			$arr = unserialize($result["attach"]);
			file_put_contents("./weixinQuery.txt", "\$arr------" . $arr["type"], FILE_APPEND);
			if( is_array($arr) && $arr["type"] == "vip" ) 
			{
				$order_id = intval($arr["order_id"]);
				file_put_contents("./weixinQuery.txt", "\$order_id------" . $order_id, FILE_APPEND);
				$orderModel = new app\common\model\MingpianOrder();
				if( !($order = $orderModel->where(array( "id" => $order_id ))->find()) ) 
				{
					echo "FAIL";
					exit();
				}
				if( $order->status == 0 ) 
				{
					$orderModel->update(array( "status" => 1 ), array( "id" => $order_id ));
					$cardModel = new app\common\model\MingpianCard();
					$oriCard = $cardModel->where(array( "id" => $order["card_id"] ))->find();
					$vip_end_time = strtotime("+ " . $order["vip_term_month"] . " month");
					if( $oriCard["vip_level"] && $oriCard["vip_level"] == $order["vip_name"] ) 
					{
						$vip_end_time = strtotime("+ " . $order["vip_term_month"] . " month", $oriCard["vip_end_time"]);
					}
					$cardModel->update(array( "vip_level" => $order["vip_name"], "vip_end_time" => $vip_end_time, "is_vip_success" => 1 ), array( "id" => $order["card_id"] ));
					$param = serialize(array( "uid" => $order["uid"], "uniacid" => $order["uniacid"], "money" => $order["money"], "order_type" => $order["type"] ));
					think\Hook::exec("app\\common\\behavior\\PartnerMoney", "run", $param);
				}
				return true;
			}
			if( is_array($arr) && $arr["type"] == "template" ) 
			{
				$order_id = intval($arr["order_id"]);
				$orderModel = new app\common\model\MingpianOrder();
				if( !($order = $orderModel->where(array( "id" => $order_id ))->find()) ) 
				{
					echo "FAIL";
					exit();
				}
				if( $order->status == 0 ) 
				{
					$orderModel->update(array( "status" => 1 ), array( "id" => $order_id ));
					$payModel = new app\common\model\MingpianTemplateCard();
					$save_data = array( "uniacid" => $order["uniacid"], "create_time" => time(), "update_time" => time(), "card_id" => $order["card_id"], "temp_id" => $order["temp_id"], "order_id" => $order_id );
					$payModel->insert($save_data);
					$param = serialize(array( "uid" => $order["uid"], "uniacid" => $order["uniacid"], "money" => $order["money"], "order_type" => $order["type"] ));
					think\Hook::exec("app\\common\\behavior\\PartnerMoney", "run", $param);
				}
				return true;
			}
			if( is_array($arr) && $arr["type"] == "partner" ) 
			{
				$order_id = intval($arr["order_id"]);
				$orderModel = new app\common\model\MingpianOrder();
				if( !($order = $orderModel->where(array( "id" => $order_id ))->find()) ) 
				{
					echo "FAIL";
					exit();
				}
				if( $order->status == 0 ) 
				{
					$orderModel->update(array( "status" => 1 ), array( "id" => $order_id ));
					$userModel = new app\common\model\MingpianUser();
					$userModel->update(array( "partner_level" => $order["temp_id"] ), array( "id" => $order["uid"] ));
					$param = serialize(array( "uid" => $order["uid"], "uniacid" => $order["uniacid"], "money" => $order["money"], "order_type" => $order["type"] ));
					think\Hook::exec("app\\common\\behavior\\PartnerMoney", "run", $param);
				}
				return true;
			}
			if( is_array($arr) && $arr["type"] == "info_top" ) 
			{
				$order_id = intval($arr["order_id"]);
				$orderModel = new app\common\model\MingpianOrder();
				if( !($order = $orderModel->where(array( "id" => $order_id ))->find()) ) 
				{
					echo "FAIL";
					exit();
				}
				if( $order->status == 0 ) 
				{
					$orderModel->update(array( "status" => 1 ), array( "id" => $order_id ));
					$infoModel = new app\common\model\MingpianInfo();
					$ruleModel = new app\common\model\MingpianRule();
					$rule = $ruleModel->get(array( "id" => $order["temp_id"] ));
					$infoModel->where(array( "id" => $order["vip_name"] ))->update(array( "status" => 1, "is_top" => 1, "top_time" => time(), "top_end_time" => strtotime("+ " . $rule["days"] . "days") ));
					$param = serialize(array( "uid" => $order["uid"], "uniacid" => $order["uniacid"], "money" => $order["money"], "order_type" => $order["type"] ));
					think\Hook::exec("app\\common\\behavior\\PartnerMoney", "run", $param);
				}
				return true;
			}
		}
		return false;
	}
	public function NotifyProcess($data, &$msg) 
	{
		$notfiyOutput = array( );
		if( !array_key_exists("transaction_id", $data) ) 
		{
			file_put_contents("./weixinQuery.txt", "输入参数不正确", FILE_APPEND);
			$msg = "输入参数不正确";
			return false;
		}
		file_put_contents("./weixinQuery.txt", "abc", FILE_APPEND);
		if( !$this->Queryorder($data["transaction_id"]) ) 
		{
			$msg = "订单查询失败";
			return false;
		}
		return true;
	}
}
?>