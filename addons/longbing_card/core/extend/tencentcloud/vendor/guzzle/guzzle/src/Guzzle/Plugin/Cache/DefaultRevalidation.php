<?php  namespace Guzzle\Plugin\Cache;
class DefaultRevalidation implements RevalidationInterface 
{
	protected $storage = NULL;
	protected $canCache = NULL;
	public function __construct(CacheStorageInterface $cache, CanCacheStrategyInterface $canCache = NULL) 
	{
		$this->storage = $cache;
		$this->canCache = ($canCache ?: new DefaultCanCacheStrategy());
	}
	public function revalidate(\Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response) 
	{
		try 
		{
			$revalidate = $this->createRevalidationRequest($request, $response);
			$validateResponse = $revalidate->send();
			if( $validateResponse->getStatusCode() == 200 ) 
			{
				return $this->handle200Response($request, $validateResponse);
			}
			if( $validateResponse->getStatusCode() == 304 ) 
			{
				return $this->handle304Response($request, $validateResponse, $response);
			}
		}
		catch( \Guzzle\Http\Exception\BadResponseException $e ) 
		{
			$this->handleBadResponse($e);
		}
		return false;
	}
	public function shouldRevalidate(\Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response) 
	{
		if( $request->getMethod() != \Guzzle\Http\Message\RequestInterface::GET ) 
		{
			return false;
		}
		$reqCache = $request->getHeader("Cache-Control");
		$resCache = $response->getHeader("Cache-Control");
		$revalidate = $request->getHeader("Pragma") == "no-cache" || $reqCache && ($reqCache->hasDirective("no-cache") || $reqCache->hasDirective("must-revalidate")) || $resCache && ($resCache->hasDirective("no-cache") || $resCache->hasDirective("must-revalidate"));
		if( !$revalidate && !$resCache && $response->hasHeader("ETag") ) 
		{
			$revalidate = true;
		}
		return $revalidate;
	}
	protected function handleBadResponse(\Guzzle\Http\Exception\BadResponseException $e) 
	{
		if( $e->getResponse()->getStatusCode() == 404 ) 
		{
			$this->storage->delete($e->getRequest());
			throw $e;
		}
	}
	protected function createRevalidationRequest(\Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response) 
	{
		$revalidate = clone $request;
		$revalidate->removeHeader("Pragma")->removeHeader("Cache-Control");
		if( $response->getLastModified() ) 
		{
			$revalidate->setHeader("If-Modified-Since", $response->getLastModified());
		}
		if( $response->getEtag() ) 
		{
			$revalidate->setHeader("If-None-Match", $response->getEtag());
		}
		$dispatcher = $revalidate->getEventDispatcher();
		foreach( $dispatcher->getListeners() as $eventName => $listeners ) 
		{
			foreach( $listeners as $listener ) 
			{
				if( is_array($listener) && $listener[0] instanceof CachePlugin ) 
				{
					$dispatcher->removeListener($eventName, $listener);
				}
			}
		}
		return $revalidate;
	}
	protected function handle200Response(\Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $validateResponse) 
	{
		$request->setResponse($validateResponse);
		if( $this->canCache->canCacheResponse($validateResponse) ) 
		{
			$this->storage->cache($request, $validateResponse);
		}
		return false;
	}
	protected function handle304Response(\Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $validateResponse, \Guzzle\Http\Message\Response $response) 
	{
		static $replaceHeaders = array( "Date", "Expires", "Cache-Control", "ETag", "Last-Modified" );
		if( $validateResponse->getEtag() != $response->getEtag() ) 
		{
			return false;
		}
		$modified = false;
		foreach( $replaceHeaders as $name ) 
		{
			if( $validateResponse->hasHeader($name) ) 
			{
				$modified = true;
				$response->setHeader($name, $validateResponse->getHeader($name));
			}
		}
		if( $modified && $this->canCache->canCacheResponse($response) ) 
		{
			$this->storage->cache($request, $response);
		}
		return true;
	}
}
?>