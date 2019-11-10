<?php  namespace OSS\Result;
class GetLiveChannelStatusResult extends Result 
{
	protected function parseDataFromResponse() 
	{
		$content = $this->rawResponse->body;
		$channelList = new \OSS\Model\GetLiveChannelStatus();
		$channelList->parseFromXml($content);
		return $channelList;
	}
}
?>