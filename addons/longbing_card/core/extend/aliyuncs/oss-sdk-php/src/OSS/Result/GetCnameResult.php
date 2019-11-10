<?php  namespace OSS\Result;
class GetCnameResult extends Result 
{
	protected function parseDataFromResponse() 
	{
		$content = $this->rawResponse->body;
		$config = new \OSS\Model\CnameConfig();
		$config->parseFromXml($content);
		return $config;
	}
}
?>