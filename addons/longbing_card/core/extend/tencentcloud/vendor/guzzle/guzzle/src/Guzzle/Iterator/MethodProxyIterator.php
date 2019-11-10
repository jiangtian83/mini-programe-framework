<?php  namespace Guzzle\Iterator;
class MethodProxyIterator extends \IteratorIterator 
{
	public function __call($name, array $args) 
	{
		$i = $this->getInnerIterator();
		while( $i instanceof \OuterIterator ) 
		{
			$i = $i->getInnerIterator();
		}
		return call_user_func_array(array( $i, $name ), $args);
	}
}
?>