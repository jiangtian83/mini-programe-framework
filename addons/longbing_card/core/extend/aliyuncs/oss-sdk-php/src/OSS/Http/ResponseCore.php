<?php  namespace OSS\Http;
class ResponseCore 
{
	public $header = NULL;
	public $body = NULL;
	public $status = NULL;
	public function __construct($header, $body, $status = NULL) 
	{
		$this->header = $header;
		$this->body = $body;
		$this->status = $status;
		return $this;
	}
	public function isOK($codes = array( )) 
	{
		if( is_array($codes) ) 
		{
			return in_array($this->status, $codes);
		}
		return $this->status === $codes;
	}
}
?>