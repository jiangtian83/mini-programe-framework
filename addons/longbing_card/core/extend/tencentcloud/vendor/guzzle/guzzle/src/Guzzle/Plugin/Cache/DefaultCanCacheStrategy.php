<?php  namespace Guzzle\Plugin\Cache;
class DefaultCanCacheStrategy implements CanCacheStrategyInterface 
{
	public function canCacheRequest(\Guzzle\Http\Message\RequestInterface $request) 
	{
		if( $request->getMethod() != \Guzzle\Http\Message\RequestInterface::GET && $request->getMethod() != \Guzzle\Http\Message\RequestInterface::HEAD ) 
		{
			return false;
		}
		if( $request->hasHeader("Cache-Control") && $request->getHeader("Cache-Control")->hasDirective("no-store") ) 
		{
			return false;
		}
		return true;
	}
	public function canCacheResponse(\Guzzle\Http\Message\Response $response) 
	{
		return $response->isSuccessful() && $response->canCache();
	}
}
?>