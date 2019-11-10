<?php  echo "<html>\r\r\n<head>\r\r\n    <meta http-equiv=\"content-type\" content=\"text/html;charset=utf-8\"/>\r\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\" /> \r\r\n    <title>微信支付样例-退款</title>\r\r\n</head>\r\r\n";
ini_set("date.timezone", "Asia/Shanghai");
error_reporting(1);
require_once("../lib/WxPay.Api.php");
require_once("log.php");
$logHandler = new CLogFileHandler("../logs/" . date("Y-m-d") . ".log");
$log = Log::Init($logHandler, 15);
if( isset($_REQUEST["transaction_id"]) && $_REQUEST["transaction_id"] != "" ) 
{
	$transaction_id = $_REQUEST["transaction_id"];
	$total_fee = $_REQUEST["total_fee"];
	$refund_fee = $_REQUEST["refund_fee"];
	$input = new WxPayRefund();
	$input->SetTransaction_id($transaction_id);
	$input->SetTotal_fee($total_fee);
	$input->SetRefund_fee($refund_fee);
	$input->SetOut_refund_no(WX_MCHID . date("YmdHis"));
	$input->SetOp_user_id(WX_MCHID);
	printf_info(WxPayApi::refund($input));
	exit();
}
if( isset($_REQUEST["out_trade_no"]) && $_REQUEST["out_trade_no"] != "" ) 
{
	$out_trade_no = $_REQUEST["out_trade_no"];
	$total_fee = $_REQUEST["total_fee"];
	$refund_fee = $_REQUEST["refund_fee"];
	$input = new WxPayRefund();
	$input->SetOut_trade_no($out_trade_no);
	$input->SetTotal_fee($total_fee);
	$input->SetRefund_fee($refund_fee);
	$input->SetOut_refund_no(WX_MCHID . date("YmdHis"));
	$input->SetOp_user_id(WX_MCHID);
	printf_info(WxPayApi::refund($input));
	exit();
}
echo "\r\n<body>  \r\r\n\t<form action=\"#\" method=\"post\">\r\r\n        <div style=\"margin-left:2%;color:#f00\">微信订单号和商户订单号选少填一个，微信订单号优先：</div><br/>\r\r\n        <div style=\"margin-left:2%;\">微信订单号：</div><br/>\r\r\n        <input type=\"text\" style=\"width:96%;height:35px;margin-left:2%;\" name=\"transaction_id\" /><br /><br />\r\r\n        <div style=\"margin-left:2%;\">商户订单号：</div><br/>\r\r\n        <input type=\"text\" style=\"width:96%;height:35px;margin-left:2%;\" name=\"out_trade_no\" /><br /><br />\r\r\n        <div style=\"margin-left:2%;\">订单总金额(分)：</div><br/>\r\r\n        <input type=\"text\" style=\"width:96%;height:35px;margin-left:2%;\" name=\"total_fee\" /><br /><br />\r\r\n        <div style=\"margin-left:2%;\">退款金额(分)：</div><br/>\r\r\n        <input type=\"text\" style=\"width:96%;height:35px;margin-left:2%;\" name=\"refund_fee\" /><br /><br />\r\r\n\t\t<div align=\"center\">\r\r\n\t\t\t<input type=\"submit\" value=\"提交退款\" style=\"width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;\" type=\"button\" onclick=\"callpay()\" />\r\r\n\t\t</div>\r\r\n\t</form>\r\r\n</body>\r\r\n</html>";
function printf_info($data) 
{
	foreach( $data as $key => $value ) 
	{
		echo "<font color='#f00;'>" . $key . "</font> : " . $value . " <br/>";
	}
}
?>