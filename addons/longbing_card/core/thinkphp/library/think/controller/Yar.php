<?php  namespace think\controller;
abstract class Yar 
{
	public function __construct() 
	{
		if( method_exists($this, "_initialize") ) 
		{
			$this->_initialize();
		}
		if( !extension_loaded("yar") ) 
		{
			throw new \Exception("not support yar");
		}
		$server = new \Yar_Server($this);
		$server->handle();
	}
	public function __call($method, $args) 
	{
	}
}
?>