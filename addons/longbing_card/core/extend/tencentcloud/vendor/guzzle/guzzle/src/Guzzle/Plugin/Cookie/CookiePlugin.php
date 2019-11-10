<?php  namespace Guzzle\Plugin\Cookie;
class CookiePlugin implements \Symfony\Component\EventDispatcher\EventSubscriberInterface 
{
	protected $cookieJar = NULL;
	public function __construct(CookieJar\CookieJarInterface $cookieJar = NULL) 
	{
		$this->cookieJar = ($cookieJar ?: new CookieJar\ArrayCookieJar());
	}
	public static function getSubscribedEvents() 
	{
		return array( "request.before_send" => array( "onRequestBeforeSend", 125 ), "request.sent" => array( "onRequestSent", 125 ) );
	}
	public function getCookieJar() 
	{
		return $this->cookieJar;
	}
	public function onRequestBeforeSend(\Guzzle\Common\Event $event) 
	{
		$request = $event["request"];
		if( !$request->getParams()->get("cookies.disable") ) 
		{
			$request->removeHeader("Cookie");
			foreach( $this->cookieJar->getMatchingCookies($request) as $cookie ) 
			{
				$request->addCookie($cookie->getName(), $cookie->getValue());
			}
		}
	}
	public function onRequestSent(\Guzzle\Common\Event $event) 
	{
		$this->cookieJar->addCookiesFromResponse($event["response"], $event["request"]);
	}
}
?>