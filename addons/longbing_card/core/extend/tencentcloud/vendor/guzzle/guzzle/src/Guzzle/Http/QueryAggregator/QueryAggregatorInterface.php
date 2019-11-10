<?php  namespace Guzzle\Http\QueryAggregator;
interface QueryAggregatorInterface 
{
	public function aggregate($key, $value, \Guzzle\Http\QueryString $query);
}
?>