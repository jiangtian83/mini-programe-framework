<?php  namespace Qiniu;
final class Zone 
{
	public $srcUpHosts = NULL;
	public $cdnUpHosts = NULL;
	public $rsHost = NULL;
	public $rsfHost = NULL;
	public $apiHost = NULL;
	public $iovipHost = NULL;
	public function __construct($srcUpHosts = array( ), $cdnUpHosts = array( ), $rsHost = "rs.qiniu.com", $rsfHost = "rsf.qiniu.com", $apiHost = "api.qiniu.com", $iovipHost = NULL) 
	{
		$this->srcUpHosts = $srcUpHosts;
		$this->cdnUpHosts = $cdnUpHosts;
		$this->rsHost = $rsHost;
		$this->rsfHost = $rsfHost;
		$this->apiHost = $apiHost;
		$this->iovipHost = $iovipHost;
	}
	public static function zone0() 
	{
		$Zone_z0 = new Zone(array( "up.qiniup.com", "up-jjh.qiniup.com", "up-xs.qiniup.com" ), array( "upload.qiniup.com", "upload-jjh.qiniup.com", "upload-xs.qiniup.com" ), "rs.qbox.me", "rsf.qbox.me", "api.qiniu.com", "iovip.qbox.me");
		return $Zone_z0;
	}
	public static function zoneZ0() 
	{
		$Zone_z01 = new Zone(array( "free-qvm-z0-xs.qiniup.com" ), "rs.qbox.me", "rsf.qbox.me", "api.qiniu.com", "iovip.qbox.me");
		return $Zone_z01;
	}
	public static function zoneZ1() 
	{
		$Zone_z12 = new Zone(array( "free-qvm-z1-zz.qiniup.com" ), "rs-z1.qbox.me", "rsf-z1.qbox.me", "api-z1.qiniu.com", "iovip-z1.qbox.me");
		return $Zone_z12;
	}
	public static function zone1() 
	{
		$Zone_z1 = new Zone(array( "up-z1.qiniup.com" ), array( "upload-z1.qiniup.com" ), "rs-z1.qbox.me", "rsf-z1.qbox.me", "api-z1.qiniu.com", "iovip-z1.qbox.me");
		return $Zone_z1;
	}
	public static function zone2() 
	{
		$Zone_z2 = new Zone(array( "up-z2.qiniup.com", "up-dg.qiniup.com", "up-fs.qiniup.com" ), array( "upload-z2.qiniup.com", "upload-dg.qiniup.com", "upload-fs.qiniup.com" ), "rs-z2.qbox.me", "rsf-z2.qbox.me", "api-z2.qiniu.com", "iovip-z2.qbox.me");
		return $Zone_z2;
	}
	public static function zoneNa0() 
	{
		$Zone_na0 = new Zone(array( "up-na0.qiniup.com" ), array( "upload-na0.qiniup.com" ), "rs-na0.qbox.me", "rsf-na0.qbox.me", "api-na0.qiniu.com", "iovip-na0.qbox.me");
		return $Zone_na0;
	}
	public static function zoneAs0() 
	{
		$Zone_as0 = new Zone(array( "up-as0.qiniup.com" ), array( "upload-as0.qiniup.com" ), "rs-as0.qbox.me", "rsf-as0.qbox.me", "api-as0.qiniu.com", "iovip-as0.qbox.me");
		return $Zone_as0;
	}
	public static function queryZone($ak, $bucket) 
	{
		$zone = new Zone();
		$url = Config::UC_HOST . "/v2/query" . "?ak=" . $ak . "&bucket=" . $bucket;
		$ret = Http\Client::Get($url);
		if( !$ret->ok() ) 
		{
			return array( null, new Http\Error($url, $ret) );
		}
		$r = ($ret->body === null ? array( ) : $ret->json());
		$iovipHost = $r["io"]["src"]["main"][0];
		$zone->iovipHost = $iovipHost;
		$accMain = $r["up"]["acc"]["main"][0];
		array_push($zone->cdnUpHosts, $accMain);
		if( isset($r["up"]["acc"]["backup"]) ) 
		{
			foreach( $r["up"]["acc"]["backup"] as $key => $value ) 
			{
				array_push($zone->cdnUpHosts, $value);
			}
		}
		$srcMain = $r["up"]["src"]["main"][0];
		array_push($zone->srcUpHosts, $srcMain);
		if( isset($r["up"]["src"]["backup"]) ) 
		{
			foreach( $r["up"]["src"]["backup"] as $key => $value ) 
			{
				array_push($zone->srcUpHosts, $value);
			}
		}
		if( strstr($zone->iovipHost, "z1") !== false ) 
		{
			$zone->rsHost = "rs-z1.qbox.me";
			$zone->rsfHost = "rsf-z1.qbox.me";
			$zone->apiHost = "api-z1.qiniu.com";
		}
		else 
		{
			if( strstr($zone->iovipHost, "z2") !== false ) 
			{
				$zone->rsHost = "rs-z2.qbox.me";
				$zone->rsfHost = "rsf-z2.qbox.me";
				$zone->apiHost = "api-z2.qiniu.com";
			}
			else 
			{
				if( strstr($zone->iovipHost, "na0") !== false ) 
				{
					$zone->rsHost = "rs-na0.qbox.me";
					$zone->rsfHost = "rsf-na0.qbox.me";
					$zone->apiHost = "api-na0.qiniu.com";
				}
				else 
				{
					if( strstr($zone->iovipHost, "as0") !== false ) 
					{
						$zone->rsHost = "rs-as0.qbox.me";
						$zone->rsfHost = "rsf-as0.qbox.me";
						$zone->apiHost = "api-as0.qiniu.com";
					}
					else 
					{
						$zone->rsHost = "rs.qbox.me";
						$zone->rsfHost = "rsf.qbox.me";
						$zone->apiHost = "api.qiniu.com";
					}
				}
			}
		}
		return $zone;
	}
}
?>