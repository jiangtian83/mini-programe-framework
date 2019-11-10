<?php  namespace Symfony\Component\EventDispatcher;
class Event 
{
	private $propagationStopped = false;
	private $dispatcher = NULL;
	private $name = NULL;
	public function isPropagationStopped() 
	{
		return $this->propagationStopped;
	}
	public function stopPropagation() 
	{
		$this->propagationStopped = true;
	}
	public function setDispatcher(EventDispatcherInterface $dispatcher) 
	{
		$this->dispatcher = $dispatcher;
	}
	public function getDispatcher() 
	{
		@trigger_error("The " . "Symfony\\Component\\EventDispatcher\\Event::getDispatcher" . " method is deprecated since Symfony 2.4 and will be removed in 3.0. The event dispatcher instance can be received in the listener call instead.", E_USER_DEPRECATED);
		return $this->dispatcher;
	}
	public function getName() 
	{
		@trigger_error("The " . "Symfony\\Component\\EventDispatcher\\Event::getName" . " method is deprecated since Symfony 2.4 and will be removed in 3.0. The event name can be received in the listener call instead.", E_USER_DEPRECATED);
		return $this->name;
	}
	public function setName($name) 
	{
		$this->name = $name;
	}
}
?>