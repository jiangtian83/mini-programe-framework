<?php  namespace Guzzle\Plugin\CurlAuth;
class CurlAuthPlugin implements \Symfony\Component\EventDispatcher\EventSubscriberInterface 
{
	private $username = NULL;
	private $password = NULL;
	private $scheme = NULL;
	public function __construct($username, $password, $scheme = CURLAUTH_BASIC) 
	{
		\Guzzle\Common\Version::warn("Guzzle\\Plugin\\CurlAuth\\CurlAuthPlugin" . " is deprecated. Use \$client->getConfig()->setPath('request.options/auth', array('user', 'pass', 'Basic|Digest');");
		$this->username = $username;
		$this->password = $password;
		$this->scheme = $scheme;
	}
	public static function getSubscribedEvents() 
	{
		return array( "client.create_request" => array( "onRequestCreate", 255 ) );
	}
	public function onRequestCreate(\Guzzle\Common\Event $event) 
	{
		$event["request"]->setAuth($this->username, $this->password, $this->scheme);
	}
}
?>