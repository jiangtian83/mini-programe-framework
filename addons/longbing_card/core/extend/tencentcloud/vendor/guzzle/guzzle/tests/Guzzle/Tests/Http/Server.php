<?php  namespace Guzzle\Tests\Http;
class Server 
{
	private $port = NULL;
	private $running = false;
	private $client = NULL;
	const DEFAULT_PORT = 8124;
	const REQUEST_DELIMITER = "\n----[request]\n";
	public function __construct($port = NULL) 
	{
		$this->port = ($port ?: self::DEFAULT_PORT);
		$this->client = new \Guzzle\Http\Client($this->getUrl());
		register_shutdown_function(array( $this, "stop" ));
	}
	public function flush() 
	{
		$this->client->delete("guzzle-server/requests")->send();
	}
	public function enqueue($responses) 
	{
		$data = array( );
		foreach( (array) $responses as $response ) 
		{
			if( is_string($response) ) 
			{
				$response = \Guzzle\Http\Message\Response::fromMessage($response);
			}
			else 
			{
				if( !$response instanceof \Guzzle\Http\Message\Response ) 
				{
					throw new \Guzzle\Http\Exception\BadResponseException("Responses must be strings or implement Response");
				}
			}
			$data[] = array( "statusCode" => $response->getStatusCode(), "reasonPhrase" => $response->getReasonPhrase(), "headers" => $response->getHeaders()->toArray(), "body" => $response->getBody(true) );
		}
		$request = $this->client->put("guzzle-server/responses", null, json_encode($data));
		$request->send();
	}
	public function isRunning() 
	{
		if( $this->running ) 
		{
			return true;
		}
		try 
		{
			$this->client->get("guzzle-server/perf", array( ), array( "timeout" => 5 ))->send();
			$this->running = true;
			return true;
		}
		catch( \Exception $e ) 
		{
			return false;
		}
	}
	public function getUrl() 
	{
		return "http://127.0.0.1:" . $this->getPort() . "/";
	}
	public function getPort() 
	{
		return $this->port;
	}
	public function getReceivedRequests($hydrate = false) 
	{
		$response = $this->client->get("guzzle-server/requests")->send();
		$data = array_filter(explode(self::REQUEST_DELIMITER, $response->getBody(true)));
		if( $hydrate ) 
		{
			$data = array_map(function($message) 
			{
				return \Guzzle\Http\Message\RequestFactory::getInstance()->fromMessage($message);
			}
			, $data);
		}
		return $data;
	}
	public function start() 
	{
		if( !$this->isRunning() ) 
		{
			exec("node " . __DIR__ . DIRECTORY_SEPARATOR . "server.js " . $this->port . " >> /tmp/server.log 2>&1 &");
			$start = time();
			while( !$this->isRunning() && time() - $start < 5 ) 
			{
			}
			if( !$this->running ) 
			{
				throw new \Guzzle\Common\Exception\RuntimeException("Unable to contact server.js. Have you installed node.js v0.5.0+? node must be in your path.");
			}
		}
	}
	public function stop() 
	{
		if( !$this->isRunning() ) 
		{
			return false;
		}
		$this->running = false;
		$this->client->delete("guzzle-server")->send();
	}
}
?>