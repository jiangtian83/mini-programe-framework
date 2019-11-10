<?php  namespace Guzzle\Plugin\Cache;
interface CanCacheStrategyInterface 
{
	public function canCacheRequest(\Guzzle\Http\Message\RequestInterface $request);
	public function canCacheResponse(\Guzzle\Http\Message\Response $response);
}
?>