<?php  namespace Guzzle\Http\QueryAggregator;
class PhpAggregator implements QueryAggregatorInterface 
{
	public function aggregate($key, $value, \Guzzle\Http\QueryString $query) 
	{
		$ret = array( );
		foreach( $value as $k => $v ) 
		{
			$k = (string) $key . "[" . $k . "]";
			if( is_array($v) ) 
			{
				$ret = array_merge($ret, self::aggregate($k, $v, $query));
			}
			else 
			{
				$ret[$query->encodeValue($k)] = $query->encodeValue($v);
			}
		}
		return $ret;
	}
}
?>