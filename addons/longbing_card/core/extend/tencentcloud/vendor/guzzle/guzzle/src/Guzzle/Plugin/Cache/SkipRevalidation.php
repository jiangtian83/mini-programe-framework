<?php  namespace Guzzle\Plugin\Cache;
class SkipRevalidation extends DefaultRevalidation 
{
	public function __construct() 
	{
	}
	public function revalidate(\Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response) 
	{
		return true;
	}
}
?>