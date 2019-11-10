<?php  require_once(__DIR__ . "/Common.php");
$bucket = Common::getBucketName();
$ossClient = Common::getOssClient();
if( is_null($ossClient) ) 
{
	exit( 1 );
}
$config = new OSS\Model\LiveChannelConfig(array( "description" => "live channel test", "type" => "HLS", "fragDuration" => 10, "fragCount" => 5, "playListName" => "hello.m3u8" ));
$info = $ossClient->putBucketLiveChannel($bucket, "test_rtmp_live", $config);
Common::println("bucket " . $bucket . " liveChannel created:\n" . "live channel name: " . $info->getName() . "\n" . "live channel description: " . $info->getDescription() . "\n" . "publishurls: " . $info->getPublishUrls()[0] . "\n" . "playurls: " . $info->getPlayUrls()[0] . "\n");
$list = $ossClient->listBucketLiveChannels($bucket);
Common::println("bucket " . $bucket . " listLiveChannel:\n" . "list live channel prefix: " . $list->getPrefix() . "\n" . "list live channel marker: " . $list->getMarker() . "\n" . "list live channel maxkey: " . $list->getMaxKeys() . "\n" . "list live channel IsTruncated: " . $list->getIsTruncated() . "\n" . "list live channel getNextMarker: " . $list->getNextMarker() . "\n");
foreach( $list->getChannelList() as $list ) 
{
	Common::println("bucket " . $bucket . " listLiveChannel:\n" . "list live channel IsTruncated: " . $list->getName() . "\n" . "list live channel Description: " . $list->getDescription() . "\n" . "list live channel Status: " . $list->getStatus() . "\n" . "list live channel getNextMarker: " . $list->getLastModified() . "\n");
}
$play_url = $ossClient->signRtmpUrl($bucket, "test_rtmp_live", 3600, array( "params" => array( "playlistName" => "playlist.m3u8" ) ));
Common::println("bucket " . $bucket . " rtmp url: \n" . $play_url);
$play_url = $ossClient->signRtmpUrl($bucket, "test_rtmp_live", 3600);
Common::println("bucket " . $bucket . " rtmp url: \n" . $play_url);
$resp = $ossClient->putLiveChannelStatus($bucket, "test_rtmp_live", "enabled");
$info = $ossClient->getLiveChannelInfo($bucket, "test_rtmp_live");
Common::println("bucket " . $bucket . " LiveChannelInfo:\n" . "live channel info description: " . $info->getDescription() . "\n" . "live channel info status: " . $info->getStatus() . "\n" . "live channel info type: " . $info->getType() . "\n" . "live channel info fragDuration: " . $info->getFragDuration() . "\n" . "live channel info fragCount: " . $info->getFragCount() . "\n" . "live channel info playListName: " . $info->getPlayListName() . "\n");
$history = $ossClient->getLiveChannelHistory($bucket, "test_rtmp_live");
if( count($history->getLiveRecordList()) != 0 ) 
{
	foreach( $history->getLiveRecordList() as $recordList ) 
	{
		Common::println("bucket " . $bucket . " liveChannelHistory:\n" . "live channel history startTime: " . $recordList->getStartTime() . "\n" . "live channel history endTime: " . $recordList->getEndTime() . "\n" . "live channel history remoteAddr: " . $recordList->getRemoteAddr() . "\n");
	}
}
$status = $ossClient->getLiveChannelStatus($bucket, "test_rtmp_live");
Common::println("bucket " . $bucket . " listLiveChannel:\n" . "live channel status status: " . $status->getStatus() . "\n" . "live channel status ConnectedTime: " . $status->getConnectedTime() . "\n" . "live channel status VideoWidth: " . $status->getVideoWidth() . "\n" . "live channel status VideoHeight: " . $status->getVideoHeight() . "\n" . "live channel status VideoFrameRate: " . $status->getVideoFrameRate() . "\n" . "live channel status VideoBandwidth: " . $status->getVideoBandwidth() . "\n" . "live channel status VideoCodec: " . $status->getVideoCodec() . "\n" . "live channel status AudioBandwidth: " . $status->getAudioBandwidth() . "\n" . "live channel status AudioSampleRate: " . $status->getAudioSampleRate() . "\n" . "live channel status AdioCodec: " . $status->getAudioCodec() . "\n");
$current_time = time();
$ossClient->postVodPlaylist($bucket, "test_rtmp_live", "vod_playlist.m3u8", array( "StartTime" => $current_time - 60, "EndTime" => $current_time ));
$ossClient->deleteBucketLiveChannel($bucket, "test_rtmp_live");
?>