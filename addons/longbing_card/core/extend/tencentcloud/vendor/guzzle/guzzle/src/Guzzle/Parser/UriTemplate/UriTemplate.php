<?php  namespace Guzzle\Parser\UriTemplate;
class UriTemplate implements UriTemplateInterface 
{
	private $template = NULL;
	private $variables = NULL;
	private $regex = self::DEFAULT_PATTERN;
	private static $operatorHash = array( "+" => true, "#" => true, "." => true, "/" => true, ";" => true, "?" => true, "&" => true );
	private static $delims = array( ":", "/", "?", "#", "[", "]", "@", "!", "\$", "&", "'", "(", ")", "*", "+", ",", ";", "=" );
	private static $delimsPct = array( "%3A", "%2F", "%3F", "%23", "%5B", "%5D", "%40", "%21", "%24", "%26", "%27", "%28", "%29", "%2A", "%2B", "%2C", "%3B", "%3D" );
	const DEFAULT_PATTERN = "/\\{([^\\}]+)\\}/";
	public function expand($template, array $variables) 
	{
		if( $this->regex == self::DEFAULT_PATTERN && false === strpos($template, "{") ) 
		{
			return $template;
		}
		$this->template = $template;
		$this->variables = $variables;
		return preg_replace_callback($this->regex, array( $this, "expandMatch" ), $this->template);
	}
	public function setRegex($regexPattern) 
	{
		$this->regex = $regexPattern;
	}
	private function parseExpression($expression) 
	{
		$operator = "";
		if( isset(self::$operatorHash[$expression[0]]) ) 
		{
			$operator = $expression[0];
			$expression = substr($expression, 1);
		}
		$values = explode(",", $expression);
		foreach( $values as &$value ) 
		{
			$value = trim($value);
			$varspec = array( );
			$substrPos = strpos($value, ":");
			if( $substrPos ) 
			{
				$varspec["value"] = substr($value, 0, $substrPos);
				$varspec["modifier"] = ":";
				$varspec["position"] = (int) substr($value, $substrPos + 1);
			}
			else 
			{
				if( substr($value, -1) == "*" ) 
				{
					$varspec["modifier"] = "*";
					$varspec["value"] = substr($value, 0, -1);
				}
				else 
				{
					$varspec["value"] = (string) $value;
					$varspec["modifier"] = "";
				}
			}
			$value = $varspec;
		}
		return array( "operator" => $operator, "values" => $values );
	}
	private function expandMatch(array $matches) 
	{
		static $rfc1738to3986 = array( "+" => "%20", "%7e" => "~" );
		$parsed = self::parseExpression($matches[1]);
		$replacements = array( );
		$prefix = $parsed["operator"];
		$joiner = $parsed["operator"];
		$useQueryString = false;
		if( $parsed["operator"] == "?" ) 
		{
			$joiner = "&";
			$useQueryString = true;
		}
		else 
		{
			if( $parsed["operator"] == "&" ) 
			{
				$useQueryString = true;
			}
			else 
			{
				if( $parsed["operator"] == "#" ) 
				{
					$joiner = ",";
				}
				else 
				{
					if( $parsed["operator"] == ";" ) 
					{
						$useQueryString = true;
					}
					else 
					{
						if( $parsed["operator"] == "" || $parsed["operator"] == "+" ) 
						{
							$joiner = ",";
							$prefix = "";
						}
					}
				}
			}
		}
		foreach( $parsed["values"] as $value ) 
		{
			if( !array_key_exists($value["value"], $this->variables) || $this->variables[$value["value"]] === null ) 
			{
				continue;
			}
			$variable = $this->variables[$value["value"]];
			$actuallyUseQueryString = $useQueryString;
			$expanded = "";
			if( is_array($variable) ) 
			{
				$isAssoc = $this->isAssoc($variable);
				$kvp = array( );
				foreach( $variable as $key => $var ) 
				{
					if( $isAssoc ) 
					{
						$key = rawurlencode($key);
						$isNestedArray = is_array($var);
					}
					else 
					{
						$isNestedArray = false;
					}
					if( !$isNestedArray ) 
					{
						$var = rawurlencode($var);
						if( $parsed["operator"] == "+" || $parsed["operator"] == "#" ) 
						{
							$var = $this->decodeReserved($var);
						}
					}
					if( $value["modifier"] == "*" ) 
					{
						if( $isAssoc ) 
						{
							if( $isNestedArray ) 
							{
								$var = strtr(http_build_query(array( $key => $var )), $rfc1738to3986);
							}
							else 
							{
								$var = $key . "=" . $var;
							}
						}
						else 
						{
							if( 0 < $key && $actuallyUseQueryString ) 
							{
								$var = $value["value"] . "=" . $var;
							}
						}
					}
					$kvp[$key] = $var;
				}
				if( empty($variable) ) 
				{
					$actuallyUseQueryString = false;
				}
				else 
				{
					if( $value["modifier"] == "*" ) 
					{
						$expanded = implode($joiner, $kvp);
						if( $isAssoc ) 
						{
							$actuallyUseQueryString = false;
						}
					}
					else 
					{
						if( $isAssoc ) 
						{
							foreach( $kvp as $k => &$v ) 
							{
								$v = $k . "," . $v;
							}
						}
						$expanded = implode(",", $kvp);
					}
				}
			}
			else 
			{
				if( $value["modifier"] == ":" ) 
				{
					$variable = substr($variable, 0, $value["position"]);
				}
				$expanded = rawurlencode($variable);
				if( $parsed["operator"] == "+" || $parsed["operator"] == "#" ) 
				{
					$expanded = $this->decodeReserved($expanded);
				}
			}
			if( $actuallyUseQueryString ) 
			{
				if( !$expanded && $joiner != "&" ) 
				{
					$expanded = $value["value"];
				}
				else 
				{
					$expanded = $value["value"] . "=" . $expanded;
				}
			}
			$replacements[] = $expanded;
		}
		$ret = implode($joiner, $replacements);
		if( $ret && $prefix ) 
		{
			return $prefix . $ret;
		}
		return $ret;
	}
	private function isAssoc(array $array) 
	{
		return (bool) count(array_filter(array_keys($array), "is_string"));
	}
	private function decodeReserved($string) 
	{
		return str_replace(self::$delimsPct, self::$delims, $string);
	}
}
?>