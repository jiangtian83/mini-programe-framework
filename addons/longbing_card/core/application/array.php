<?php  if( !function_exists("array_column") ) 
{
	function array_column($input, $columnKey, $indexKey = NULL) 
	{
		$columnKeyIsNumber = (is_numeric($columnKey) ? true : false);
		$indexKeyIsNull = (is_null($indexKey) ? true : false);
		$indexKeyIsNumber = (is_numeric($indexKey) ? true : false);
		$result = array( );
		foreach( (array) $input as $key => $row ) 
		{
			if( $columnKeyIsNumber ) 
			{
				$tmp = array_slice($row, $columnKey, 1);
				$tmp = (is_array($tmp) && !empty($tmp) ? current($tmp) : NULL);
			}
			else 
			{
				$tmp = (isset($row[$columnKey]) ? $row[$columnKey] : NULL);
			}
			if( !$indexKeyIsNull ) 
			{
				if( $indexKeyIsNumber ) 
				{
					$key = array_slice($row, $indexKey, 1);
					$key = (is_array($key) && !empty($key) ? current($key) : NULL);
					$key = (is_null($key) ? 0 : $key);
				}
				else 
				{
					$key = (isset($row[$indexKey]) ? $row[$indexKey] : 0);
				}
			}
			$result[$key] = $tmp;
		}
		return $result;
	}
}
function list_to_tree($list, $pk = "id", $pid = "pid", $child = "_child", $root = 0, $is_count = false) 
{
	$tree = array( );
	if( is_array($list) ) 
	{
		$refer = array( );
		foreach( $list as $key => $data ) 
		{
			$refer[$data[$pk]] =& $list[$key];
		}
		foreach( $list as $key => $data ) 
		{
			$parentId = $data[$pid];
			if( $root == $parentId ) 
			{
				$tree[] =& $list[$key];
			}
			else 
			{
				if( isset($refer[$parentId]) ) 
				{
					$parent =& $refer[$parentId];
					$parent[$child][] =& $list[$key];
					if( $is_count == true ) 
					{
						$parent["_count"] = count($parent[$child]);
					}
				}
			}
		}
	}
	return $tree;
}
function genTree($items, $pk = "id", $pid = "pid", $child = "_child") 
{
	$tree = array( );
	foreach( $items as $item ) 
	{
		if( isset($items[$item[$pid]]) ) 
		{
			$items[$item[$pid]][$child][] =& $items[$item[$pk]];
		}
		else 
		{
			$tree[] =& $items[$item[$pk]];
		}
	}
	return $tree;
}
function combineDika() 
{
	$data = func_get_args();
	$data = current($data);
	$cnt = count($data);
	$result = array( );
	$arr1 = array_shift($data);
	foreach( $arr1 as $key => $item ) 
	{
		$result[] = array( $item );
	}
	foreach( $data as $key => $item ) 
	{
		$result = combineArray($result, $item);
	}
	return $result;
}
function combineArray($arr1 = array( ), $arr2 = array( )) 
{
	$result = array( );
	foreach( $arr1 as $item1 ) 
	{
		foreach( $arr2 as $item2 ) 
		{
			$temp = $item1;
			$temp[] = $item2;
			$result[] = $temp;
		}
	}
	return $result;
}
function group_same_key($arr, $key) 
{
	$new_arr = array( );
	foreach( $arr as $k => $v ) 
	{
		$new_arr[$v[$key]][] = $v;
	}
	return $new_arr;
}
function convert_arr_key($arr, $key_name = "id") 
{
	$arr2 = array( );
	foreach( $arr as $key => $val ) 
	{
		$arr2[$val[$key_name]] = $val;
	}
	return $arr2;
}
function array_to_object($arr) 
{
	if( gettype($arr) != "array" ) 
	{
		return NULL;
	}
	foreach( $arr as $k => $v ) 
	{
		if( gettype($v) == "array" || getType($v) == "object" ) 
		{
			$arr[$k] = (object) array_to_object($v);
		}
	}
	return (object) $arr;
}
function object_to_array($obj) 
{
	$obj = (array) $obj;
	foreach( $obj as $k => $v ) 
	{
		if( gettype($v) == "resource" ) 
		{
			return NULL;
		}
		if( gettype($v) == "object" || gettype($v) == "array" ) 
		{
			$obj[$k] = (array) object_to_array($v);
		}
	}
	return $obj;
}
function array_delete($array, $value) 
{
	$key = array_search($value, $array);
	if( $key !== false ) 
	{
		array_splice($array, $key, 1);
	}
	return $array;
}
function parse_config_attr($value, $type = NULL, $checkValArr = NULL) 
{
	switch( $type ) 
	{
		case 1: $array = preg_split("/[,;\\r\\n]+/", trim($value, ",;\r\n"));
		if( strpos($value, ":") ) 
		{
			$value = array( );
			foreach( $array as $val ) 
			{
				list($k, $v) = explode(":", $val);
				$item = array( );
				$item["name"] = $v;
				$item["value"] = $k;
				if( $checkValArr ) 
				{
					$item["checked"] = in_array($k, $checkValArr);
				}
				else 
				{
					$item["checked"] = false;
				}
				$value[] = $item;
			}
		}
		else 
		{
			$value = $array;
		}
		break;
		default: $array = preg_split("/[,;\\r\\n]+/", trim($value, ",;\r\n"));
		if( strpos($value, ":") ) 
		{
			$value = array( );
			foreach( $array as $val ) 
			{
				list($k, $v) = explode(":", $val);
				$value[$k] = $v;
			}
		}
		else 
		{
			$value = $array;
		}
		break;
	}
	return $value;
}
?>