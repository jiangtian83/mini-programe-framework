<?php  namespace Qcloud\Cos;
class TokenListener implements \Symfony\Component\EventDispatcher\EventSubscriberInterface 
{
	protected $token = NULL;
	public function __construct($token) 
	{
		$this->token = $token;
	}
	public static function getSubscribedEvents() 
	{
		return array( "request.before_send" => array( "onRequestBeforeSend", -240 ) );
	}
	public function onRequestBeforeSend(\Guzzle\Common\Event $event) 
	{
		if( $this->token != null ) 
		{
			$event["request"]->setHeader("x-cos-security-token", $this->token);
		}
	}
}
?>