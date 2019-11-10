<?php  namespace Guzzle\Tests\Http\Message;
class HeaderComparison 
{
	public function compare($filteredHeaders, $actualHeaders) 
	{
		$expected = array( );
		$ignore = array( );
		$absent = array( );
		if( $actualHeaders instanceof \Guzzle\Http\Message\Header\HeaderCollection ) 
		{
			$actualHeaders = $actualHeaders->toArray();
		}
		foreach( $filteredHeaders as $k => $v ) 
		{
			if( $k[0] == "_" ) 
			{
				$ignore[] = str_replace("_", "", $k);
			}
			else 
			{
				if( $k[0] == "!" ) 
				{
					$absent[] = str_replace("!", "", $k);
				}
				else 
				{
					$expected[$k] = $v;
				}
			}
		}
		return $this->compareArray($expected, $actualHeaders, $ignore, $absent);
	}
	public function compareArray(array $expected, $actual, array $ignore = array( ), array $absent = array( )) 
	{
		$differences = array( );
		foreach( $absent as $header ) 
		{
			if( $this->hasKey($header, $actual) ) 
			{
				$differences["++ " . $header] = $actual[$header];
				unset($actual[$header]);
			}
		}
		foreach( $expected as $header => $value ) 
		{
			if( !$this->hasKey($header, $actual) ) 
			{
				$differences["- " . $header] = $value;
			}
		}
		$ignore = array_flip($ignore);
		$expected = array_change_key_case($expected);
		foreach( $actual as $key => $value ) 
		{
			if( $this->hasKey($key, $ignore) ) 
			{
				continue;
			}
			if( !$this->hasKey($key, $expected) ) 
			{
				$differences["+ " . $key] = $value;
				continue;
			}
			$lkey = strtolower($key);
			$pos = (is_string($expected[$lkey]) ? strpos($expected[$lkey], "*") : false);
			foreach( (array) $actual[$key] as $v ) 
			{
				if( $pos === false && $v != $expected[$lkey] || 0 < $pos && substr($v, 0, $pos) != substr($expected[$lkey], 0, $pos) ) 
				{
					$differences[$key] = (string) $value . " != " . $expected[$lkey];
				}
			}
		}
		return (empty($differences) ? false : $differences);
	}
	protected function hasKey($key, $array) 
	{
		if( $array instanceof \Guzzle\Common\Collection ) 
		{
			$keys = $array->getKeys();
		}
		else 
		{
			$keys = array_keys($array);
		}
		foreach( $keys as $k ) 
		{
			if( !strcasecmp($k, $key) ) 
			{
				return true;
			}
		}
		return false;
	}
}
?>