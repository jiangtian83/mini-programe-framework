<?php  namespace Guzzle\Inflection;
class PreComputedInflector implements InflectorInterface 
{
	protected $mapping = array( "snake" => array( ), "camel" => array( ) );
	protected $decoratedInflector = NULL;
	public function __construct(InflectorInterface $inflector, array $snake = array( ), array $camel = array( ), $mirror = false) 
	{
		if( $mirror ) 
		{
			$camel = array_merge(array_flip($snake), $camel);
			$snake = array_merge(array_flip($camel), $snake);
		}
		$this->decoratedInflector = $inflector;
		$this->mapping = array( "snake" => $snake, "camel" => $camel );
	}
	public function snake($word) 
	{
		return (isset($this->mapping["snake"][$word]) ? $this->mapping["snake"][$word] : $this->decoratedInflector->snake($word));
	}
	public function camel($word) 
	{
		return (isset($this->mapping["camel"][$word]) ? $this->mapping["camel"][$word] : $this->decoratedInflector->camel($word));
	}
}
?>