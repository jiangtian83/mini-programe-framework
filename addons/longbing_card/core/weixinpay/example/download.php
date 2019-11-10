<?php  require_once("../lib/WxPay.Api.php");
if( isset($_REQUEST["bill_date"]) && $_REQUEST["bill_date"] != "" ) 
{
	$bill_date = $_REQUEST["bill_date"];
	$bill_type = $_REQUEST["bill_type"];
	$input = new WxPayDownloadBill();
	$input->SetBill_date($bill_date);
	$input->SetBill_type($bill_type);
	$file = WxPayApi::downloadBill($input);
	echo $file;
	exit( 0 );
}
echo "\r\n<html>\r\r\n<head>\r\r\n    <meta http-equiv=\"content-type\" content=\"text/html;charset=utf-8\"/>\r\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\" /> \r\r\n    <title>微信支付样例-查退款单</title>\r\r\n</head>\r\r\n<body>  \r\r\n\t<form action=\"#\" method=\"post\">\r\r\n        <div style=\"margin-left:2%;\">对账日期：</div><br/>\r\r\n        <input type=\"text\" style=\"width:96%;height:35px;margin-left:2%;\" name=\"bill_date\" /><br /><br />\r\r\n        <div style=\"margin-left:2%;\">账单类型：</div><br/>\r\r\n        <select style=\"width:96%;height:35px;margin-left:2%;\" name=\"bill_type\">\r\r\n\t\t  <option value =\"ALL\">所有订单信息</option>\r\r\n\t\t  <option value =\"SUCCESS\">成功支付的订单</option>\r\r\n\t\t  <option value=\"REFUND\">退款订单</option>\r\r\n\t\t  <option value=\"REVOKED\">撤销的订单</option>\r\r\n\t\t</select><br /><br />\r\r\n       \t<div align=\"center\">\r\r\n\t\t\t<input type=\"submit\" value=\"下载订单\" style=\"width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;\" type=\"button\" onclick=\"callpay()\" />\r\r\n\t\t</div>\r\r\n\t</form>\r\r\n</body>\r\r\n</html>";
?>