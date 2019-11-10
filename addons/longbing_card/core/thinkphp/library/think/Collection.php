<?php  namespace think;
class Collection implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable 
{
	protected $items = array( );
	public function __construct($items = array( )) 
	{
		$this->items = $this->convertToArray($items);
	}
	public static function make($items = array( )) 
	{
		return new static($items);
	}
	public function isEmpty() 
	{
		return empty($this->items);
	}
	public function toArray() 
	{
		return array_map(function($value) 
		{
			return ($value instanceof Model || $value instanceof $this ? $value->toArray() : $value);
		}
		, $this->items);
	}
	public function all() 
	{
		return $this->items;
	}
	public function flip() 
	{
		return new static(array_flip($this->items));
	}
	public function keys() 
	{
		return new static(array_keys($this->items));
	}
	public function merge($items) 
	{
		return new static(array_merge($this->items, $this->convertToArray($items)));
	}
	public function diff($items) 
	{
		return new static(array_diff($this->items, $this->convertToArray($items)));
	}
	public function intersect($items) 
	{
		return new static(array_intersect($this->items, $this->convertToArray($items)));
	}
	public function pop() 
	{
		return array_pop($this->items);
	}
	public function shift() 
	{
		return array_shift($this->items);
	}
	public function unshift($value, $key = NULL) 
	{
		if( is_null($key) ) 
		{
			array_unshift($this->items, $value);
		}
		else 
		{
			$this->items = array( $key => $value ) + $this->items;
		}
	}
	public function push($value, $key = NULL) 
	{
		if( is_null($key) ) 
		{
			$this->items[] = $value;
		}
		else 
		{
			$this->items[$key] = $value;
		}
	}
	public function reduce(callable $callback, $initial = NULL) 
	{
		return array_reduce($this->items, $callback, $initial);
	}
	public function reverse() 
	{
		return new static(array_reverse($this->items));
	}
	public function chunk($size, $preserveKeys = false) 
	{
		$chunks = array( );
		foreach( array_chunk($this->items, $size, $preserveKeys) as $chunk ) 
		{
			$chunks[] = new static($chunk);
		}
		return new static($chunks);
	}
	public function each(callable $callback) 
	{
		foreach( $this->items as $key => $item ) 
		{
			$result = $callback($item, $key);
			if( false === $result ) 
			{
				break;
			}
			if( !is_object($item) ) 
			{
				$this->items[$key] = $result;
			}
		}
		return $this;
	}
	public function filter(callable $callback = NULL) 
	{
		return new static(array_filter($this->items, ($callback ?: null)));
	}
	public function column($columnKey, $indexKey = NULL) 
	{
		if( function_exists("array_column") ) 
		{
			return array_column($this->items, $columnKey, $indexKey);
		}
		$result = array( );
		foreach( $this->items as $row ) 
		{
			$key = $value = null;
			$keySet = $valueSet = false;
			if( null !== $indexKey && array_key_exists($indexKey, $row) ) 
			{
				$key = (string) $row[$indexKey];
				$keySet = true;
			}
			if( null === $columnKey ) 
			{
				$valueSet = true;
				$value = $row;
			}
			else 
			{
				if( is_array($row) && array_key_exists($columnKey, $row) ) 
				{
					$valueSet = true;
					$value = $row[$columnKey];
				}
			}
			if( $valueSet ) 
			{
				if( $keySet ) 
				{
					$result[$key] = $value;
				}
				else 
				{
					$result[] = $value;
				}
			}
		}
		return $result;
	}
	public function sort(callable $callback = NULL) 
	{
		$items = $this->items;
		$callback = ($callback ?: function($a, $b) 
		{
			return ($a == $b ? 0 : ($a < $b ? -1 : 1));
		}
		);
		uasort($items, $callback);
		return new static($items);
	}
	public function shuffle() 
	{
		$items = $this->items;
		shuffle($items);
		return new static($items);
	}
	public function slice($offset, $length = NULL, $preserveKeys = false) 
	{
		return new static(array_slice($this->items, $offset, $length, $preserveKeys));
	}
	public function offsetExists($offset) 
	{
		return array_key_exists($offset, $this->items);
	}
	public function offsetGet($offset) 
	{
		return $this->items[$offset];
	}
	public function offsetSet($offset, $value) 
	{
		if( is_null($offset) ) 
		{
			$this->items[] = $value;
		}
		else 
		{
			$this->items[$offset] = $value;
		}
	}
	public function offsetUnset($offset) 
	{
		unset($this->items[$offset]);
	}
	public function count() 
	{
		return count($this->items);
	}
	public function getIterator() 
	{
		return new \ArrayIterator($this->items);
	}
	public function jsonSerialize() 
	{
		return $this->toArray();
	}
	public function toJson($options = JSON_UNESCAPED_UNICODE) 
	{
		return json_encode($this->toArray(), $options);
	}
	public function __toString() 
	{
		return $this->toJson();
	}
	protected function convertToArray($items) 
	{
		return ($items instanceof $this ? $items->all() : (array) $items);
	}
}
?>