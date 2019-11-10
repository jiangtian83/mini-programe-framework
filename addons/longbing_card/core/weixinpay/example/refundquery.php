<?php  echo "<html>\r\r\n<head>\r\r\n    <meta http-equiv=\"content-type\" content=\"text/html;charset=utf-8\"/>\r\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\" /> \r\r\n    <title>微信支付样例-查退款单</title>\r\r\n</head>\r\r\n";
ini_set("date.timezone", "Asia/Shanghai");
error_reporting(1);
require_once("../lib/WxPay.Api.php");
require_once("log.php");
$logHandler = new CLogFileHandler("../logs/" . date("Y-m-d") . ".log");
$log = Log::Init($logHandler, 15);
if( isset($_REQUEST["transaction_id"]) && $_REQUEST["transaction_id"] != "" ) 
{
	$transaction_id = $_REQUEST["transaction_id"];
	$input = new WxPayRefundQuery();
	$input->SetTransaction_id($transaction_id);
	printf_info(WxPayApi::refundQuery($input));
}
if( isset($_REQUEST["out_trade_no"]) && $_REQUEST["out_trade_no"] != "" ) 
{
	$out_trade_no = $_REQUEST["out_trade_no"];
	$input = new WxPayRefundQuery();
	$input->SetOut_trade_no($out_trade_no);
	printf_info(WxPayApi::refundQuery($input));
	exit();
}
if( isset($_REQUEST["out_refund_no"]) && $_REQUEST["out_refund_no"] != "" ) 
{
	$out_refund_no = $_REQUEST["out_refund_no"];
	$input = new WxPayRefundQuery();
	$input->SetOut_refund_no($out_refund_no);
	printf_info(WxPayApi::refundQuery($input));
	exit();
}
if( isset($_REQUEST["refund_id"]) && $_REQUEST["refund_id"] != "" ) 
{
	$refund_id = $_REQUEST["refund_id"];
	$input = new WxPayRefundQuery();
	$input->SetRefund_id($refund_id);
	printf_info(WxPayApi::refundQuery($input));
	exit();
}
echo "\r\n<body>  \r\r\n\t<form action=\"#\" method=\"post\">\r\r\n        <div style=\"margin-left:2%;color:#f00\">微信订单号、商户订单号、微信订单号、微信退款单号选填至少一个，微信退款单号优先：</div><br/>\r\r\n        <div style=\"margin-left:2%;\">微信订单号：</div><br/>\r\r\n        <input type=\"text\" style=\"width:96%;height:35px;margin-left:2%;\" name=\"transaction_id\" /><br /><br />\r\r\n        <div style=\"margin-left:2%;\">商户订单号：</div><br/>\r\r\n        <input type=\"text\" style=\"width:96%;height:35px;margin-left:2%;\" name=\"out_trade_no\" /><br /><br />\r\r\n        <div style=\"margin-left:2%;\">商户退款单号：</div><br/>\r\r\n        <input type=\"text\" style=\"width:96%;height:35px;margin-left:2%;\" name=\"out_refund_no\" /><br /><br />\r\r\n        <div style=\"margin-left:2%;\">微信退款单号：</div><br/>\r\r\n        <input type=\"text\" style=\"width:96%;height:35px;margin-left:2%;\" name=\"refund_id\" /><br /><br />\r\r\n\t\t<div align=\"center\">\r\r\n\t\t\t<input type=\"submit\" value=\"查询\" style=\"width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;\" type=\"button\" onclick=\"callpay()\" />\r\r\n\t\t</div>\r\r\n\t</form>\r\r\n</body>\r\r\n</html>";
function printf_info($data) 
{
	foreach( $data as $key => $value ) 
	{
		echo "<font color='#f00;'>" . $key . "</font> : " . $value . " <br/>";
	}
}
?>