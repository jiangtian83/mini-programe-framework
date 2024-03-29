<?php  namespace Guzzle\Plugin\History;
class HistoryPlugin implements \Symfony\Component\EventDispatcher\EventSubscriberInterface, \IteratorAggregate, \Countable 
{
	protected $limit = 10;
	protected $transactions = array( );
	public static function getSubscribedEvents() 
	{
		return array( "request.sent" => array( "onRequestSent", 9999 ) );
	}
	public function __toString() 
	{
		$lines = array( );
		foreach( $this->transactions as $entry ) 
		{
			$response = (isset($entry["response"]) ? $entry["response"] : "");
			$lines[] = "> " . trim($entry["request"]) . "\n\n< " . trim($response) . "\n";
		}
		return implode("\n", $lines);
	}
	public function add(\Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response = NULL) 
	{
		if( !$response && $request->getResponse() ) 
		{
			$response = $request->getResponse();
		}
		$this->transactions[] = array( "request" => $request, "response" => $response );
		if( $this->getlimit() < count($this->transactions) ) 
		{
			array_shift($this->transactions);
		}
		return $this;
	}
	public function setLimit($limit) 
	{
		$this->limit = (int) $limit;
		return $this;
	}
	public function getLimit() 
	{
		return $this->limit;
	}
	public function getAll() 
	{
		return $this->transactions;
	}
	public function getIterator() 
	{
		return new \ArrayIterator(array_map(function($entry) 
		{
			$entry["request"]->getParams()->set("actual_response", $entry["response"]);
			return $entry["request"];
		}
		, $this->transactions));
	}
	public function count() 
	{
		return count($this->transactions);
	}
	public function getLastRequest() 
	{
		$last = end($this->transactions);
		return $last["request"];
	}
	public function getLastResponse() 
	{
		$last = end($this->transactions);
		return (isset($last["response"]) ? $last["response"] : null);
	}
	public function clear() 
	{
		$this->transactions = array( );
		return $this;
	}
	public function onRequestSent(\Guzzle\Common\Event $event) 
	{
		$this->add($event["request"], $event["response"]);
	}
}
?>