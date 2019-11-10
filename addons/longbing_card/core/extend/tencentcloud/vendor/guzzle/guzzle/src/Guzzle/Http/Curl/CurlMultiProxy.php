<?php  namespace Guzzle\Http\Curl;
class CurlMultiProxy extends \Guzzle\Common\AbstractHasDispatcher implements CurlMultiInterface 
{
	protected $handles = array( );
	protected $groups = array( );
	protected $queued = array( );
	protected $maxHandles = NULL;
	protected $selectTimeout = NULL;
	public function __construct($maxHandles = 3, $selectTimeout = 1) 
	{
		$this->maxHandles = $maxHandles;
		$this->selectTimeout = $selectTimeout;
		class_exists("Guzzle\\Http\\Message\\Response");
		class_exists("Guzzle\\Http\\Exception\\CurlException");
	}
	public function add(\Guzzle\Http\Message\RequestInterface $request) 
	{
		$this->queued[] = $request;
		return $this;
	}
	public function all() 
	{
		$requests = $this->queued;
		foreach( $this->handles as $handle ) 
		{
			$requests = array_merge($requests, $handle->all());
		}
		return $requests;
	}
	public function remove(\Guzzle\Http\Message\RequestInterface $request) 
	{
		foreach( $this->queued as $i => $r ) 
		{
			if( $request === $r ) 
			{
				unset($this->queued[$i]);
				return true;
			}
		}
		foreach( $this->handles as $handle ) 
		{
			if( $handle->remove($request) ) 
			{
				return true;
			}
		}
		return false;
	}
	public function reset($hard = false) 
	{
		$this->queued = array( );
		$this->groups = array( );
		foreach( $this->handles as $handle ) 
		{
			$handle->reset();
		}
		if( $hard ) 
		{
			$this->handles = array( );
		}
		return $this;
	}
	public function send() 
	{
		if( $this->queued ) 
		{
			$group = $this->getAvailableHandle();
			$this->groups[] = $group;
			while( $request = array_shift($this->queued) ) 
			{
				$group->add($request);
			}
			try 
			{
				$group->send();
				array_pop($this->groups);
				$this->cleanupHandles();
			}
			catch( \Exception $e ) 
			{
				if( !$group->count() ) 
				{
					array_pop($this->groups);
					$this->cleanupHandles();
				}
				throw $e;
			}
		}
	}
	public function count() 
	{
		return count($this->all());
	}
	protected function getAvailableHandle() 
	{
		foreach( $this->handles as $h ) 
		{
			if( !in_array($h, $this->groups, true) ) 
			{
				return $h;
			}
		}
		$handle = new CurlMulti($this->selectTimeout);
		$handle->setEventDispatcher($this->getEventDispatcher());
		$this->handles[] = $handle;
		return $handle;
	}
	protected function cleanupHandles() 
	{
		if( $diff = max(0, count($this->handles) - $this->maxHandles) ) 
		{
			for( $i = count($this->handles) - 1;
			0 < $i && 0 < $diff;
			$i-- ) 
			{
				if( !count($this->handles[$i]) ) 
				{
					unset($this->handles[$i]);
					$diff--;
				}
			}
			$this->handles = array_values($this->handles);
		}
	}
}
?>