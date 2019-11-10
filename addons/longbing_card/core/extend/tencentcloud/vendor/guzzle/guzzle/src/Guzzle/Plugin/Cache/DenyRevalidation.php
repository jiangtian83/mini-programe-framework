<?php  namespace Guzzle\Plugin\Cache;
class DenyRevalidation extends DefaultRevalidation 
{
	public function __construct() 
	{
	}
	public function revalidate(\Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response) 
	{
		return false;
	}
}
?>