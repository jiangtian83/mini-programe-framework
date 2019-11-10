<?php  namespace OSS\Tests;
require_once(__DIR__ . "/Common.php");
class LiveChannelXmlTest extends \PHPUnit_Framework_TestCase 
{
	private $config = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n<LiveChannelConfiguration>\r\n  <Description>xxx</Description>\r\n  <Status>enabled</Status>\r\n  <Target>\r\n     <Type>hls</Type>\r\n     <FragDuration>1000</FragDuration>\r\n     <FragCount>5</FragCount>\r\n     <PlayListName>hello.m3u8</PlayListName>\r\n  </Target>\r\n</LiveChannelConfiguration>";
	private $info = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n<CreateLiveChannelResult>\r\n  <Name>live-1</Name>\r\n  <Description>xxx</Description>\r\n  <PublishUrls>\r\n    <Url>rtmp://bucket.oss-cn-hangzhou.aliyuncs.com/live/213443245345</Url>\r\n  </PublishUrls>\r\n  <PlayUrls>\r\n    <Url>http://bucket.oss-cn-hangzhou.aliyuncs.com/213443245345/播放列表.m3u8</Url>\r\n  </PlayUrls>\r\n  <Status>enabled</Status>\r\n  <LastModified>2015-11-24T14:25:31.000Z</LastModified>\r\n</CreateLiveChannelResult>";
	private $list = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n<ListLiveChannelResult>\r\n<Prefix>xxx</Prefix>\r\n  <Marker>yyy</Marker>\r\n  <MaxKeys>100</MaxKeys>\r\n  <IsTruncated>false</IsTruncated>\r\n  <NextMarker>121312132</NextMarker>\r\n  <LiveChannel>\r\n    <Name>12123214323431</Name>\r\n    <Description>xxx</Description>\r\n    <PublishUrls>\r\n      <Url>rtmp://bucket.oss-cn-hangzhou.aliyuncs.com/live/1</Url>\r\n    </PublishUrls>\r\n    <PlayUrls>\r\n      <Url>http://bucket.oss-cn-hangzhou.aliyuncs.com/1/播放列表.m3u8</Url>\r\n    </PlayUrls>\r\n    <Status>enabled</Status>\r\n    <LastModified>2015-11-24T14:25:31.000Z</LastModified>\r\n  </LiveChannel>\r\n  <LiveChannel>\r\n    <Name>432423432423</Name>\r\n    <Description>yyy</Description>\r\n    <PublishUrls>\r\n      <Url>rtmp://bucket.oss-cn-hangzhou.aliyuncs.com/live/2</Url>\r\n    </PublishUrls>\r\n    <PlayUrls>\r\n      <Url>http://bucket.oss-cn-hangzhou.aliyuncs.com/2/播放列表.m3u8</Url>\r\n    </PlayUrls>\r\n    <Status>enabled</Status>\r\n    <LastModified>2016-11-24T14:25:31.000Z</LastModified>\r\n  </LiveChannel>\r\n</ListLiveChannelResult>";
	private $status = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n<LiveChannelStat>\r\n    <Status>Live</Status>\r\n    <ConnectedTime>2016-10-20T14:25:31.000Z</ConnectedTime>\r\n    <RemoteAddr>10.1.2.4:47745</RemoteAddr>\r\n    <Video>\r\n        <Width>1280</Width>\r\n        <Height>536</Height>\r\n        <FrameRate>24</FrameRate>\r\n        <Bandwidth>72513</Bandwidth>\r\n        <Codec>H264</Codec>\r\n    </Video>\r\n        <Audio>\r\n        <Bandwidth>6519</Bandwidth>\r\n        <SampleRate>44100</SampleRate>\r\n        <Codec>AAC</Codec>\r\n    </Audio>\r\n</LiveChannelStat>";
	private $history = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n<LiveChannelHistory>\r\n    <LiveRecord>\r\n        <StartTime>2013-11-24T14:25:31.000Z</StartTime>\r\n        <EndTime>2013-11-24T15:25:31.000Z</EndTime>\r\n        <RemoteAddr>10.101.194.148:56861</RemoteAddr>\r\n    </LiveRecord>\r\n    <LiveRecord>\r\n        <StartTime>2014-11-24T14:25:31.000Z</StartTime>\r\n        <EndTime>2014-11-24T15:25:31.000Z</EndTime>\r\n        <RemoteAddr>10.101.194.148:56862</RemoteAddr>\r\n    </LiveRecord>\r\n    <LiveRecord>\r\n        <StartTime>2015-11-24T14:25:31.000Z</StartTime>\r\n        <EndTime>2015-11-24T15:25:31.000Z</EndTime>\r\n        <RemoteAddr>10.101.194.148:56863</RemoteAddr>\r\n    </LiveRecord>\r\n</LiveChannelHistory>";
	public function testLiveChannelStatus() 
	{
		$stat = new \OSS\Model\GetLiveChannelStatus();
		$stat->parseFromXml($this->status);
		$this->assertEquals("Live", $stat->getStatus());
		$this->assertEquals("2016-10-20T14:25:31.000Z", $stat->getConnectedTime());
		$this->assertEquals("10.1.2.4:47745", $stat->getRemoteAddr());
		$this->assertEquals(1280, $stat->getVideoWidth());
		$this->assertEquals(536, $stat->getVideoHeight());
		$this->assertEquals(24, $stat->getVideoFrameRate());
		$this->assertEquals(72513, $stat->getVideoBandwidth());
		$this->assertEquals("H264", $stat->getVideoCodec());
		$this->assertEquals(6519, $stat->getAudioBandwidth());
		$this->assertEquals(44100, $stat->getAudioSampleRate());
		$this->assertEquals("AAC", $stat->getAudioCodec());
	}
	public function testLiveChannelHistory() 
	{
		$history = new \OSS\Model\GetLiveChannelHistory();
		$history->parseFromXml($this->history);
		$recordList = $history->getLiveRecordList();
		$this->assertEquals(3, count($recordList));
		$list0 = $recordList[0];
		$this->assertEquals("2013-11-24T14:25:31.000Z", $list0->getStartTime());
		$this->assertEquals("2013-11-24T15:25:31.000Z", $list0->getEndTime());
		$this->assertEquals("10.101.194.148:56861", $list0->getRemoteAddr());
		$list1 = $recordList[1];
		$this->assertEquals("2014-11-24T14:25:31.000Z", $list1->getStartTime());
		$this->assertEquals("2014-11-24T15:25:31.000Z", $list1->getEndTime());
		$this->assertEquals("10.101.194.148:56862", $list1->getRemoteAddr());
		$list2 = $recordList[2];
		$this->assertEquals("2015-11-24T14:25:31.000Z", $list2->getStartTime());
		$this->assertEquals("2015-11-24T15:25:31.000Z", $list2->getEndTime());
		$this->assertEquals("10.101.194.148:56863", $list2->getRemoteAddr());
	}
	public function testLiveChannelConfig() 
	{
		$config = new \OSS\Model\LiveChannelConfig(array( "name" => "live-1" ));
		$config->parseFromXml($this->config);
		$this->assertEquals("xxx", $config->getDescription());
		$this->assertEquals("enabled", $config->getStatus());
		$this->assertEquals("hls", $config->getType());
		$this->assertEquals(1000, $config->getFragDuration());
		$this->assertEquals(5, $config->getFragCount());
		$this->assertEquals("hello.m3u8", $config->getPlayListName());
		$xml = $config->serializeToXml();
		$config2 = new \OSS\Model\LiveChannelConfig(array( "name" => "live-2" ));
		$config2->parseFromXml($xml);
		$this->assertEquals("xxx", $config2->getDescription());
		$this->assertEquals("enabled", $config2->getStatus());
		$this->assertEquals("hls", $config2->getType());
		$this->assertEquals(1000, $config2->getFragDuration());
		$this->assertEquals(5, $config2->getFragCount());
		$this->assertEquals("hello.m3u8", $config2->getPlayListName());
	}
	public function testLiveChannelInfo() 
	{
		$info = new \OSS\Model\LiveChannelInfo(array( "name" => "live-1" ));
		$info->parseFromXml($this->info);
		$this->assertEquals("live-1", $info->getName());
		$this->assertEquals("xxx", $info->getDescription());
		$this->assertEquals("enabled", $info->getStatus());
		$this->assertEquals("2015-11-24T14:25:31.000Z", $info->getLastModified());
		$pubs = $info->getPublishUrls();
		$this->assertEquals(1, count($pubs));
		$this->assertEquals("rtmp://bucket.oss-cn-hangzhou.aliyuncs.com/live/213443245345", $pubs[0]);
		$plays = $info->getPlayUrls();
		$this->assertEquals(1, count($plays));
		$this->assertEquals("http://bucket.oss-cn-hangzhou.aliyuncs.com/213443245345/播放列表.m3u8", $plays[0]);
	}
	public function testLiveChannelList() 
	{
		$list = new \OSS\Model\LiveChannelListInfo();
		$list->parseFromXml($this->list);
		$this->assertEquals("xxx", $list->getPrefix());
		$this->assertEquals("yyy", $list->getMarker());
		$this->assertEquals(100, $list->getMaxKeys());
		$this->assertEquals(false, $list->getIsTruncated());
		$this->assertEquals("121312132", $list->getNextMarker());
		$channels = $list->getChannelList();
		$this->assertEquals(2, count($channels));
		$chan1 = $channels[0];
		$this->assertEquals("12123214323431", $chan1->getName());
		$this->assertEquals("xxx", $chan1->getDescription());
		$this->assertEquals("enabled", $chan1->getStatus());
		$this->assertEquals("2015-11-24T14:25:31.000Z", $chan1->getLastModified());
		$pubs = $chan1->getPublishUrls();
		$this->assertEquals(1, count($pubs));
		$this->assertEquals("rtmp://bucket.oss-cn-hangzhou.aliyuncs.com/live/1", $pubs[0]);
		$plays = $chan1->getPlayUrls();
		$this->assertEquals(1, count($plays));
		$this->assertEquals("http://bucket.oss-cn-hangzhou.aliyuncs.com/1/播放列表.m3u8", $plays[0]);
		$chan2 = $channels[1];
		$this->assertEquals("432423432423", $chan2->getName());
		$this->assertEquals("yyy", $chan2->getDescription());
		$this->assertEquals("enabled", $chan2->getStatus());
		$this->assertEquals("2016-11-24T14:25:31.000Z", $chan2->getLastModified());
		$pubs = $chan2->getPublishUrls();
		$this->assertEquals(1, count($pubs));
		$this->assertEquals("rtmp://bucket.oss-cn-hangzhou.aliyuncs.com/live/2", $pubs[0]);
		$plays = $chan2->getPlayUrls();
		$this->assertEquals(1, count($plays));
		$this->assertEquals("http://bucket.oss-cn-hangzhou.aliyuncs.com/2/播放列表.m3u8", $plays[0]);
	}
}