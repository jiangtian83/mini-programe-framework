<?php  echo "<html>\r\r\n<head>\r\r\n    <meta http-equiv=\"content-type\" content=\"text/html;charset=utf-8\"/>\r\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\" /> \r\r\n    <title>微信支付样例-查退款单</title>\r\r\n</head>\r\r\n";
require_once("../lib/WxPay.Api.php");
require_once("WxPay.MicroPay.php");
require_once("log.php");
$logHandler = new CLogFileHandler("../logs/" . date("Y-m-d") . ".log");
$log = Log::Init($logHandler, 15);
if( isset($_REQUEST["auth_code"]) && $_REQUEST["auth_code"] != "" ) 
{
	$auth_code = $_REQUEST["auth_code"];
	$input = new WxPayMicroPay();
	$input->SetAuth_code($auth_code);
	$input->SetBody("刷卡测试样例-支付");
	$input->SetTotal_fee("1");
	$input->SetOut_trade_no(WX_MCHID . date("YmdHis"));
	$microPay = new MicroPay();
	printf_info($microPay->pay($input));
}
echo "\r\n<body>  \r\r\n\t<form action=\"#\" method=\"post\">\r\r\n        <div style=\"margin-left:2%;\">商品描述：</div><br/>\r\r\n        <input type=\"text\" style=\"width:96%;height:35px;margin-left:2%;\" readonly value=\"刷卡测试样例-支付\" name=\"auth_code\" /><br /><br />\r\r\n        <div style=\"margin-left:2%;\">支付金额：</div><br/>\r\r\n        <input type=\"text\" style=\"width:96%;height:35px;margin-left:2%;\" readonly value=\"1分\" name=\"auth_code\" /><br /><br />\r\r\n        <div style=\"margin-left:2%;\">授权码：</div><br/>\r\r\n        <input type=\"text\" style=\"width:96%;height:35px;margin-left:2%;\" name=\"auth_code\" /><br /><br />\r\r\n       \t<div align=\"center\">\r\r\n\t\t\t<input type=\"submit\" value=\"提交刷卡\" style=\"width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;\" type=\"button\" onclick=\"callpay()\" />\r\r\n\t\t</div>\r\r\n\t</form>\r\r\n</body>\r\r\n</html>";
function printf_info($data) 
{
	foreach( $data as $key => $value ) 
	{
		echo "<font color='#00ff55;'>" . $key . "</font> : " . $value . " <br/>";
	}
}
?>