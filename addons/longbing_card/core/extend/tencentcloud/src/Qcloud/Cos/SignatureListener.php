<?php  namespace Qcloud\Cos;
class SignatureListener implements \Symfony\Component\EventDispatcher\EventSubscriberInterface 
{
	protected $signature = NULL;
	public function __construct($accessKey, $secretKey) 
	{
		$this->signature = new Signature($accessKey, $secretKey);
	}
	public static function getSubscribedEvents() 
	{
		return array( "request.before_send" => array( "onRequestBeforeSend", -255 ) );
	}
	public function onRequestBeforeSend(\Guzzle\Common\Event $event) 
	{
		$this->signature->signRequest($event["request"]);
	}
}
?>