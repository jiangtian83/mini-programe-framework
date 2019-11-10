<?php  namespace Qiniu\Storage;
final class BucketManager 
{
	private $auth = NULL;
	private $config = NULL;
	public function __construct(\Qiniu\Auth $auth, \Qiniu\Config $config = NULL) 
	{
		$this->auth = $auth;
		if( $config == null ) 
		{
			$this->config = new \Qiniu\Config();
		}
		else 
		{
			$this->config = $config;
		}
	}
	public function buckets($shared = true) 
	{
		$includeShared = "false";
		if( $shared === true ) 
		{
			$includeShared = "true";
		}
		return $this->rsGet("/buckets?shared=" . $includeShared);
	}
	public function domains($bucket) 
	{
		return $this->apiGet("/v6/domain/list?tbl=" . $bucket);
	}
	public function listFiles($bucket, $prefix = NULL, $marker = NULL, $limit = 1000, $delimiter = NULL) 
	{
		$query = array( "bucket" => $bucket );
		Qiniu\setWithoutEmpty($query, "prefix", $prefix);
		Qiniu\setWithoutEmpty($query, "marker", $marker);
		Qiniu\setWithoutEmpty($query, "limit", $limit);
		Qiniu\setWithoutEmpty($query, "delimiter", $delimiter);
		$url = $this->getRsfHost() . "/list?" . http_build_query($query);
		return $this->get($url);
	}
	public function stat($bucket, $key) 
	{
		$path = "/stat/" . Qiniu\entry($bucket, $key);
		return $this->rsGet($path);
	}
	public function delete($bucket, $key) 
	{
		$path = "/delete/" . Qiniu\entry($bucket, $key);
		list(, $error) = $this->rsPost($path);
		return $error;
	}
	public function rename($bucket, $oldname, $newname) 
	{
		return $this->move($bucket, $oldname, $bucket, $newname);
	}
	public function copy($from_bucket, $from_key, $to_bucket, $to_key, $force = false) 
	{
		$from = Qiniu\entry($from_bucket, $from_key);
		$to = Qiniu\entry($to_bucket, $to_key);
		$path = "/copy/" . $from . "/" . $to;
		if( $force === true ) 
		{
			$path .= "/force/true";
		}
		list(, $error) = $this->rsPost($path);
		return $error;
	}
	public function move($from_bucket, $from_key, $to_bucket, $to_key, $force = false) 
	{
		$from = Qiniu\entry($from_bucket, $from_key);
		$to = Qiniu\entry($to_bucket, $to_key);
		$path = "/move/" . $from . "/" . $to;
		if( $force ) 
		{
			$path .= "/force/true";
		}
		list(, $error) = $this->rsPost($path);
		return $error;
	}
	public function changeMime($bucket, $key, $mime) 
	{
		$resource = Qiniu\entry($bucket, $key);
		$encode_mime = Qiniu\base64_urlSafeEncode($mime);
		$path = "/chgm/" . $resource . "/mime/" . $encode_mime;
		list(, $error) = $this->rsPost($path);
		return $error;
	}
	public function changeType($bucket, $key, $fileType) 
	{
		$resource = Qiniu\entry($bucket, $key);
		$path = "/chtype/" . $resource . "/type/" . $fileType;
		list(, $error) = $this->rsPost($path);
		return $error;
	}
	public function changeStatus($bucket, $key, $status) 
	{
		$resource = Qiniu\entry($bucket, $key);
		$path = "/chstatus/" . $resource . "/status/" . $status;
		list(, $error) = $this->rsPost($path);
		return $error;
	}
	public function fetch($url, $bucket, $key = NULL) 
	{
		$resource = Qiniu\base64_urlSafeEncode($url);
		$to = Qiniu\entry($bucket, $key);
		$path = "/fetch/" . $resource . "/to/" . $to;
		$ak = $this->auth->getAccessKey();
		$ioHost = $this->config->getIovipHost($ak, $bucket);
		$url = $ioHost . $path;
		return $this->post($url, null);
	}
	public function prefetch($bucket, $key) 
	{
		$resource = Qiniu\entry($bucket, $key);
		$path = "/prefetch/" . $resource;
		$ak = $this->auth->getAccessKey();
		$ioHost = $this->config->getIovipHost($ak, $bucket);
		$url = $ioHost . $path;
		list(, $error) = $this->post($url, null);
		return $error;
	}
	public function batch($operations) 
	{
		$params = "op=" . implode("&op=", $operations);
		return $this->rsPost("/batch", $params);
	}
	public function deleteAfterDays($bucket, $key, $days) 
	{
		$entry = Qiniu\entry($bucket, $key);
		$path = "/deleteAfterDays/" . $entry . "/" . $days;
		list(, $error) = $this->rsPost($path);
		return $error;
	}
	private function getRsfHost() 
	{
		$scheme = "http://";
		if( $this->config->useHTTPS == true ) 
		{
			$scheme = "https://";
		}
		return $scheme . \Qiniu\Config::RSF_HOST;
	}
	private function getRsHost() 
	{
		$scheme = "http://";
		if( $this->config->useHTTPS == true ) 
		{
			$scheme = "https://";
		}
		return $scheme . \Qiniu\Config::RS_HOST;
	}
	private function getApiHost() 
	{
		$scheme = "http://";
		if( $this->config->useHTTPS == true ) 
		{
			$scheme = "https://";
		}
		return $scheme . \Qiniu\Config::API_HOST;
	}
	private function rsPost($path, $body = NULL) 
	{
		$url = $this->getRsHost() . $path;
		return $this->post($url, $body);
	}
	private function apiGet($path) 
	{
		$url = $this->getApiHost() . $path;
		return $this->get($url);
	}
	private function rsGet($path) 
	{
		$url = $this->getRsHost() . $path;
		return $this->get($url);
	}
	private function get($url) 
	{
		$headers = $this->auth->authorization($url);
		$ret = \Qiniu\Http\Client::get($url, $headers);
		if( !$ret->ok() ) 
		{
			return array( null, new \Qiniu\Http\Error($url, $ret) );
		}
		return array( $ret->json(), null );
	}
	private function post($url, $body) 
	{
		$headers = $this->auth->authorization($url, $body, "application/x-www-form-urlencoded");
		$ret = \Qiniu\Http\Client::post($url, $body, $headers);
		if( !$ret->ok() ) 
		{
			return array( null, new \Qiniu\Http\Error($url, $ret) );
		}
		$r = ($ret->body === null ? array( ) : $ret->json());
		return array( $r, null );
	}
	public static function buildBatchCopy($source_bucket, $key_pairs, $target_bucket, $force) 
	{
		return self::twoKeyBatch("/copy", $source_bucket, $key_pairs, $target_bucket, $force);
	}
	public static function buildBatchRename($bucket, $key_pairs, $force) 
	{
		return self::buildBatchMove($bucket, $key_pairs, $bucket, $force);
	}
	public static function buildBatchMove($source_bucket, $key_pairs, $target_bucket, $force) 
	{
		return self::twoKeyBatch("/move", $source_bucket, $key_pairs, $target_bucket, $force);
	}
	public static function buildBatchDelete($bucket, $keys) 
	{
		return self::oneKeyBatch("/delete", $bucket, $keys);
	}
	public static function buildBatchStat($bucket, $keys) 
	{
		return self::oneKeyBatch("/stat", $bucket, $keys);
	}
	public static function buildBatchDeleteAfterDays($bucket, $key_day_pairs) 
	{
		$data = array( );
		foreach( $key_day_pairs as $key => $day ) 
		{
			array_push($data, "/deleteAfterDays/" . Qiniu\entry($bucket, $key) . "/" . $day);
		}
		return $data;
	}
	public static function buildBatchChangeMime($bucket, $key_mime_pairs) 
	{
		$data = array( );
		foreach( $key_mime_pairs as $key => $mime ) 
		{
			array_push($data, "/chgm/" . Qiniu\entry($bucket, $key) . "/mime/" . base64_encode($mime));
		}
		return $data;
	}
	public static function buildBatchChangeType($bucket, $key_type_pairs) 
	{
		$data = array( );
		foreach( $key_type_pairs as $key => $type ) 
		{
			array_push($data, "/chtype/" . Qiniu\entry($bucket, $key) . "/type/" . $type);
		}
		return $data;
	}
	private static function oneKeyBatch($operation, $bucket, $keys) 
	{
		$data = array( );
		foreach( $keys as $key ) 
		{
			array_push($data, $operation . "/" . Qiniu\entry($bucket, $key));
		}
		return $data;
	}
	private static function twoKeyBatch($operation, $source_bucket, $key_pairs, $target_bucket, $force) 
	{
		if( $target_bucket === null ) 
		{
			$target_bucket = $source_bucket;
		}
		$data = array( );
		$forceOp = "false";
		if( $force ) 
		{
			$forceOp = "true";
		}
		foreach( $key_pairs as $from_key => $to_key ) 
		{
			$from = Qiniu\entry($source_bucket, $from_key);
			$to = Qiniu\entry($target_bucket, $to_key);
			array_push($data, $operation . "/" . $from . "/" . $to . "/force/" . $forceOp);
		}
		return $data;
	}
}
?>