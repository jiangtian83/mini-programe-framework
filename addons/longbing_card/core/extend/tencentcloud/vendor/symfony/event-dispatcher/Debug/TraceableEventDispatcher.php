<?php  namespace Symfony\Component\EventDispatcher\Debug;
class TraceableEventDispatcher implements TraceableEventDispatcherInterface 
{
	protected $logger = NULL;
	protected $stopwatch = NULL;
	private $called = NULL;
	private $dispatcher = NULL;
	private $wrappedListeners = NULL;
	public function __construct(\Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher, \Symfony\Component\Stopwatch\Stopwatch $stopwatch, \Psr\Log\LoggerInterface $logger = NULL) 
	{
		$this->dispatcher = $dispatcher;
		$this->stopwatch = $stopwatch;
		$this->logger = $logger;
		$this->called = array( );
		$this->wrappedListeners = array( );
	}
	public function addListener($eventName, $listener, $priority = 0) 
	{
		$this->dispatcher->addListener($eventName, $listener, $priority);
	}
	public function addSubscriber(\Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber) 
	{
		$this->dispatcher->addSubscriber($subscriber);
	}
	public function removeListener($eventName, $listener) 
	{
		if( isset($this->wrappedListeners[$eventName]) ) 
		{
			foreach( $this->wrappedListeners[$eventName] as $index => $wrappedListener ) 
			{
				if( $wrappedListener->getWrappedListener() === $listener ) 
				{
					$listener = $wrappedListener;
					unset($this->wrappedListeners[$eventName][$index]);
					break;
				}
			}
		}
		return $this->dispatcher->removeListener($eventName, $listener);
	}
	public function removeSubscriber(\Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber) 
	{
		return $this->dispatcher->removeSubscriber($subscriber);
	}
	public function getListeners($eventName = NULL) 
	{
		return $this->dispatcher->getListeners($eventName);
	}
	public function getListenerPriority($eventName, $listener) 
	{
		if( !method_exists($this->dispatcher, "getListenerPriority") ) 
		{
			return 0;
		}
		return $this->dispatcher->getListenerPriority($eventName, $listener);
	}
	public function hasListeners($eventName = NULL) 
	{
		return $this->dispatcher->hasListeners($eventName);
	}
	public function dispatch($eventName, \Symfony\Component\EventDispatcher\Event $event = NULL) 
	{
		if( null === $event ) 
		{
			$event = new \Symfony\Component\EventDispatcher\Event();
		}
		if( null !== $this->logger && $event->isPropagationStopped() ) 
		{
			$this->logger->debug(sprintf("The \"%s\" event is already stopped. No listeners have been called.", $eventName));
		}
		$this->preProcess($eventName);
		$this->preDispatch($eventName, $event);
		$e = $this->stopwatch->start($eventName, "section");
		$this->dispatcher->dispatch($eventName, $event);
		if( $e->isStarted() ) 
		{
			$e->stop();
		}
		$this->postDispatch($eventName, $event);
		$this->postProcess($eventName);
		return $event;
	}
	public function getCalledListeners() 
	{
		$called = array( );
		foreach( $this->called as $eventName => $listeners ) 
		{
			foreach( $listeners as $listener ) 
			{
				$info = $this->getListenerInfo($listener->getWrappedListener(), $eventName);
				$called[$eventName . "." . $info["pretty"]] = $info;
			}
		}
		return $called;
	}
	public function getNotCalledListeners() 
	{
		try 
		{
			$allListeners = $this->getListeners();
		}
		catch( \Exception $e ) 
		{
			if( null !== $this->logger ) 
			{
				$this->logger->info("An exception was thrown while getting the uncalled listeners.", array( "exception" => $e ));
			}
			return array( );
		}
		$notCalled = array( );
		foreach( $allListeners as $eventName => $listeners ) 
		{
			foreach( $listeners as $listener ) 
			{
				$called = false;
				if( isset($this->called[$eventName]) ) 
				{
					foreach( $this->called[$eventName] as $l ) 
					{
						if( $l->getWrappedListener() === $listener ) 
						{
							$called = true;
							break;
						}
					}
				}
				if( !$called ) 
				{
					$info = $this->getListenerInfo($listener, $eventName);
					$notCalled[$eventName . "." . $info["pretty"]] = $info;
				}
			}
		}
		uasort($notCalled, array( $this, "sortListenersByPriority" ));
		return $notCalled;
	}
	public function __call($method, $arguments) 
	{
		return call_user_func_array(array( $this->dispatcher, $method ), $arguments);
	}
	protected function preDispatch($eventName, \Symfony\Component\EventDispatcher\Event $event) 
	{
	}
	protected function postDispatch($eventName, \Symfony\Component\EventDispatcher\Event $event) 
	{
	}
	private function preProcess($eventName) 
	{
		foreach( $this->dispatcher->getListeners($eventName) as $listener ) 
		{
			$info = $this->getListenerInfo($listener, $eventName);
			$name = (isset($info["class"]) ? $info["class"] : $info["type"]);
			$wrappedListener = new WrappedListener($listener, $name, $this->stopwatch, $this);
			$this->wrappedListeners[$eventName][] = $wrappedListener;
			$this->dispatcher->removeListener($eventName, $listener);
			$this->dispatcher->addListener($eventName, $wrappedListener, $info["priority"]);
		}
	}
	private function postProcess($eventName) 
	{
		unset($this->wrappedListeners[$eventName]);
		$skipped = false;
		foreach( $this->dispatcher->getListeners($eventName) as $listener ) 
		{
			if( !$listener instanceof WrappedListener ) 
			{
				continue;
			}
			$priority = $this->getListenerPriority($eventName, $listener);
			$this->dispatcher->removeListener($eventName, $listener);
			$this->dispatcher->addListener($eventName, $listener->getWrappedListener(), $priority);
			$info = $this->getListenerInfo($listener->getWrappedListener(), $eventName);
			if( $listener->wasCalled() ) 
			{
				if( null !== $this->logger ) 
				{
					$this->logger->debug(sprintf("Notified event \"%s\" to listener \"%s\".", $eventName, $info["pretty"]));
				}
				if( !isset($this->called[$eventName]) ) 
				{
					$this->called[$eventName] = new \SplObjectStorage();
				}
				$this->called[$eventName]->attach($listener);
			}
			if( null !== $this->logger && $skipped ) 
			{
				$this->logger->debug(sprintf("Listener \"%s\" was not called for event \"%s\".", $info["pretty"], $eventName));
			}
			if( $listener->stoppedPropagation() ) 
			{
				if( null !== $this->logger ) 
				{
					$this->logger->debug(sprintf("Listener \"%s\" stopped propagation of the event \"%s\".", $info["pretty"], $eventName));
				}
				$skipped = true;
			}
		}
	}
	private function getListenerInfo($listener, $eventName) 
	{
		$info = array( "event" => $eventName, "priority" => $this->getListenerPriority($eventName, $listener) );
		if( $listener instanceof WrappedListener ) 
		{
			$listener = $listener->getWrappedListener();
		}
		if( $listener instanceof \Closure ) 
		{
			$info += array( "type" => "Closure", "pretty" => "closure" );
		}
		else 
		{
			if( is_string($listener) ) 
			{
				try 
				{
					$r = new \ReflectionFunction($listener);
					$file = $r->getFileName();
					$line = $r->getStartLine();
				}
				catch( \ReflectionException $e ) 
				{
					$file = null;
					$line = null;
				}
				$info += array( "type" => "Function", "function" => $listener, "file" => $file, "line" => $line, "pretty" => $listener );
			}
			else 
			{
				if( is_array($listener) || is_object($listener) && is_callable($listener) ) 
				{
					if( !is_array($listener) ) 
					{
						$listener = array( $listener, "__invoke" );
					}
					$class = (is_object($listener[0]) ? get_class($listener[0]) : $listener[0]);
					try 
					{
						$r = new \ReflectionMethod($class, $listener[1]);
						$file = $r->getFileName();
						$line = $r->getStartLine();
					}
					catch( \ReflectionException $e ) 
					{
						$file = null;
						$line = null;
					}
					$info += array( "type" => "Method", "class" => $class, "method" => $listener[1], "file" => $file, "line" => $line, "pretty" => $class . "::" . $listener[1] );
				}
			}
		}
		return $info;
	}
	private function sortListenersByPriority($a, $b) 
	{
		if( is_int($a["priority"]) && !is_int($b["priority"]) ) 
		{
			return 1;
		}
		if( !is_int($a["priority"]) && is_int($b["priority"]) ) 
		{
			return -1;
		}
		if( $a["priority"] === $b["priority"] ) 
		{
			return 0;
		}
		if( $b["priority"] < $a["priority"] ) 
		{
			return -1;
		}
		return 1;
	}
}
?>