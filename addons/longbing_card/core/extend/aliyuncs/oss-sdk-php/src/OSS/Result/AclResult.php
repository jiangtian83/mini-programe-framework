<?php  namespace OSS\Result;
class AclResult extends Result 
{
	protected function parseDataFromResponse() 
	{
		$content = $this->rawResponse->body;
		if( empty($content) ) 
		{
			throw new \OSS\Core\OssException("body is null");
		}
		$xml = simplexml_load_string($content);
		if( isset($xml->AccessControlList->Grant) ) 
		{
			return strval($xml->AccessControlList->Grant);
		}
		throw new \OSS\Core\OssException("xml format exception");
	}
}
?>