<?php  namespace Guzzle\Http;
final class StaticClient 
{
	private static $client = NULL;
	public static function mount($className = "Guzzle", ClientInterface $client = NULL) 
	{
		class_alias("Guzzle\\Http\\StaticClient", $className);
		if( $client ) 
		{
			self::$client = $client;
		}
	}
	public static function request($method, $url, $options = array( )) 
	{
		if( !self::$client ) 
		{
			self::$client = new Client();
		}
		$request = self::$client->createRequest($method, $url, null, null, $options);
		if( isset($options["stream"]) ) 
		{
			if( $options["stream"] instanceof \Guzzle\Stream\StreamRequestFactoryInterface ) 
			{
				return $options["stream"]->fromRequest($request);
			}
			if( $options["stream"] == true ) 
			{
				$streamFactory = new \Guzzle\Stream\PhpStreamRequestFactory();
				return $streamFactory->fromRequest($request);
			}
		}
		return $request->send();
	}
	public static function get($url, $options = array( )) 
	{
		return self::request("GET", $url, $options);
	}
	public static function head($url, $options = array( )) 
	{
		return self::request("HEAD", $url, $options);
	}
	public static function delete($url, $options = array( )) 
	{
		return self::request("DELETE", $url, $options);
	}
	public static function post($url, $options = array( )) 
	{
		return self::request("POST", $url, $options);
	}
	public static function put($url, $options = array( )) 
	{
		return self::request("PUT", $url, $options);
	}
	public static function patch($url, $options = array( )) 
	{
		return self::request("PATCH", $url, $options);
	}
	public static function options($url, $options = array( )) 
	{
		return self::request("OPTIONS", $url, $options);
	}
}
?>