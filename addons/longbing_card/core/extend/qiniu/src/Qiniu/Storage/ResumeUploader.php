<?php  namespace Qiniu\Storage;
final class ResumeUploader 
{
	private $upToken = NULL;
	private $key = NULL;
	private $inputStream = NULL;
	private $size = NULL;
	private $params = NULL;
	private $mime = NULL;
	private $contexts = NULL;
	private $host = NULL;
	private $currentUrl = NULL;
	private $config = NULL;
	public function __construct($upToken, $key, $inputStream, $size, $params, $mime, $config) 
	{
		$this->upToken = $upToken;
		$this->key = $key;
		$this->inputStream = $inputStream;
		$this->size = $size;
		$this->params = $params;
		$this->mime = $mime;
		$this->contexts = array( );
		$this->config = $config;
		list($accessKey, $bucket, $err) = Qiniu\explodeUpToken($upToken);
		if( $err != null ) 
		{
			return array( null, $err );
		}
		$upHost = $config->getUpHost($accessKey, $bucket);
		if( $err != null ) 
		{
			throw new \Exception($err->message(), 1);
		}
		$this->host = $upHost;
	}
	public function upload($fname) 
	{
		$uploaded = 0;
		while( $uploaded < $this->size ) 
		{
			$blockSize = $this->blockSize($uploaded);
			$data = fread($this->inputStream, $blockSize);
			if( $data === false ) 
			{
				throw new \Exception("file read failed", 1);
			}
			$crc = Qiniu\crc32_data($data);
			$response = $this->makeBlock($data, $blockSize);
			$ret = null;
			if( $response->ok() && $response->json() != null ) 
			{
				$ret = $response->json();
			}
			if( $response->statusCode < 0 ) 
			{
				list($accessKey, $bucket, $err) = Qiniu\explodeUpToken($this->upToken);
				if( $err != null ) 
				{
					return array( null, $err );
				}
				$upHostBackup = $this->config->getUpBackupHost($accessKey, $bucket);
				$this->host = $upHostBackup;
			}
			if( $response->needRetry() || !isset($ret["crc32"]) || $crc != $ret["crc32"] ) 
			{
				$response = $this->makeBlock($data, $blockSize);
				$ret = $response->json();
			}
			if( !$response->ok() || !isset($ret["crc32"]) || $crc != $ret["crc32"] ) 
			{
				return array( null, new \Qiniu\Http\Error($this->currentUrl, $response) );
			}
			array_push($this->contexts, $ret["ctx"]);
			$uploaded += $blockSize;
		}
		return $this->makeFile($fname);
	}
	private function makeBlock($block, $blockSize) 
	{
		$url = $this->host . "/mkblk/" . $blockSize;
		return $this->post($url, $block);
	}
	private function fileUrl($fname) 
	{
		$url = $this->host . "/mkfile/" . $this->size;
		$url .= "/mimeType/" . Qiniu\base64_urlSafeEncode($this->mime);
		if( $this->key != null ) 
		{
			$url .= "/key/" . Qiniu\base64_urlSafeEncode($this->key);
		}
		$url .= "/fname/" . Qiniu\base64_urlSafeEncode($fname);
		if( !empty($this->params) ) 
		{
			foreach( $this->params as $key => $value ) 
			{
				$val = Qiniu\base64_urlSafeEncode($value);
				$url .= "/" . $key . "/" . $val;
			}
		}
		return $url;
	}
	private function makeFile($fname) 
	{
		$url = $this->fileUrl($fname);
		$body = implode(",", $this->contexts);
		$response = $this->post($url, $body);
		if( $response->needRetry() ) 
		{
			$response = $this->post($url, $body);
		}
		if( !$response->ok() ) 
		{
			return array( null, new \Qiniu\Http\Error($this->currentUrl, $response) );
		}
		return array( $response->json(), null );
	}
	private function post($url, $data) 
	{
		$this->currentUrl = $url;
		$headers = array( "Authorization" => "UpToken " . $this->upToken );
		return \Qiniu\Http\Client::post($url, $data, $headers);
	}
	private function blockSize($uploaded) 
	{
		if( $this->size < $uploaded + \Qiniu\Config::BLOCK_SIZE ) 
		{
			return $this->size - $uploaded;
		}
		return \Qiniu\Config::BLOCK_SIZE;
	}
}
?>