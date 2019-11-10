<?php  namespace Guzzle\Plugin\Cache;
interface RevalidationInterface 
{
	public function revalidate(\Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response);
	public function shouldRevalidate(\Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response);
}
?>