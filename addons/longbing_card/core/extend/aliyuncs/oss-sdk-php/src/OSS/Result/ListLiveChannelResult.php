<?php  namespace OSS\Result;
class ListLiveChannelResult extends Result 
{
	protected function parseDataFromResponse() 
	{
		$content = $this->rawResponse->body;
		$channelList = new \OSS\Model\LiveChannelListInfo();
		$channelList->parseFromXml($content);
		return $channelList;
	}
}
?>