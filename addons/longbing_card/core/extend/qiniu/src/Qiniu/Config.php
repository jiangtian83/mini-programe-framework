<?php  namespace Qiniu;
final class Config 
{
	public $zone = NULL;
	public $useHTTPS = NULL;
	public $useCdnDomains = NULL;
	private $zoneCache = NULL;
	const SDK_VER = "7.2.7";
	const BLOCK_SIZE = 4194304;
	const RSF_HOST = "rsf.qiniu.com";
	const API_HOST = "api.qiniu.com";
	const RS_HOST = "rs.qiniu.com";
	const UC_HOST = "https://api.qiniu.com";
	const RTCAPI_HOST = "http://rtc.qiniuapi.com";
	const ARGUS_HOST = "argus.atlab.ai";
	const RTCAPI_VERSION = "v3";
	public function __construct(Zone $z = NULL) 
	{
		$this->zone = $z;
		$this->useHTTPS = false;
		$this->useCdnDomains = false;
		$this->zoneCache = array( );
	}
	public function getUpHost($accessKey, $bucket) 
	{
		$zone = $this->getZone($accessKey, $bucket);
		if( $this->useHTTPS === true ) 
		{
			$scheme = "https://";
		}
		else 
		{
			$scheme = "http://";
		}
		$host = $zone->srcUpHosts[0];
		if( $this->useCdnDomains === true ) 
		{
			$host = $zone->cdnUpHosts[0];
		}
		return $scheme . $host;
	}
	public function getUpBackupHost($accessKey, $bucket) 
	{
		$zone = $this->getZone($accessKey, $bucket);
		if( $this->useHTTPS === true ) 
		{
			$scheme = "https://";
		}
		else 
		{
			$scheme = "http://";
		}
		$host = $zone->cdnUpHosts[0];
		if( $this->useCdnDomains === true ) 
		{
			$host = $zone->srcUpHosts[0];
		}
		return $scheme . $host;
	}
	public function getRsHost($accessKey, $bucket) 
	{
		$zone = $this->getZone($accessKey, $bucket);
		if( $this->useHTTPS === true ) 
		{
			$scheme = "https://";
		}
		else 
		{
			$scheme = "http://";
		}
		return $scheme . $zone->rsHost;
	}
	public function getRsfHost($accessKey, $bucket) 
	{
		$zone = $this->getZone($accessKey, $bucket);
		if( $this->useHTTPS === true ) 
		{
			$scheme = "https://";
		}
		else 
		{
			$scheme = "http://";
		}
		return $scheme . $zone->rsfHost;
	}
	public function getIovipHost($accessKey, $bucket) 
	{
		$zone = $this->getZone($accessKey, $bucket);
		if( $this->useHTTPS === true ) 
		{
			$scheme = "https://";
		}
		else 
		{
			$scheme = "http://";
		}
		return $scheme . $zone->iovipHost;
	}
	public function getApiHost($accessKey, $bucket) 
	{
		$zone = $this->getZone($accessKey, $bucket);
		if( $this->useHTTPS === true ) 
		{
			$scheme = "https://";
		}
		else 
		{
			$scheme = "http://";
		}
		return $scheme . $zone->apiHost;
	}
	private function getZone($accessKey, $bucket) 
	{
		$cacheId = (string) $accessKey . ":" . $bucket;
		if( isset($this->zoneCache[$cacheId]) ) 
		{
			$zone = $this->zoneCache[$cacheId];
		}
		else 
		{
			if( isset($this->zone) ) 
			{
				$zone = $this->zone;
				$this->zoneCache[$cacheId] = $zone;
			}
			else 
			{
				$zone = Zone::queryZone($accessKey, $bucket);
				$this->zoneCache[$cacheId] = $zone;
			}
		}
		return $zone;
	}
}
?>