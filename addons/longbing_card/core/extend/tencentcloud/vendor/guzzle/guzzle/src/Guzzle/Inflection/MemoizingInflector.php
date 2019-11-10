<?php  namespace Guzzle\Inflection;
class MemoizingInflector implements InflectorInterface 
{
	protected $cache = array( "snake" => array( ), "camel" => array( ) );
	protected $maxCacheSize = NULL;
	protected $decoratedInflector = NULL;
	public function __construct(InflectorInterface $inflector, $maxCacheSize = 500) 
	{
		$this->decoratedInflector = $inflector;
		$this->maxCacheSize = $maxCacheSize;
	}
	public function snake($word) 
	{
		if( !isset($this->cache["snake"][$word]) ) 
		{
			$this->pruneCache("snake");
			$this->cache["snake"][$word] = $this->decoratedInflector->snake($word);
		}
		return $this->cache["snake"][$word];
	}
	public function camel($word) 
	{
		if( !isset($this->cache["camel"][$word]) ) 
		{
			$this->pruneCache("camel");
			$this->cache["camel"][$word] = $this->decoratedInflector->camel($word);
		}
		return $this->cache["camel"][$word];
	}
	protected function pruneCache($cache) 
	{
		if( count($this->cache[$cache]) == $this->maxCacheSize ) 
		{
			$this->cache[$cache] = array_slice($this->cache[$cache], $this->maxCacheSize * 0.2);
		}
	}
}
?>