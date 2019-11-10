<?php  namespace Guzzle\Http;
interface ClientInterface extends \Guzzle\Common\HasDispatcherInterface 
{
	const CREATE_REQUEST = "client.create_request";
	const HTTP_DATE = "D, d M Y H:i:s \\G\\M\\T";
	public function setConfig($config);
	public function getConfig($key);
	public function createRequest($method, $uri, $headers, $body, array $options);
	public function get($uri, $headers, $options);
	public function head($uri, $headers, array $options);
	public function delete($uri, $headers, $body, array $options);
	public function put($uri, $headers, $body, array $options);
	public function patch($uri, $headers, $body, array $options);
	public function post($uri, $headers, $postBody, array $options);
	public function options($uri, array $options);
	public function send($requests);
	public function getBaseUrl($expand);
	public function setBaseUrl($url);
	public function setUserAgent($userAgent, $includeDefault);
	public function setSslVerification($certificateAuthority, $verifyPeer, $verifyHost);
}
?>