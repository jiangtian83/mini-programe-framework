<?php  namespace Guzzle\Plugin\Async;
class AsyncPlugin implements \Symfony\Component\EventDispatcher\EventSubscriberInterface 
{
	public static function getSubscribedEvents() 
	{
		return array( "request.before_send" => "onBeforeSend", "request.exception" => "onRequestTimeout", "request.sent" => "onRequestSent", "curl.callback.progress" => "onCurlProgress" );
	}
	public function onBeforeSend(\Guzzle\Common\Event $event) 
	{
		$event["request"]->getCurlOptions()->set("progress", true);
	}
	public function onCurlProgress(\Guzzle\Common\Event $event) 
	{
		if( $event["handle"] && ($event["downloaded"] || isset($event["uploaded"]) && $event["upload_size"] === $event["uploaded"]) ) 
		{
			curl_setopt($event["handle"], CURLOPT_TIMEOUT_MS, 1);
			if( $event["uploaded"] ) 
			{
				curl_setopt($event["handle"], CURLOPT_NOBODY, true);
			}
		}
	}
	public function onRequestTimeout(\Guzzle\Common\Event $event) 
	{
		if( $event["exception"] instanceof \Guzzle\Http\Exception\CurlException ) 
		{
			$event["request"]->setResponse(new \Guzzle\Http\Message\Response(200, array( "X-Guzzle-Async" => "Did not wait for the response" )));
		}
	}
	public function onRequestSent(\Guzzle\Common\Event $event) 
	{
		$event["request"]->getResponse()->setHeader("X-Guzzle-Async", "Did not wait for the response");
	}
}
?>