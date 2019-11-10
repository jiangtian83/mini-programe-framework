<?php  namespace Symfony\Component\EventDispatcher\Debug;
class WrappedListener 
{
	private $listener = NULL;
	private $name = NULL;
	private $called = NULL;
	private $stoppedPropagation = NULL;
	private $stopwatch = NULL;
	private $dispatcher = NULL;
	public function __construct($listener, $name, \Symfony\Component\Stopwatch\Stopwatch $stopwatch, \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher = NULL) 
	{
		$this->listener = $listener;
		$this->name = $name;
		$this->stopwatch = $stopwatch;
		$this->dispatcher = $dispatcher;
		$this->called = false;
		$this->stoppedPropagation = false;
	}
	public function getWrappedListener() 
	{
		return $this->listener;
	}
	public function wasCalled() 
	{
		return $this->called;
	}
	public function stoppedPropagation() 
	{
		return $this->stoppedPropagation;
	}
	public function __invoke(\Symfony\Component\EventDispatcher\Event $event, $eventName, \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher) 
	{
		$this->called = true;
		$e = $this->stopwatch->start($this->name, "event_listener");
		call_user_func($this->listener, $event, $eventName, ($this->dispatcher ?: $dispatcher));
		if( $e->isStarted() ) 
		{
			$e->stop();
		}
		if( $event->isPropagationStopped() ) 
		{
			$this->stoppedPropagation = true;
		}
	}
}
?>