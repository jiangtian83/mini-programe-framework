<?php  namespace Guzzle\Plugin\Cache;
interface CacheStorageInterface 
{
	public function fetch(\Guzzle\Http\Message\RequestInterface $request);
	public function cache(\Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response);
	public function delete(\Guzzle\Http\Message\RequestInterface $request);
	public function purge($url);
}
?>