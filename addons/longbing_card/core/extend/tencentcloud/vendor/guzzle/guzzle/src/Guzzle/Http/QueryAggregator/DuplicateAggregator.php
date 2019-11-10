<?php  namespace Guzzle\Http\QueryAggregator;
class DuplicateAggregator implements QueryAggregatorInterface 
{
	public function aggregate($key, $value, \Guzzle\Http\QueryString $query) 
	{
		if( $query->isUrlEncoding() ) 
		{
			return array( $query->encodeValue($key) => array_map(array( $query, "encodeValue" ), $value) );
		}
		return array( $key => $value );
	}
}
?>