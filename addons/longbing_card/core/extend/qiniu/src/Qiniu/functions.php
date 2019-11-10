<?php  if( !Qiniu\defined("QINIU_FUNCTIONS_VERSION") ) 
{
	Qiniu\define("QINIU_FUNCTIONS_VERSION", Qiniu\Config::SDK_VER);
	function Qiniu\crc32_file($file) 
	{
		$hash = Qiniu\hash_file("crc32b", $file);
		$array = Qiniu\unpack("N", Qiniu\pack("H*", $hash));
		return Qiniu\sprintf("%u", $array[1]);
	}
	function Qiniu\crc32_data($data) 
	{
		$hash = Qiniu\hash("crc32b", $data);
		$array = Qiniu\unpack("N", Qiniu\pack("H*", $hash));
		return Qiniu\sprintf("%u", $array[1]);
	}
	function Qiniu\base64_urlSafeEncode($data) 
	{
		$find = array( "+", "/" );
		$replace = array( "-", "_" );
		return Qiniu\str_replace($find, $replace, Qiniu\base64_encode($data));
	}
	function Qiniu\base64_urlSafeDecode($str) 
	{
		$find = array( "-", "_" );
		$replace = array( "+", "/" );
		return Qiniu\base64_decode(Qiniu\str_replace($find, $replace, $str));
	}
	function Qiniu\json_decode($json, $assoc = false, $depth = 512) 
	{
		static $jsonErrors = NULL;
		if( empty($json) ) 
		{
			return Qiniu\\null;
		}
		$data = json_decode($json, $assoc, $depth);
		if( Qiniu\\JSON_ERROR_NONE !== Qiniu\json_last_error() ) 
		{
			$last = Qiniu\json_last_error();
			throw new InvalidArgumentException("Unable to parse JSON data: " . ((isset($jsonErrors[$last]) ? $jsonErrors[$last] : "Unknown error")));
		}
		return $data;
	}
	function Qiniu\entry($bucket, $key) 
	{
		$en = $bucket;
		if( !empty($key) ) 
		{
			$en = $bucket . ":" . $key;
		}
		return Qiniu\base64_urlSafeEncode($en);
	}
	function Qiniu\setWithoutEmpty(&$array, $key, $value) 
	{
		if( !empty($value) ) 
		{
			$array[$key] = $value;
		}
		return $array;
	}
	function Qiniu\thumbnail($url, $mode, $width, $height, $format = NULL, $quality = NULL, $interlace = NULL, $ignoreError = 1) 
	{
		static $imageUrlBuilder = NULL;
		if( Qiniu\is_null($imageUrlBuilder) ) 
		{
			$imageUrlBuilder = new Qiniu\Processing\ImageUrlBuilder();
		}
		return Qiniu\call_user_func_array(array( $imageUrlBuilder, "thumbnail" ), Qiniu\func_get_args());
	}
	function Qiniu\waterImg($url, $image, $dissolve = 100, $gravity = "SouthEast", $dx = NULL, $dy = NULL, $watermarkScale = NULL) 
	{
		static $imageUrlBuilder = NULL;
		if( Qiniu\is_null($imageUrlBuilder) ) 
		{
			$imageUrlBuilder = new Qiniu\Processing\ImageUrlBuilder();
		}
		return Qiniu\call_user_func_array(array( $imageUrlBuilder, "waterImg" ), Qiniu\func_get_args());
	}
	function Qiniu\waterText($url, $text, $font = "黑体", $fontSize = 0, $fontColor = NULL, $dissolve = 100, $gravity = "SouthEast", $dx = NULL, $dy = NULL) 
	{
		static $imageUrlBuilder = NULL;
		if( Qiniu\is_null($imageUrlBuilder) ) 
		{
			$imageUrlBuilder = new Qiniu\Processing\ImageUrlBuilder();
		}
		return Qiniu\call_user_func_array(array( $imageUrlBuilder, "waterText" ), Qiniu\func_get_args());
	}
	function Qiniu\explodeUpToken($upToken) 
	{
		$items = Qiniu\explode(":", $upToken);
		if( Qiniu\count($items) != 3 ) 
		{
			return array( Qiniu\\null, Qiniu\\null, "invalid uptoken" );
		}
		$accessKey = $items[0];
		$putPolicy = Qiniu\json_decode(Qiniu\base64_urlSafeDecode($items[2]));
		$scope = $putPolicy->scope;
		$scopeItems = Qiniu\explode(":", $scope);
		$bucket = $scopeItems[0];
		return array( $accessKey, $bucket, Qiniu\\null );
	}
}
?>