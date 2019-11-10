<?php  namespace Qcloud\Cos;
class BucketStyleListener implements \Symfony\Component\EventDispatcher\EventSubscriberInterface 
{
	private $appId = NULL;
	public function __construct($appId) 
	{
		$this->appId = $appId;
	}
	public static function getSubscribedEvents() 
	{
		return array( "command.after_prepare" => array( "onCommandAfterPrepare", -230 ) );
	}
	public function onCommandAfterPrepare(\Guzzle\Common\Event $event) 
	{
		$command = $event["command"];
		$bucket = $command["Bucket"];
		$request = $command->getRequest();
		if( $command->getName() == "ListBuckets" ) 
		{
			$request->setHost("service.cos.myqcloud.com");
		}
		else 
		{
			if( ($key = $command["Key"]) && is_array($key) ) 
			{
				$command["Key"] = $key = implode("/", $key);
			}
			$request->setHeader("Date", gmdate("D, d M Y H:i:s T"));
			$request->setPath(preg_replace("#^/" . $bucket . "#", "", $request->getPath()));
			if( $this->appId != null && endWith($bucket, "-" . $this->appId) == False ) 
			{
				$bucket = $bucket . "-" . $this->appId;
			}
			$request->getParams()->set("bucket", $bucket)->set("key", $key);
			$request->setHost($bucket . "." . $request->getHost());
			if( !$bucket ) 
			{
				$request->getParams()->set("cos.resource", "/");
			}
			else 
			{
				$request->getParams()->set("cos.resource", "/" . rawurlencode($bucket) . (($key ? "/" . Client::encodeKey($key) : "/")));
			}
		}
	}
}
function endWith($haystack, $needle) 
{
	$length = strlen($needle);
	if( $length == 0 ) 
	{
		return true;
	}
	return substr($haystack, 0 - $length) === $needle;
}
?>