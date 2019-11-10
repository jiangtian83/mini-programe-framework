<?php  namespace think\db\builder;
class Sqlsrv extends \think\db\Builder 
{
	protected $selectSql = "SELECT T1.* FROM (SELECT thinkphp.*, ROW_NUMBER() OVER (%ORDER%) AS ROW_NUMBER FROM (SELECT %DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%) AS thinkphp) AS T1 %LIMIT%%COMMENT%";
	protected $selectInsertSql = "SELECT %DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%";
	protected $updateSql = "UPDATE %TABLE% SET %SET% FROM %TABLE% %JOIN% %WHERE% %LIMIT% %LOCK%%COMMENT%";
	protected $deleteSql = "DELETE FROM %TABLE%  %USING% FROM %TABLE%  %JOIN% %WHERE% %LIMIT% %LOCK%%COMMENT%";
	protected $insertSql = "INSERT INTO %TABLE% (%FIELD%) VALUES (%DATA%) %COMMENT%";
	protected $insertAllSql = "INSERT INTO %TABLE% (%FIELD%) %DATA% %COMMENT%";
	protected function parseOrder($order, $options = array( )) 
	{
		if( empty($order) ) 
		{
			return " ORDER BY rand()";
		}
		$array = array( );
		foreach( $order as $key => $val ) 
		{
			if( $val instanceof \think\db\Expression ) 
			{
				$array[] = $val->getValue();
			}
			else 
			{
				if( is_numeric($key) ) 
				{
					if( false === strpos($val, "(") ) 
					{
						$array[] = $this->parseKey($val, $options);
					}
					else 
					{
						if( "[rand]" == $val ) 
						{
							$array[] = $this->parseRand();
						}
						else 
						{
							$array[] = $val;
						}
					}
				}
				else 
				{
					$sort = (in_array(strtolower(trim($val)), array( "asc", "desc" ), true) ? " " . $val : "");
					$array[] = $this->parseKey($key, $options, true) . " " . $sort;
				}
			}
		}
		return " ORDER BY " . implode(",", $array);
	}
	protected function parseRand() 
	{
		return "rand()";
	}
	protected function parseKey($key, $options = array( ), $strict = false) 
	{
		if( is_numeric($key) ) 
		{
			return $key;
		}
		if( $key instanceof \think\db\Expression ) 
		{
			return $key->getValue();
		}
		$key = trim($key);
		if( strpos($key, ".") && !preg_match("/[,'\\\"\\(\\)\\[\\s]/", $key) ) 
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
		if( "*" != $key && ($strict || !preg_match("/[,'\\\"\\*\\(\\)\\[.\\s]/", $key)) ) 
		{
			$key = "[" . $key . "]";
		}
		if( isset($table) ) 
		{
			$key = "[" . $table . "]." . $key;
		}
		return $key;
	}
	protected function parseLimit($limit) 
	{
		if( empty($limit) ) 
		{
			return "";
		}
		$limit = explode(",", $limit);
		if( 1 < count($limit) ) 
		{
			$limitStr = "(T1.ROW_NUMBER BETWEEN " . $limit[0] . " + 1 AND " . $limit[0] . " + " . $limit[1] . ")";
		}
		else 
		{
			$limitStr = "(T1.ROW_NUMBER BETWEEN 1 AND " . $limit[0] . ")";
		}
		return "WHERE " . $limitStr;
	}
	public function selectInsert($fields, $table, $options) 
	{
		$this->selectSql = $this->selectInsertSql;
		return parent::selectInsert($fields, $table, $options);
	}
}
?>