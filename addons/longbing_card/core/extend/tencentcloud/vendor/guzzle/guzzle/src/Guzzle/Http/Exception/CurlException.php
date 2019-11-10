<?php  namespace Guzzle\Http\Exception;
class CurlException extends RequestException 
{
	private $curlError = NULL;
	private $curlErrorNo = NULL;
	private $handle = NULL;
	private $curlInfo = array( );
	public function setError($error, $number) 
	{
		$this->curlError = $error;
		$this->curlErrorNo = $number;
		return $this;
	}
	public function setCurlHandle(\Guzzle\Http\Curl\CurlHandle $handle) 
	{
		$this->handle = $handle;
		return $this;
	}
	public function getCurlHandle() 
	{
		return $this->handle;
	}
	public function getError() 
	{
		return $this->curlError;
	}
	public function getErrorNo() 
	{
		return $this->curlErrorNo;
	}
	public function getCurlInfo() 
	{
		return $this->curlInfo;
	}
	public function setCurlInfo(array $info) 
	{
		$this->curlInfo = $info;
		return $this;
	}
}
?>