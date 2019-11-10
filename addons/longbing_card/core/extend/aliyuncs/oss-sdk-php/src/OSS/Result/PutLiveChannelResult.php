<?php  namespace OSS\Result;
class PutLiveChannelResult extends Result 
{
	protected function parseDataFromResponse() 
	{
		$content = $this->rawResponse->body;
		$channel = new \OSS\Model\LiveChannelInfo();
		$channel->parseFromXml($content);
		return $channel;
	}
}
?>