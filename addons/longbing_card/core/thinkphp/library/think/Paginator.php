<?php  namespace think;
abstract class Paginator implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable 
{
	protected $simple = false;
	protected $items = NULL;
	protected $currentPage = NULL;
	protected $lastPage = NULL;
	protected $total = NULL;
	protected $listRows = NULL;
	protected $hasMore = NULL;
	protected $options = array( "var_page" => "page", "path" => "/", "query" => array( ), "fragment" => "" );
	protected $nextItem = NULL;
	public function __construct($items, $listRows, $currentPage = NULL, $total = NULL, $simple = false, $options = array( )) 
	{
		$this->options = array_merge($this->options, $options);
		$this->options["path"] = ("/" != $this->options["path"] ? rtrim($this->options["path"], "/") : $this->options["path"]);
		$this->simple = $simple;
		$this->listRows = $listRows;
		if( !$items instanceof Collection ) 
		{
			$items = Collection::make($items);
		}
		if( $simple ) 
		{
			$this->currentPage = $this->setCurrentPage($currentPage);
			$this->hasMore = $this->listRows < count($items);
			if( $this->hasMore ) 
			{
				$this->nextItem = $items->slice($this->listRows, 1);
			}
			$items = $items->slice(0, $this->listRows);
		}
		else 
		{
			$this->total = $total;
			$this->lastPage = (int) ceil($total / $listRows);
			$this->currentPage = $this->setCurrentPage($currentPage);
			$this->hasMore = $this->currentPage < $this->lastPage;
		}
		$this->items = $items;
	}
	public static function make($items, $listRows, $currentPage = NULL, $total = NULL, $simple = false, $options = array( )) 
	{
		return new static($items, $listRows, $currentPage, $total, $simple, $options);
	}
	protected function setCurrentPage($currentPage) 
	{
		if( !$this->simple && $this->lastPage < $currentPage ) 
		{
			return (0 < $this->lastPage ? $this->lastPage : 1);
		}
		return $currentPage;
	}
	protected function url($page) 
	{
		if( $page <= 0 ) 
		{
			$page = 1;
		}
		if( strpos($this->options["path"], "[PAGE]") === false ) 
		{
			$parameters = array( $this->options["var_page"] => $page );
			$path = $this->options["path"];
		}
		else 
		{
			$parameters = array( );
			$path = str_replace("[PAGE]", $page, $this->options["path"]);
		}
		if( 0 < count($this->options["query"]) ) 
		{
			$parameters = array_merge($this->options["query"], $parameters);
		}
		$url = $path;
		if( !empty($parameters) ) 
		{
			$url .= "?" . http_build_query($parameters, null, "&");
		}
		return $url . $this->buildFragment();
	}
	public static function getCurrentPage($varPage = "page", $default = 1) 
	{
		$page = (int) Request::instance()->param($varPage);
		if( filter_var($page, FILTER_VALIDATE_INT) !== false && 1 <= $page ) 
		{
			return $page;
		}
		return $default;
	}
	public static function getCurrentPath() 
	{
		return Request::instance()->baseUrl();
	}
	public function total() 
	{
		if( $this->simple ) 
		{
			throw new \DomainException("not support total");
		}
		return $this->total;
	}
	public function listRows() 
	{
		return $this->listRows;
	}
	public function currentPage() 
	{
		return $this->currentPage;
	}
	public function lastPage() 
	{
		if( $this->simple ) 
		{
			throw new \DomainException("not support last");
		}
		return $this->lastPage;
	}
	public function hasPages() 
	{
		return 1 != $this->currentPage || $this->hasMore;
	}
	public function getUrlRange($start, $end) 
	{
		$urls = array( );
		for( $page = $start; $page <= $end; $page++ ) 
		{
			$urls[$page] = $this->url($page);
		}
		return $urls;
	}
	public function fragment($fragment) 
	{
		$this->options["fragment"] = $fragment;
		return $this;
	}
	public function appends($key, $value = NULL) 
	{
		if( !is_array($key) ) 
		{
			$queries = array( $key => $value );
		}
		else 
		{
			$queries = $key;
		}
		foreach( $queries as $k => $v ) 
		{
			if( $k !== $this->options["var_page"] ) 
			{
				$this->options["query"][$k] = $v;
			}
		}
		return $this;
	}
	protected function buildFragment() 
	{
		return ($this->options["fragment"] ? "#" . $this->options["fragment"] : "");
	}
	abstract public function render();
	public function items() 
	{
		return $this->items->all();
	}
	public function getCollection() 
	{
		return $this->items;
	}
	public function isEmpty() 
	{
		return $this->items->isEmpty();
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
	public function getIterator() 
	{
		return new \ArrayIterator($this->items->all());
	}
	public function offsetExists($offset) 
	{
		return $this->items->offsetExists($offset);
	}
	public function offsetGet($offset) 
	{
		return $this->items->offsetGet($offset);
	}
	public function offsetSet($offset, $value) 
	{
		$this->items->offsetSet($offset, $value);
	}
	public function offsetUnset($offset) 
	{
		$this->items->offsetUnset($offset);
	}
	public function count() 
	{
		return $this->items->count();
	}
	public function __toString() 
	{
		return (string) $this->render();
	}
	public function toArray() 
	{
		if( $this->simple ) 
		{
			return array( "per_page" => $this->listRows, "current_page" => $this->currentPage, "has_more" => $this->hasMore, "next_item" => $this->nextItem, "data" => $this->items->toArray() );
		}
		return array( "total" => $this->total, "per_page" => $this->listRows, "current_page" => $this->currentPage, "last_page" => $this->lastPage, "data" => $this->items->toArray() );
	}
	public function jsonSerialize() 
	{
		return $this->toArray();
	}
	public function __call($name, $arguments) 
	{
		$collection = $this->getCollection();
		$result = call_user_func_array(array( $collection, $name ), $arguments);
		if( $result === $collection ) 
		{
			return $this;
		}
		return $result;
	}
}
?>