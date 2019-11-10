<?php  global $_GPC;
global $_W;
$data = array( "tabBar" => array( "color" => "#838591", "selectedColor" => "#e93636", "backgroundColor" => "#fff", "borderStyle" => "white", "list" => array( array( "pagePath" => "", "text" => "名片", "currentTabBar" => "toCard", "iconPath" => "/longbing_card/resource/icon/icon-card.png", "selectedIconPath" => "/longbing_card/resource/icon/icon-card-cur.png", "method" => "reLaunch" ), array( "pagePath" => "/longbing_card/reserve/pages/index/index", "text" => "预约", "iconPath" => "/longbing_card/resource/icon/icon-reserve.png", "selectedIconPath" => "/longbing_card/resource/icon/icon-reserve-cur.png", "method" => "redirectTo" ), array( "pagePath" => "", "text" => "商城", "currentTabBar" => "toShop", "iconPath" => "/longbing_card/resource/icon/icon-shop.png", "selectedIconPath" => "/longbing_card/resource/icon/icon-shop-cur.png", "method" => "reLaunch" ), array( "pagePath" => "", "text" => "动态", "currentTabBar" => "toNews", "iconPath" => "/longbing_card/resource/icon/icon-news.png", "selectedIconPath" => "/longbing_card/resource/icon/icon-news-cur.png", "method" => "reLaunch" ), array( "pagePath" => "", "text" => "官网", "currentTabBar" => "toCompany", "iconPath" => "/longbing_card/resource/icon/icon-company.png", "selectedIconPath" => "/longbing_card/resource/icon/icon-company-cur.png", "method" => "reLaunch" ) ) ) );
$list = array( );
$info = pdo_get("longbing_card_tabbar", array( "uniacid" => $_W["uniacid"] ));
if( !$info ) 
{
	return $this->result(0, "", $data);
}
if( $info["menu1_is_hide"] ) 
{
	$tmp = array( "pagePath" => "", "text" => $info["menu1_name"], "currentTabBar" => "toCard", "iconPath" => "/longbing_card/resource/icon/icon-card.png", "selectedIconPath" => "/longbing_card/resource/icon/icon-card-cur.png", "method" => "reLaunch" );
	array_push($list, $tmp);
}
if( $info["menu_appoint_is_hide"] ) 
{
	$tmp = array( "pagePath" => $info["menu_appoint_url"], "text" => $info["menu_appoint_name"], "iconPath" => "/longbing_card/resource/icon/icon-reserve.png", "selectedIconPath" => "/longbing_card/resource/icon/icon-reserve-cur.png", "method" => "redirectTo" );
	array_push($list, $tmp);
}
if( $info["menu2_is_hide"] ) 
{
	$tmp = array( "pagePath" => $info["menu2_url"], "text" => $info["menu2_name"], "currentTabBar" => "toShop", "iconPath" => "/longbing_card/resource/icon/icon-shop.png", "selectedIconPath" => "/longbing_card/resource/icon/icon-shop-cur.png", "method" => "reLaunch" );
	array_push($list, $tmp);
}
if( $info["menu3_is_hide"] ) 
{
	$tmp = array( "pagePath" => $info["menu3_url"], "text" => $info["menu3_name"], "currentTabBar" => "toNews", "iconPath" => "/longbing_card/resource/icon/icon-news.png", "selectedIconPath" => "/longbing_card/resource/icon/icon-news-cur.png", "method" => "reLaunch" );
	array_push($list, $tmp);
}
if( $info["menu4_is_hide"] ) 
{
	$tmp = array( "pagePath" => $info["menu4_url"], "text" => $info["menu4_name"], "currentTabBar" => "toCompany", "iconPath" => "/longbing_card/resource/icon/icon-company.png", "selectedIconPath" => "/longbing_card/resource/icon/icon-company-cur.png", "method" => "reLaunch" );
	array_push($list, $tmp);
}
$data["tabBar"]["list"] = $list;
return $this->result(0, "", $data);
?>