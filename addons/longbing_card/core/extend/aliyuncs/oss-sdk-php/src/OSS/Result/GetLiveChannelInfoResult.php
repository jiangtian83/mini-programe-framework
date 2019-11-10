<?php  namespace OSS\Result;
class GetLiveChannelInfoResult extends Result 
{
	protected function parseDataFromResponse() 
	{
		$content = $this->rawResponse->body;
		$channelList = new \OSS\Model\GetLiveChannelInfo();
		$channelList->parseFromXml($content);
		return $channelList;
	}
}
?>