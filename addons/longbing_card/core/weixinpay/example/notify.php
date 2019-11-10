<?php  ini_set("date.timezone", "Asia/Shanghai");
error_reporting(1);
require_once("../lib/WxPay.Api.php");
require_once("../lib/WxPay.Notify.php");
require_once("log.php");
$logHandler = new CLogFileHandler("../logs/" . date("Y-m-d") . ".log");
$log = Log::Init($logHandler, 15);
class PayNotifyCallBack extends WxPayNotify 
{
	public function Queryorder($transaction_id) 
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
		Log::DEBUG("query:" . json_encode($result));
		if( array_key_exists("return_code", $result) && array_key_exists("result_code", $result) && $result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS" ) 
		{
			return true;
		}
		return false;
	}
	public function NotifyProcess($data, &$msg) 
	{
		Log::DEBUG("call back:" . json_encode($data));
		$notfiyOutput = array( );
		if( !array_key_exists("transaction_id", $data) ) 
		{
			$msg = "输入参数不正确";
			return false;
		}
		if( !$this->Queryorder($data["transaction_id"]) ) 
		{
			$msg = "订单查询失败";
			return false;
		}
		return true;
	}
}
Log::DEBUG("begin notify");
$notify = new PayNotifyCallBack();
$notify->Handle(false);
?>