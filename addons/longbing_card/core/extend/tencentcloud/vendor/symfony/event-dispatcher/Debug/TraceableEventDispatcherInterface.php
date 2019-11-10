<?php  namespace Symfony\Component\EventDispatcher\Debug;
interface TraceableEventDispatcherInterface extends \Symfony\Component\EventDispatcher\EventDispatcherInterface 
{
	public function getCalledListeners();
	public function getNotCalledListeners();
}
?>