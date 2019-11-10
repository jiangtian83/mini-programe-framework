<?php  namespace Guzzle\Http\Curl;
interface CurlMultiInterface extends \Countable, \Guzzle\Common\HasDispatcherInterface 
{
	const POLLING_REQUEST = "curl_multi.polling_request";
	const ADD_REQUEST = "curl_multi.add_request";
	const REMOVE_REQUEST = "curl_multi.remove_request";
	const MULTI_EXCEPTION = "curl_multi.exception";
	const BLOCKING = "curl_multi.blocking";
	public function add(\Guzzle\Http\Message\RequestInterface $request);
	public function all();
	public function remove(\Guzzle\Http\Message\RequestInterface $request);
	public function reset($hard);
	public function send();
}
?>