<?php  namespace OSS\Result;
class AppendResult extends Result 
{
	protected function parseDataFromResponse() 
	{
		$header = $this->rawResponse->header;
		if( isset($header["x-oss-next-append-position"]) ) 
		{
			return intval($header["x-oss-next-append-position"]);
		}
		throw new \OSS\Core\OssException("cannot get next-append-position");
	}
}
?>