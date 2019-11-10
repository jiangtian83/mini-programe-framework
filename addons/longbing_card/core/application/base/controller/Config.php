<?php  namespace app\base\controller;
defined("ROOT_PATH") or define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
class Config extends BaseApi 
{
	protected $request = NULL;
	protected $user_id = NULL;
	public function __construct(\think\Request $request = NULL) 
	{
		$request = \think\Request::instance();
		$this->request = $request;
		$user_id = $this->user_id = $request->post("user_id");
		if( !$user_id ) 
		{
			resultJsonStr(-1, "请传入用户数据", array( "msg" => "请传入用户数据" . $user_id ));
		}
		parent::__construct($request);
	}
	public function config() 
	{
		$uniacid = $this->request->param("i");
		$configModel = new \app\base\model\BaseConfig();
		$info = $configModel->get(array( "uniacid" => $uniacid ));
		$time = time();
		if( !$info ) 
		{
			$dataConfig = array( "uniacid" => $uniacid, "status" => 1, "create_time" => $time, "update_time" => $time );
			$configModel->data($dataConfig);
			$configModel->save();
			$info = $configModel->get(array( "uniacid" => $uniacid ));
		}
		if( !$info ) 
		{
			resultJsonStr(-1, "未找到系统配置", array( "msg" => "未找到系统配置" ));
		}
		$info["default_video"] = tomedia($info["default_video"]);
		$info["default_voice"] = tomedia($info["default_voice"]);
		$info["appoint_pic"] = tomedia($info["appoint_pic"]);
		$info["click_copy_show_img"] = tomedia($info["click_copy_show_img"]);
		$info["shop_carousel_more"] = tomedia($info["shop_carousel_more"]);
		$info["copyright"] = tomedia($info["copyright"]);
		$info["click_copy_show_img"] = ($info["click_copy_show_img"] ? $info["click_copy_show_img"] : $info["copyright"]);
		if( isset($info["order_pwd"]) ) 
		{
			unset($info["order_pwd"]);
		}
		$info["default_video_cover"] = tomedia($info["default_video_cover"]);
		if( !LONGBING_AUTH_COPYRIGHT ) 
		{
			$info["copyright"] = "https://retail.xiaochengxucms.com/images/12/2018/11/crDXyl3TyBRLUBch6ToqXL6e9D96hY.jpg";
		}
		$checkExists = \think\Db::query("show tables like \"" . config("database.prefix") . "longbing_cardauth2_config\"");
		$cardAuthConfig = new \app\base\model\CardauthConfig();
		if( !empty($checkExists) ) 
		{
			$auth_info = $cardAuthConfig->get(array( "modular_id" => $uniacid ));
			if( $auth_info && $auth_info["copyright_id"] ) 
			{
				$cardAuthCopyright = new \app\base\model\CardauthCopyright();
				$copyright = $cardAuthCopyright->get($auth_info["copyright_id"]);
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
		$data["config"] = $info->toArray();
		$companyModel = new \app\base\model\CardCompany();
		$company = $companyModel->where(array( "uniacid" => $uniacid, "status" => 1 ))->select();
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
		$userInfoModel = new \app\base\model\CardUserInfo();
		$uid = $this->user_id;
		$user = $userInfoModel->where(array( "fans_id" => $uid, "is_staff" => 1, "status" => 1 ))->find();
		if( $user ) 
		{
			if( $user["company_id"] ) 
			{
				$company2 = $companyModel->get($user["company_id"]);
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
		$tabBarModel = new \app\base\model\CardTabBar();
		$info = $tabBarModel->get(array( "uniacid" => $uniacid ));
		if( !$info ) 
		{
			$data["tabBar"] = $tabBar;
			return $this->result(0, "suc", $data);
		}
		if( $info["menu1_is_hide"] ) 
		{
			$tmp = array( "pagePath" => "", "text" => $info["menu1_name"], "currentTabBar" => "toCard", "iconPath" => "/longbing_card/resource/icon/icon-card.png", "selectedIconPath" => "/longbing_card/resource/icon/icon-card-cur.png", "method" => "reLaunch" );
			array_push($list, $tmp);
		}
		$appoint = false;
		$data["config"]["plugin"]["appoint"] = 0;
		if( $info["menu_appoint_is_hide"] ) 
		{
			$check_plugin = \think\Db::query("SELECT * FROM " . config("database.prefix") . "modules_plugin \r\n            WHERE `name` = \"longbing_card_plugin_yuyue2\" \r\n                && `main_module` = \"longbing_card\"");
			if( $check_plugin ) 
			{
				$check_table = \think\Db::query("show tables like \"" . config("database.prefix") . "lb_appoint_record_check\"");
				if( !empty($check_table) ) 
				{
					$checkExists = \think\Db::query("show tables like \"" . config("database.prefix") . "longbing_cardauth2_config\"");
					if( !empty($checkExists) ) 
					{
						$auth_info = $cardAuthConfig->get(array( "modular_id" => $uniacid ));
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
		$data["a_is_new"] = 1;
		$payqr = false;
		$data["config"]["plugin"]["payqr"] = 0;
		$check_plugin = \think\Db::query("SELECT * FROM " . config("database.prefix") . "modules_plugin \r\n            WHERE `name` = \"longbing_card_plugin_pay_qr\" \r\n                && `main_module` = \"longbing_card\"");
		if( $check_plugin ) 
		{
			$check_table = \think\Db::query("show tables like \"" . config("database.prefix") . "lb_appoint_record_check\"");
			if( !empty($check_table) ) 
			{
				$checkExists = \think\Db::query("show tables like \"" . config("database.prefix") . "longbing_cardauth2_config\"");
				if( !empty($checkExists) ) 
				{
					$auth_info = $cardAuthConfig->get(array( "modular_id" => $uniacid ));
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
		resultJsonStr($this->error_array["error0"], "suc", $data);
	}
}
?>