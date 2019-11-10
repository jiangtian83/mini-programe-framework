<?php  namespace Guzzle\Tests;
abstract class GuzzleTestCase extends \PHPUnit_Framework_TestCase 
{
	protected static $mockBasePath = NULL;
	public static $serviceBuilder = NULL;
	public static $server = NULL;
	private $requests = array( );
	public $mockObserver = NULL;
	public static function getServer() 
	{
		if( !self::$server ) 
		{
			self::$server = new Http\Server();
			if( self::$server->isRunning() ) 
			{
				self::$server->flush();
			}
			else 
			{
				self::$server->start();
			}
		}
		return self::$server;
	}
	public static function setServiceBuilder(\Guzzle\Service\Builder\ServiceBuilderInterface $builder) 
	{
		self::$serviceBuilder = $builder;
	}
	public static function getServiceBuilder() 
	{
		if( !self::$serviceBuilder ) 
		{
			throw new \RuntimeException("No service builder has been set via setServiceBuilder()");
		}
		return self::$serviceBuilder;
	}
	protected function hasSubscriber(\Guzzle\Common\HasDispatcherInterface $dispatcher, \Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber) 
	{
		$class = get_class($subscriber);
		$all = array_keys(call_user_func(array( $class, "getSubscribedEvents" )));
		foreach( $all as $i => $event ) 
		{
			foreach( $dispatcher->getEventDispatcher()->getListeners($event) as $e ) 
			{
				if( $e[0] === $subscriber ) 
				{
					unset($all[$i]);
					break;
				}
			}
		}
		return count($all) == 0;
	}
	public function getWildcardObserver(\Guzzle\Common\HasDispatcherInterface $hasDispatcher) 
	{
		$class = get_class($hasDispatcher);
		$o = new Mock\MockObserver();
		$events = call_user_func(array( $class, "getAllEvents" ));
		foreach( $events as $event ) 
		{
			$hasDispatcher->getEventDispatcher()->addListener($event, array( $o, "update" ));
		}
		return $o;
	}
	public static function setMockBasePath($path) 
	{
		self::$mockBasePath = $path;
	}
	public function addMockedRequest(\Guzzle\Http\Message\RequestInterface $request) 
	{
		$this->requests[] = $request;
		return $this;
	}
	public function getMockedRequests() 
	{
		return $this->requests;
	}
	public function getMockResponse($path) 
	{
		return ($path instanceof \Guzzle\Http\Message\Response ? $path : \Guzzle\Plugin\Mock\MockPlugin::getMockFile(self::$mockBasePath . DIRECTORY_SEPARATOR . $path));
	}
	public function setMockResponse(\Guzzle\Http\Client $client, $paths) 
	{
		$this->requests = array( );
		$that = $this;
		$mock = new \Guzzle\Plugin\Mock\MockPlugin(null, true);
		$client->getEventDispatcher()->removeSubscriber($mock);
		$mock->getEventDispatcher()->addListener("mock.request", function(\Guzzle\Common\Event $event) use ($that) 
		{
			$that->addMockedRequest($event["request"]);
		}
		);
		if( $paths instanceof \Guzzle\Http\Message\Response ) 
		{
			$paths = array( $paths );
		}
		foreach( (array) $paths as $path ) 
		{
			$mock->addResponse($this->getMockResponse($path));
		}
		$client->getEventDispatcher()->addSubscriber($mock);
		return $mock;
	}
	public function compareHeaders($filteredHeaders, $actualHeaders) 
	{
		$comparison = new Http\Message\HeaderComparison();
		return $comparison->compare($filteredHeaders, $actualHeaders);
	}
	public function assertContainsIns($needle, $haystack, $message = NULL) 
	{
		$this->assertContains(strtolower($needle), strtolower($haystack), $message);
	}
}
?>