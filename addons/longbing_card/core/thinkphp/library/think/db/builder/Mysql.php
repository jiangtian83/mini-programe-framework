<?php  namespace think\db\builder;
class Mysql extends \think\db\Builder 
{
	protected $insertAllSql = "%INSERT% INTO %TABLE% (%FIELD%) VALUES %DATA% %COMMENT%";
	protected $updateSql = "UPDATE %TABLE% %JOIN% SET %SET% %WHERE% %ORDER%%LIMIT% %LOCK%%COMMENT%";
	public function insertAll($dataSet, $options = array( ), $replace = false) 
	{
		if( "*" == $options["field"] ) 
		{
			$fields = array_keys($this->query->getFieldsType($options["table"]));
		}
		else 
		{
			$fields = $options["field"];
		}
		foreach( $dataSet as $data ) 
		{
			foreach( $data as $key => $val ) 
			{
				if( !in_array($key, $fields, true) ) 
				{
					if( $options["strict"] ) 
					{
						throw new \think\Exception("fields not exists:[" . $key . "]");
					}
					unset($data[$key]);
				}
				else 
				{
					if( is_null($val) ) 
					{
						$data[$key] = "NULL";
					}
					else 
					{
						if( is_scalar($val) ) 
						{
							$data[$key] = $this->parseValue($val, $key);
						}
						else 
						{
							if( is_object($val) && method_exists($val, "__toString") ) 
							{
								$data[$key] = $val->__toString();
							}
							else 
							{
								unset($data[$key]);
							}
						}
					}
				}
			}
			$value = array_values($data);
			$values[] = "( " . implode(",", $value) . " )";
			if( !isset($insertFields) ) 
			{
				$insertFields = array_map(array( $this, "parseKey" ), array_keys($data));
			}
		}
		return str_replace(array( "%INSERT%", "%TABLE%", "%FIELD%", "%DATA%", "%COMMENT%" ), array( ($replace ? "REPLACE" : "INSERT"), $this->parseTable($options["table"], $options), implode(" , ", $insertFields), implode(" , ", $values), $this->parseComment($options["comment"]) ), $this->insertAllSql);
	}
	protected function parseKey($key, $options = array( ), $strict = false) 
	{
		if( is_numeric($key) ) 
		{
			return $key;
		}
		if( $key instanceof Expression ) 
		{
			return $key->getValue();
		}
		$key = trim($key);
		if( strpos($key, "\$.") && false === strpos($key, "(") ) 
		{
			list($field, $name) = explode("\$.", $key);
			return "json_extract(" . $field . ", '\$." . $name . "')";
		}
		if( strpos($key, ".") && !preg_match("/[,'\\\"\\(\\)`\\s]/", $key) ) 
		{
			list($table, $key) = explode(".", $key, 2);
			if( "__TABLE__" == $table ) 
			{
				$table = $this->query->getTable();
			}
			if( isset($options["alias"][$table]) ) 
			{
				$table = $options["alias"][$table];
			}
		}
		if( "*" != $key && ($strict || !preg_match("/[,'\\\"\\*\\(\\)`.\\s]/", $key)) ) 
		{
			$key = "`" . $key . "`";
		}
		if( isset($table) ) 
		{
			if( strpos($table, ".") ) 
			{
				$table = str_replace(".", "`.`", $table);
			}
			$key = "`" . $table . "`." . $key;
		}
		return $key;
	}
	protected function parseRand() 
	{
		return "rand()";
	}
}
?>