<?php  namespace Guzzle\Plugin\Cookie\CookieJar;
interface CookieJarInterface extends \Countable, \IteratorAggregate 
{
	public function remove($domain, $path, $name);
	public function removeTemporary();
	public function removeExpired();
	public function add(\Guzzle\Plugin\Cookie\Cookie $cookie);
	public function addCookiesFromResponse(\Guzzle\Http\Message\Response $response, \Guzzle\Http\Message\RequestInterface $request);
	public function getMatchingCookies(\Guzzle\Http\Message\RequestInterface $request);
	public function all($domain, $path, $name, $skipDiscardable, $skipExpired);
}
?>