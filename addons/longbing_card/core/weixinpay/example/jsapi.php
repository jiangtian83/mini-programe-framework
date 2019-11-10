<?php  ini_set("date.timezone", "Asia/Shanghai");
require_once("../lib/WxPay.Api.php");
require_once("WxPay.JsApiPay.php");
require_once("log.php");
$logHandler = new CLogFileHandler("../logs/" . date("Y-m-d") . ".log");
$log = Log::Init($logHandler, 15);
$tools = new JsApiPay();
$openId = $tools->GetOpenid();
$input = new WxPayUnifiedOrder();
$input->SetBody("test");
$input->SetAttach("test");
$input->SetOut_trade_no(WX_MCHID . date("YmdHis"));
$input->SetTotal_fee("1");
$input->SetTime_start(date("YmdHis"));
$input->SetTime_expire(date("YmdHis", time() + 600));
$input->SetGoods_tag("test");
$input->SetNotify_url("http://paysdk.weixin.qq.com/example/notify.php");
$input->SetTrade_type("JSAPI");
$input->SetOpenid($openId);
$order = WxPayApi::unifiedOrder($input);
echo "<font color=\"#f00\"><b>统一下单支付单信息</b></font><br/>";
printf_info($order);
$jsApiParameters = $tools->GetJsApiParameters($order);
$editAddress = $tools->GetEditAddressParameters();
echo "\r\n\r\r\n<html>\r\r\n<head>\r\r\n    <meta http-equiv=\"content-type\" content=\"text/html;charset=utf-8\"/>\r\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\"/> \r\r\n    <title>微信支付样例-支付</title>\r\r\n    <script type=\"text/javascript\">\r\r\n\t//调用微信JS api 支付\r\r\n\tfunction jsApiCall()\r\r\n\t{\r\r\n\t\tWeixinJSBridge.invoke(\r\r\n\t\t\t'getBrandWCPayRequest',\r\r\n\t\t\t";
echo $jsApiParameters;
echo ",\r\r\n\t\t\tfunction(res){\r\r\n\t\t\t\tWeixinJSBridge.log(res.err_msg);\r\r\n\t\t\t\talert(res.err_code+res.err_desc+res.err_msg);\r\r\n\t\t\t}\r\r\n\t\t);\r\r\n\t}\r\r\n\r\r\n\tfunction callpay()\r\r\n\t{\r\r\n\t\tif (typeof WeixinJSBridge == \"undefined\"){\r\r\n\t\t    if( document.addEventListener ){\r\r\n\t\t        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);\r\r\n\t\t    }else if (document.attachEvent){\r\r\n\t\t        document.attachEvent('WeixinJSBridgeReady', jsApiCall); \r\r\n\t\t        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);\r\r\n\t\t    }\r\r\n\t\t}else{\r\r\n\t\t    jsApiCall();\r\r\n\t\t}\r\r\n\t}\r\r\n\t</script>\r\r\n\t<script type=\"text/javascript\">\r\r\n\t//获取共享地址\r\r\n\tfunction editAddress()\r\r\n\t{\r\r\n\t\tWeixinJSBridge.invoke(\r\r\n\t\t\t'editAddress',\r\r\n\t\t\t";
echo $editAddress;
echo ",\r\r\n\t\t\tfunction(res){\r\r\n\t\t\t\tvar value1 = res.proviceFirstStageName;\r\r\n\t\t\t\tvar value2 = res.addressCitySecondStageName;\r\r\n\t\t\t\tvar value3 = res.addressCountiesThirdStageName;\r\r\n\t\t\t\tvar value4 = res.addressDetailInfo;\r\r\n\t\t\t\tvar tel = res.telNumber;\r\r\n\t\t\t\t\r\r\n\t\t\t\talert(value1 + value2 + value3 + value4 + \":\" + tel);\r\r\n\t\t\t}\r\r\n\t\t);\r\r\n\t}\r\r\n\t\r\r\n\twindow.onload = function(){\r\r\n\t\tif (typeof WeixinJSBridge == \"undefined\"){\r\r\n\t\t    if( document.addEventListener ){\r\r\n\t\t        document.addEventListener('WeixinJSBridgeReady', editAddress, false);\r\r\n\t\t    }else if (document.attachEvent){\r\r\n\t\t        document.attachEvent('WeixinJSBridgeReady', editAddress); \r\r\n\t\t        document.attachEvent('onWeixinJSBridgeReady', editAddress);\r\r\n\t\t    }\r\r\n\t\t}else{\r\r\n\t\t\teditAddress();\r\r\n\t\t}\r\r\n\t};\r\r\n\t\r\r\n\t</script>\r\r\n</head>\r\r\n<body>\r\r\n    <br/>\r\r\n    <font color=\"#9ACD32\"><b>该笔订单支付金额为<span style=\"color:#f00;font-size:50px\">1分</span>钱</b></font><br/><br/>\r\r\n\t<div align=\"center\">\r\r\n\t\t<button style=\"width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;\" type=\"button\" onclick=\"callpay()\" >立即支付</button>\r\r\n\t</div>\r\r\n</body>\r\r\n</html>";
function printf_info($data) 
{
	foreach( $data as $key => $value ) 
	{
		echo "<font color='#00ff55;'>" . $key . "</font> : " . $value . " <br/>";
	}
}
?>