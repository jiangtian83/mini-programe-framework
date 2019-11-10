<?php  namespace OSS\Result;
class GetLiveChannelHistoryResult extends Result 
{
	protected function parseDataFromResponse() 
	{
		$content = $this->rawResponse->body;
		$channelList = new \OSS\Model\GetLiveChannelHistory();
		$channelList->parseFromXml($content);
		return $channelList;
	}
}
?>