<?php  namespace Guzzle\Plugin\Cache;
class CachePlugin implements \Symfony\Component\EventDispatcher\EventSubscriberInterface 
{
	protected $revalidation = NULL;
	protected $canCache = NULL;
	protected $storage = NULL;
	protected $autoPurge = NULL;
	public function __construct($options = NULL) 
	{
		if( !is_array($options) ) 
		{
			if( $options instanceof \Guzzle\Cache\CacheAdapterInterface ) 
			{
				$options = array( "storage" => new DefaultCacheStorage($options) );
			}
			else 
			{
				if( $options instanceof CacheStorageInterface ) 
				{
					$options = array( "storage" => $options );
				}
				else 
				{
					if( $options ) 
					{
						$options = array( "storage" => new DefaultCacheStorage(\Guzzle\Cache\CacheAdapterFactory::fromCache($options)) );
					}
					else 
					{
						if( !class_exists("Doctrine\\Common\\Cache\\ArrayCache") ) 
						{
							throw new \Guzzle\Common\Exception\InvalidArgumentException("No cache was provided and Doctrine is not installed");
						}
					}
				}
			}
		}
		$this->autoPurge = (isset($options["auto_purge"]) ? $options["auto_purge"] : false);
		$this->storage = (isset($options["storage"]) ? $options["storage"] : new DefaultCacheStorage(new \Guzzle\Cache\DoctrineCacheAdapter(new \Doctrine\Common\Cache\ArrayCache())));
		if( !isset($options["can_cache"]) ) 
		{
			$this->canCache = new DefaultCanCacheStrategy();
		}
		else 
		{
			$this->canCache = (is_callable($options["can_cache"]) ? new CallbackCanCacheStrategy($options["can_cache"]) : $options["can_cache"]);
		}
		$this->revalidation = (isset($options["revalidation"]) ? $options["revalidation"] : new DefaultRevalidation($this->storage, $this->canCache));
	}
	public static function getSubscribedEvents() 
	{
		return array( "request.before_send" => array( "onRequestBeforeSend", -255 ), "request.sent" => array( "onRequestSent", 255 ), "request.error" => array( "onRequestError", 0 ), "request.exception" => array( "onRequestException", 0 ) );
	}
	public function onRequestBeforeSend(\Guzzle\Common\Event $event) 
	{
		$request = $event["request"];
		$request->addHeader("Via", sprintf("%s GuzzleCache/%s", $request->getProtocolVersion(), \Guzzle\Common\Version::VERSION));
		if( !$this->canCache->canCacheRequest($request) ) 
		{
			switch( $request->getMethod() ) 
			{
				case "PURGE": $this->purge($request);
				$request->setResponse(new \Guzzle\Http\Message\Response(200, array( ), "purged"));
				break;
				case "PUT": case "POST": case "DELETE": case "PATCH": if( $this->autoPurge ) 
				{
					$this->purge($request);
				}
			}
		}
		else 
		{
			if( $response = $this->storage->fetch($request) ) 
			{
				$params = $request->getParams();
				$params["cache.lookup"] = true;
				$response->setHeader("Age", time() - strtotime((($response->getDate() ?: $response->getLastModified()) ?: "now")));
				if( $this->canResponseSatisfyRequest($request, $response) ) 
				{
					if( !isset($params["cache.hit"]) ) 
					{
						$params["cache.hit"] = true;
					}
					$request->setResponse($response);
				}
			}
		}
	}
	public function onRequestSent(\Guzzle\Common\Event $event) 
	{
		$request = $event["request"];
		$response = $event["response"];
		if( $request->getParams()->get("cache.hit") === null && $this->canCache->canCacheRequest($request) && $this->canCache->canCacheResponse($response) ) 
		{
			$this->storage->cache($request, $response);
		}
		$this->addResponseHeaders($request, $response);
	}
	public function onRequestError(\Guzzle\Common\Event $event) 
	{
		$request = $event["request"];
		if( !$this->canCache->canCacheRequest($request) ) 
		{
			return NULL;
		}
		if( $response = $this->storage->fetch($request) ) 
		{
			$response->setHeader("Age", time() - strtotime((($response->getLastModified() ?: $response->getDate()) ?: "now")));
			if( $this->canResponseSatisfyFailedRequest($request, $response) ) 
			{
				$request->getParams()->set("cache.hit", "error");
				$this->addResponseHeaders($request, $response);
				$event["response"] = $response;
				$event->stopPropagation();
			}
		}
	}
	public function onRequestException(\Guzzle\Common\Event $event) 
	{
		if( !$event["exception"] instanceof \Guzzle\Http\Exception\CurlException ) 
		{
			return NULL;
		}
		$request = $event["request"];
		if( !$this->canCache->canCacheRequest($request) ) 
		{
			return NULL;
		}
		if( $response = $this->storage->fetch($request) ) 
		{
			$response->setHeader("Age", time() - strtotime(($response->getDate() ?: "now")));
			if( !$this->canResponseSatisfyFailedRequest($request, $response) ) 
			{
				return NULL;
			}
			$request->getParams()->set("cache.hit", "error");
			$request->setResponse($response);
			$this->addResponseHeaders($request, $response);
			$event->stopPropagation();
		}
	}
	public function canResponseSatisfyRequest(\Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response) 
	{
		$responseAge = $response->calculateAge();
		$reqc = $request->getHeader("Cache-Control");
		$resc = $response->getHeader("Cache-Control");
		if( $reqc && $reqc->hasDirective("max-age") && $reqc->getDirective("max-age") < $responseAge ) 
		{
			return false;
		}
		if( $response->isFresh() === false ) 
		{
			$maxStale = ($reqc ? $reqc->getDirective("max-stale") : null);
			if( null !== $maxStale ) 
			{
				if( $maxStale !== true && $response->getFreshness() < -1 * $maxStale ) 
				{
					return false;
				}
			}
			else 
			{
				if( $resc && $resc->hasDirective("max-age") && $resc->getDirective("max-age") < $responseAge ) 
				{
					return false;
				}
			}
		}
		if( $this->revalidation->shouldRevalidate($request, $response) ) 
		{
			try 
			{
				return $this->revalidation->revalidate($request, $response);
			}
			catch( \Guzzle\Http\Exception\CurlException $e ) 
			{
				$request->getParams()->set("cache.hit", "error");
				return $this->canResponseSatisfyFailedRequest($request, $response);
			}
		}
		return true;
	}
	public function canResponseSatisfyFailedRequest(\Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response) 
	{
		$reqc = $request->getHeader("Cache-Control");
		$resc = $response->getHeader("Cache-Control");
		$requestStaleIfError = ($reqc ? $reqc->getDirective("stale-if-error") : null);
		$responseStaleIfError = ($resc ? $resc->getDirective("stale-if-error") : null);
		if( !$requestStaleIfError && !$responseStaleIfError ) 
		{
			return false;
		}
		if( is_numeric($requestStaleIfError) && $requestStaleIfError < $response->getAge() - $response->getMaxAge() ) 
		{
			return false;
		}
		if( is_numeric($responseStaleIfError) && $responseStaleIfError < $response->getAge() - $response->getMaxAge() ) 
		{
			return false;
		}
		return true;
	}
	public function purge($url) 
	{
		$url = ($url instanceof \Guzzle\Http\Message\RequestInterface ? $url->getUrl() : $url);
		$this->storage->purge($url);
	}
	protected function addResponseHeaders(\Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response) 
	{
		$params = $request->getParams();
		$response->setHeader("Via", sprintf("%s GuzzleCache/%s", $request->getProtocolVersion(), \Guzzle\Common\Version::VERSION));
		$lookup = (($params["cache.lookup"] === true ? "HIT" : "MISS")) . " from GuzzleCache";
		if( $header = $response->getHeader("X-Cache-Lookup") ) 
		{
			$values = $header->toArray();
			$values[] = $lookup;
			$response->setHeader("X-Cache-Lookup", array_unique($values));
		}
		else 
		{
			$response->setHeader("X-Cache-Lookup", $lookup);
		}
		if( $params["cache.hit"] === true ) 
		{
			$xcache = "HIT from GuzzleCache";
		}
		else 
		{
			if( $params["cache.hit"] == "error" ) 
			{
				$xcache = "HIT_ERROR from GuzzleCache";
			}
			else 
			{
				$xcache = "MISS from GuzzleCache";
			}
		}
		if( $header = $response->getHeader("X-Cache") ) 
		{
			$values = $header->toArray();
			$values[] = $xcache;
			$response->setHeader("X-Cache", array_unique($values));
		}
		else 
		{
			$response->setHeader("X-Cache", $xcache);
		}
		if( $response->isFresh() === false ) 
		{
			$response->addHeader("Warning", sprintf("110 GuzzleCache/%s \"Response is stale\"", \Guzzle\Common\Version::VERSION));
			if( $params["cache.hit"] === "error" ) 
			{
				$response->addHeader("Warning", sprintf("111 GuzzleCache/%s \"Revalidation failed\"", \Guzzle\Common\Version::VERSION));
			}
		}
	}
}
?>