<?php  define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
global $_GPC;
global $_W;
$uid = $_GPC["user_id"];
if( $this->redis_sup_v3 ) 
{
	$redis_key = "longbing_card_configv4_" . $_W["uniacid"];
	$data = $this->redis_server_v3->get($redis_key);
	if( $data ) 
	{
		$data = json_decode($data, true);
		$data["config"]["from_redis"] = 1;
		return $this->result(0, "suc", $data);
	}
}
$info = pdo_get("longbing_card_config", array( "uniacid" => $_W["uniacid"] ));
if( !$info ) 
{
	$dataConfig = array( "uniacid" => $_W["uniacid"], "status" => 1, "create_time" => $time, "update_time" => $time );
	@pdo_insert("longbing_card_config", $dataConfig);
	$info = pdo_get("longbing_card_config", array( "uniacid" => $_W["uniacid"] ));
}
if( !$info ) 
{
	return $this->result(-1, "need config", $data);
}
$info["default_video"] = tomedia($info["default_video"]);
$info["default_voice"] = tomedia($info["default_voice"]);
$info["appoint_pic"] = tomedia($info["appoint_pic"]);
$info["click_copy_show_img"] = tomedia($info["click_copy_show_img"]);
if( isset($info["order_pwd"]) ) 
{
	unset($info["order_pwd"]);
}
$info["copyright"] = tomedia($info["copyright"]);
$info["click_copy_show_img"] = ($info["click_copy_show_img"] ? $info["click_copy_show_img"] : $info["copyright"]);
$info["default_video_cover"] = tomedia($info["default_video_cover"]);
if( !LONGBING_AUTH_COPYRIGHT ) 
{
	$info["copyright"] = "https://retail.xiaochengxucms.com/images/12/2018/11/crDXyl3TyBRLUBch6ToqXL6e9D96hY.jpg";
}
$info["form"] = 0;
if( defined("LONGBING_AUTH_FORM") && LONGBING_AUTH_FORM ) 
{
	$info["form"] = 1;
}
$checkExists = pdo_tableexists("longbing_cardauth2_config");
if( $checkExists ) 
{
	$auth_info = pdo_get("longbing_cardauth2_config", array( "modular_id" => $_W["uniacid"] ));
	if( $auth_info && $auth_info["copyright_id"] ) 
	{
		$copyright = pdo_get("longbing_cardauth2_copyright", array( "id" => $auth_info["copyright_id"] ));
		if( $copyright ) 
		{
			$info["copyright"] = tomedia($copyright["image"]);
			$info["logo_text"] = $copyright["text"];
			$info["logo_phone"] = $copyright["phone"];
			$info["click_copy_content"] = $copyright["phone"];
			$info["click_copy_way"] = 1;
			$info["logo_switch"] = 2;
		}
	}
}
$data["config"] = $info;
$company = pdo_getall("longbing_card_company", array( "uniacid" => $_W["uniacid"], "status" => 1 ));
foreach( $company as $k => $v ) 
{
	$company[$k]["desc"] = tomedia($v["desc"]);
	$company[$k]["logo"] = $this->transImage($v["logo"]);
	$images = $v["culture"];
	$images = trim($images, ",");
	$images = explode(",", $images);
	$tmp = array( );
	foreach( $images as $k2 => $v2 ) 
	{
		$src = tomedia($v2);
		array_push($tmp, $src);
	}
	$company[$k]["culture"] = $tmp;
}
$data["company_list"] = $company;
$user = pdo_get("longbing_card_user_info", array( "fans_id" => $uid, "is_staff" => 1, "status" => 1 ));
if( $user ) 
{
	if( $user["company_id"] ) 
	{
		$company2 = pdo_get("longbing_card_company", array( "uniacid" => $_W["uniacid"], "id" => $user["company_id"] ));
		if( $company2 ) 
		{
			$company2["logo"] = $this->transImage($company2["logo"]);
			$data["my_company"] = $company2;
		}
		else 
		{
			$data["my_company"] = $company[0];
		}
	}
	else 
	{
		$data["my_company"] = $company[0];
	}
}
else 
{
	$data["my_company"] = $company[0];
}
$tabBar = array( "color" => "#838591", "selectedColor" => "#e93636", "backgroundColor" => "#fff", "borderStyle" => "white", "list" => array( array( "pagePath" => "", "text" => "名片", "currentTabBar" => "toCard", "iconPath" => "/longbing_card/resource/icon/icon-card.png", "selectedIconPath" => "/longbing_card/resource/icon/icon-card-cur.png", "method" => "reLaunch" ), array( "pagePath" => "", "pagePath2" => "/longbing_card/pages/shop/index/index", "text" => "商城", "currentTabBar" => "toShop", "iconPath" => "/longbing_card/resource/icon/icon-shop.png", "selectedIconPath" => "/longbing_card/resource/icon/icon-shop-cur.png", "method" => "reLaunch" ), array( "pagePath" => "", "text" => "动态", "currentTabBar" => "toNews", "iconPath" => "/longbing_card/resource/icon/icon-news.png", "selectedIconPath" => "/longbing_card/resource/icon/icon-news-cur.png", "method" => "reLaunch" ), array( "pagePath" => "", "text" => "官网", "currentTabBar" => "toCompany", "iconPath" => "/longbing_card/resource/icon/icon-company.png", "selectedIconPath" => "/longbing_card/resource/icon/icon-company-cur.png", "method" => "reLaunch" ) ) );
$list = array( );
$info = pdo_get("longbing_card_tabbar", array( "uniacid" => $_W["uniacid"] ));
if( !$info ) 
{
	$data["tabBar"] = $tabBar;
	if( $this->redis_sup_v3 ) 
	{
		$redis_key = "longbing_card_configv4_" . $_W["uniacid"];
		$this->redis_server_v3->set($redis_key, json_encode($data));
		$this->redis_server_v3->EXPIRE($redis_key, 60 * 120);
	}
	return $this->result(0, "suc", $data);
}
if( $info["menu1_is_hide"] ) 
{
	$tmp = array( "pagePath" => "", "text" => $info["menu1_name"], "currentTabBar" => "toCard", "iconPath" => "/longbing_card/resource/icon/icon-card.png", "selectedIconPath" => "/longbing_card/resource/icon/icon-card-cur.png", "method" => "reLaunch" );
	array_push($list, $tmp);
}
$appoint = false;
$data["config"]["plugin"] = array( "appoint" => 0 );
if( $info["menu_appoint_is_hide"] ) 
{
	$check_plugin = pdo_get("modules_plugin", array( "name" => "longbing_card_plugin_yuyue2", "main_module" => "longbing_card" ));
	if( $check_plugin ) 
	{
		$check_table = pdo_tableexists("lb_appoint_record_check");
		if( $check_table ) 
		{
			$checkExists = pdo_tableexists("longbing_cardauth2_config");
			if( $checkExists ) 
			{
				$auth_info = pdo_get("longbing_cardauth2_config", array( "modular_id" => $_W["uniacid"] ));
				if( $auth_info && $auth_info["appoint"] == 0 ) 
				{
					$appoint = false;
				}
				else 
				{
					$appoint = true;
				}
			}
			else 
			{
				$appoint = true;
			}
		}
	}
}
$data["config"]["plugin"]["payqr"] = 0;
$check_plugin = pdo_get("modules_plugin", array( "name" => "longbing_card_plugin_pay_qr", "main_module" => "longbing_card" ));
if( $check_plugin ) 
{
	$check_table = pdo_tableexists("lb_appoint_record_check");
	if( !empty($check_table) ) 
	{
		$checkExists = pdo_tableexists("longbing_cardauth2_config");
		if( !empty($checkExists) ) 
		{
			$auth_info = pdo_get("longbing_cardauth2_config", array( "modular_id" => $_W["uniacid"] ));
			if( $auth_info && $auth_info["payqr"] == 0 ) 
			{
				$payqr = false;
			}
			else 
			{
				$payqr = true;
			}
		}
		else 
		{
			$payqr = true;
		}
	}
}
if( $payqr ) 
{
	$data["config"]["plugin"]["payqr"] = 1;
}
if( $appoint ) 
{
	$tmp = array( "pagePath" => $info["menu_appoint_url"], "text" => $info["menu_appoint_name"], "currentTabBar" => "toReserve", "iconPath" => "/longbing_card/resource/icon/icon-reserve.png", "selectedIconPath" => "/longbing_card/resource/icon/icon-reserve-cur.png", "method" => "redirectTo" );
	array_push($list, $tmp);
	$data["config"]["plugin"]["appoint"] = 1;
}
if( $info["menu2_is_hide"] ) 
{
	$tmp = array( "pagePath" => ($info["menu2_url_out"] == "" ? "" : $info["menu2_url_out"]), "pagePath2" => "/longbing_card/pages/shop/index/index", "text" => $info["menu2_name"], "currentTabBar" => "toShop", "iconPath" => "/longbing_card/resource/icon/icon-shop.png", "selectedIconPath" => "/longbing_card/resource/icon/icon-shop-cur.png", "method" => "reLaunch" );
	array_push($list, $tmp);
}
if( $info["menu3_is_hide"] ) 
{
	$tmp = array( "pagePath" => ($info["menu3_url_out"] == "" ? "" : $info["menu3_url_out"]), "text" => $info["menu3_name"], "currentTabBar" => "toNews", "iconPath" => "/longbing_card/resource/icon/icon-news.png", "selectedIconPath" => "/longbing_card/resource/icon/icon-news-cur.png", "method" => "reLaunch" );
	array_push($list, $tmp);
}
if( $info["menu4_is_hide"] ) 
{
	$tmp = array( "pagePath" => ($info["menu4_url_out"] == "" ? "" : $info["menu4_url_out"]), "text" => $info["menu4_name"], "currentTabBar" => "toCompany", "iconPath" => "/longbing_card/resource/icon/icon-company.png", "selectedIconPath" => "/longbing_card/resource/icon/icon-company-cur.png", "method" => "reLaunch" );
	array_push($list, $tmp);
}
$tabBar["list"] = $list;
$data["tabBar"] = $tabBar;
if( $this->redis_sup_v3 ) 
{
	$redis_key = "longbing_card_configv4_" . $_W["uniacid"];
	$this->redis_server_v3->set($redis_key, json_encode($data));
	$this->redis_server_v3->EXPIRE($redis_key, 60 * 120);
}
return $this->result(0, "suc", $data);
?>