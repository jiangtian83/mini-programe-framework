<?php  namespace Qiniu\Processing;
final class ImageUrlBuilder 
{
	protected $modeArr = array( 0, 1, 2, 3, 4, 5 );
	protected $formatArr = array( "psd", "jpeg", "png", "gif", "webp", "tiff", "bmp" );
	protected $gravityArr = array( "NorthWest", "North", "NorthEast", "West", "Center", "East", "SouthWest", "South", "SouthEast" );
	public function thumbnail($url, $mode, $width, $height, $format = NULL, $interlace = NULL, $quality = NULL, $ignoreError = 1) 
	{
		if( !$this->isUrl($url) ) 
		{
			return $url;
		}
		if( !in_array(intval($mode), $this->modeArr, true) ) 
		{
			return $url;
		}
		if( !$width || !$height ) 
		{
			return $url;
		}
		$thumbStr = "imageView2/" . $mode . "/w/" . $width . "/h/" . $height . "/";
		if( !is_null($format) && in_array($format, $this->formatArr) ) 
		{
			$thumbStr .= "format/" . $format . "/";
		}
		if( !is_null($interlace) && in_array(intval($interlace), array( 0, 1 ), true) ) 
		{
			$thumbStr .= "interlace/" . $interlace . "/";
		}
		if( !is_null($quality) && 0 <= intval($quality) && intval($quality) <= 100 ) 
		{
			$thumbStr .= "q/" . $quality . "/";
		}
		$thumbStr .= "ignore-error/" . $ignoreError . "/";
		return $url . (($this->hasQuery($url) ? "|" : "?")) . $thumbStr;
	}
	public function waterImg($url, $image, $dissolve = 100, $gravity = "SouthEast", $dx = NULL, $dy = NULL, $watermarkScale = NULL) 
	{
		if( !$this->isUrl($url) ) 
		{
			return $url;
		}
		$waterStr = "watermark/1/image/" . Qiniu\base64_urlSafeEncode($image) . "/";
		if( is_numeric($dissolve) && $dissolve <= 100 ) 
		{
			$waterStr .= "dissolve/" . $dissolve . "/";
		}
		if( in_array($gravity, $this->gravityArr, true) ) 
		{
			$waterStr .= "gravity/" . $gravity . "/";
		}
		if( !is_null($dx) && is_numeric($dx) ) 
		{
			$waterStr .= "dx/" . $dx . "/";
		}
		if( !is_null($dy) && is_numeric($dy) ) 
		{
			$waterStr .= "dy/" . $dy . "/";
		}
		if( !is_null($watermarkScale) && is_numeric($watermarkScale) && 0 < $watermarkScale && $watermarkScale < 1 ) 
		{
			$waterStr .= "ws/" . $watermarkScale . "/";
		}
		return $url . (($this->hasQuery($url) ? "|" : "?")) . $waterStr;
	}
	public function waterText($url, $text, $font = "黑体", $fontSize = 0, $fontColor = NULL, $dissolve = 100, $gravity = "SouthEast", $dx = NULL, $dy = NULL) 
	{
		if( !$this->isUrl($url) ) 
		{
			return $url;
		}
		$waterStr = "watermark/2/text/" . Qiniu\base64_urlSafeEncode($text) . "/font/" . Qiniu\base64_urlSafeEncode($font) . "/";
		if( is_int($fontSize) ) 
		{
			$waterStr .= "fontsize/" . $fontSize . "/";
		}
		if( !is_null($fontColor) && $fontColor ) 
		{
			$waterStr .= "fill/" . Qiniu\base64_urlSafeEncode($fontColor) . "/";
		}
		if( is_numeric($dissolve) && $dissolve <= 100 ) 
		{
			$waterStr .= "dissolve/" . $dissolve . "/";
		}
		if( in_array($gravity, $this->gravityArr, true) ) 
		{
			$waterStr .= "gravity/" . $gravity . "/";
		}
		if( !is_null($dx) && is_numeric($dx) ) 
		{
			$waterStr .= "dx/" . $dx . "/";
		}
		if( !is_null($dy) && is_numeric($dy) ) 
		{
			$waterStr .= "dy/" . $dy . "/";
		}
		return $url . (($this->hasQuery($url) ? "|" : "?")) . $waterStr;
	}
	protected function isUrl($url) 
	{
		$urlArr = parse_url($url);
		return $urlArr["scheme"] && in_array($urlArr["scheme"], array( "http", "https" )) && $urlArr["host"] && $urlArr["path"];
	}
	protected function hasQuery($url) 
	{
		$urlArr = parse_url($url);
		return !empty($urlArr["query"]);
	}
}
?>