<?php  namespace think\db\builder;
class Pgsql extends \think\db\Builder 
{
	protected $insertSql = "INSERT INTO %TABLE% (%FIELD%) VALUES (%DATA%) %COMMENT%";
	protected $insertAllSql = "INSERT INTO %TABLE% (%FIELD%) %DATA% %COMMENT%";
	public function parseLimit($limit) 
	{
		$limitStr = "";
		if( !empty($limit) ) 
		{
			$limit = explode(",", $limit);
			if( 1 < count($limit) ) 
			{
				$limitStr .= " LIMIT " . $limit[1] . " OFFSET " . $limit[0] . " ";
			}
			else 
			{
				$limitStr .= " LIMIT " . $limit[0] . " ";
			}
		}
		return $limitStr;
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
			$key = $field . "->>'" . $name . "'";
		}
		else 
		{
			if( strpos($key, ".") ) 
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
		}
		if( isset($table) ) 
		{
			$key = $table . "." . $key;
		}
		return $key;
	}
	protected function parseRand() 
	{
		return "RANDOM()";
	}
}
?>