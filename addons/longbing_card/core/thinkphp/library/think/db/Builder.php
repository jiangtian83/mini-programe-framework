<?php  namespace think\db;
abstract class Builder 
{
	protected $connection = NULL;
	protected $query = NULL;
	protected $exp = array( "eq" => "=", "neq" => "<>", "gt" => ">", "egt" => ">=", "lt" => "<", "elt" => "<=", "notlike" => "NOT LIKE", "not like" => "NOT LIKE", "like" => "LIKE", "in" => "IN", "exp" => "EXP", "notin" => "NOT IN", "not in" => "NOT IN", "between" => "BETWEEN", "not between" => "NOT BETWEEN", "notbetween" => "NOT BETWEEN", "exists" => "EXISTS", "notexists" => "NOT EXISTS", "not exists" => "NOT EXISTS", "null" => "NULL", "notnull" => "NOT NULL", "not null" => "NOT NULL", "> time" => "> TIME", "< time" => "< TIME", ">= time" => ">= TIME", "<= time" => "<= TIME", "between time" => "BETWEEN TIME", "not between time" => "NOT BETWEEN TIME", "notbetween time" => "NOT BETWEEN TIME" );
	protected $selectSql = "SELECT%DISTINCT% %FIELD% FROM %TABLE%%FORCE%%JOIN%%WHERE%%GROUP%%HAVING%%UNION%%ORDER%%LIMIT%%LOCK%%COMMENT%";
	protected $insertSql = "%INSERT% INTO %TABLE% (%FIELD%) VALUES (%DATA%) %COMMENT%";
	protected $insertAllSql = "%INSERT% INTO %TABLE% (%FIELD%) %DATA% %COMMENT%";
	protected $updateSql = "UPDATE %TABLE% SET %SET% %JOIN% %WHERE% %ORDER%%LIMIT% %LOCK%%COMMENT%";
	protected $deleteSql = "DELETE FROM %TABLE% %USING% %JOIN% %WHERE% %ORDER%%LIMIT% %LOCK%%COMMENT%";
	public function __construct(Connection $connection, Query $query) 
	{
		$this->connection = $connection;
		$this->query = $query;
	}
	public function getConnection() 
	{
		return $this->connection;
	}
	public function getQuery() 
	{
		return $this->query;
	}
	protected function parseSqlTable($sql) 
	{
		return $this->query->parseSqlTable($sql);
	}
	protected function parseData($data, $options) 
	{
		if( empty($data) ) 
		{
			return array( );
		}
		$bind = $this->query->getFieldsBind($options["table"]);
		if( "*" == $options["field"] ) 
		{
			$fields = array_keys($bind);
		}
		else 
		{
			$fields = $options["field"];
		}
		$result = array( );
		foreach( $data as $key => $val ) 
		{
			$item = $this->parseKey($key, $options, true);
			if( $val instanceof Expression ) 
			{
				$result[$item] = $val->getValue();
				continue;
			}
			if( is_object($val) && method_exists($val, "__toString") ) 
			{
				$val = $val->__toString();
			}
			if( false === strpos($key, ".") && !in_array($key, $fields, true) ) 
			{
				if( $options["strict"] ) 
				{
					throw new \think\Exception("fields not exists:[" . $key . "]");
				}
			}
			else 
			{
				if( is_null($val) ) 
				{
					$result[$item] = "NULL";
				}
				else 
				{
					if( is_array($val) && !empty($val) ) 
					{
						switch( strtolower($val[0]) ) 
						{
							case "inc": $result[$item] = $item . "+" . floatval($val[1]);
							break;
							case "dec": $result[$item] = $item . "-" . floatval($val[1]);
							break;
							case "exp": throw new \think\Exception("not support data:[" . $val[0] . "]");
						}
					}
					else 
					{
						if( is_scalar($val) ) 
						{
							if( 0 === strpos($val, ":") && $this->query->isBind(substr($val, 1)) ) 
							{
								$result[$item] = $val;
							}
							else 
							{
								$key = str_replace(".", "_", $key);
								$this->query->bind("data__" . $key, $val, (isset($bind[$key]) ? $bind[$key] : \PDO::PARAM_STR));
								$result[$item] = ":data__" . $key;
							}
						}
					}
				}
			}
		}
		return $result;
	}
	protected function parseKey($key, $options = array( ), $strict = false) 
	{
		return $key;
	}
	protected function parseValue($value, $field = "") 
	{
		if( is_string($value) ) 
		{
			$value = (strpos($value, ":") === 0 && $this->query->isBind(substr($value, 1)) ? $value : $this->connection->quote($value));
		}
		else 
		{
			if( is_array($value) ) 
			{
				$value = array_map(array( $this, "parseValue" ), $value);
			}
			else 
			{
				if( is_bool($value) ) 
				{
					$value = ($value ? "1" : "0");
				}
				else 
				{
					if( is_null($value) ) 
					{
						$value = "null";
					}
				}
			}
		}
		return $value;
	}
	protected function parseField($fields, $options = array( )) 
	{
		if( "*" == $fields || empty($fields) ) 
		{
			$fieldsStr = "*";
		}
		else 
		{
			if( is_array($fields) ) 
			{
				$array = array( );
				foreach( $fields as $key => $field ) 
				{
					if( $field instanceof Expression ) 
					{
						$array[] = $field->getValue();
					}
					else 
					{
						if( !is_numeric($key) ) 
						{
							$array[] = $this->parseKey($key, $options) . " AS " . $this->parseKey($field, $options, true);
						}
						else 
						{
							$array[] = $this->parseKey($field, $options);
						}
					}
				}
				$fieldsStr = implode(",", $array);
			}
		}
		return $fieldsStr;
	}
	protected function parseTable($tables, $options = array( )) 
	{
		$item = array( );
		foreach( (array) $tables as $key => $table ) 
		{
			if( !is_numeric($key) ) 
			{
				$key = $this->parseSqlTable($key);
				$item[] = $this->parseKey($key) . " " . ((isset($options["alias"][$table]) ? $this->parseKey($options["alias"][$table]) : $this->parseKey($table)));
			}
			else 
			{
				$table = $this->parseSqlTable($table);
				if( isset($options["alias"][$table]) ) 
				{
					$item[] = $this->parseKey($table) . " " . $this->parseKey($options["alias"][$table]);
				}
				else 
				{
					$item[] = $this->parseKey($table);
				}
			}
		}
		return implode(",", $item);
	}
	protected function parseWhere($where, $options) 
	{
		$whereStr = $this->buildWhere($where, $options);
		if( !empty($options["soft_delete"]) ) 
		{
			list($field, $condition) = $options["soft_delete"];
			$binds = $this->query->getFieldsBind($options["table"]);
			$whereStr = ($whereStr ? "( " . $whereStr . " ) AND " : "");
			$whereStr = $whereStr . $this->parseWhereItem($field, $condition, "", $options, $binds);
		}
		return (empty($whereStr) ? "" : " WHERE " . $whereStr);
	}
	public function buildWhere($where, $options) 
	{
		if( empty($where) ) 
		{
			$where = array( );
		}
		if( $where instanceof Query ) 
		{
			return $this->buildWhere($where->getOptions("where"), $options);
		}
		$whereStr = "";
		$binds = $this->query->getFieldsBind($options["table"]);
		foreach( $where as $key => $val ) 
		{
			$str = array( );
			foreach( $val as $field => $value ) 
			{
				if( $value instanceof Expression ) 
				{
					$str[] = " " . $key . " ( " . $value->getValue() . " )";
				}
				else 
				{
					if( $value instanceof \Closure ) 
					{
						$query = new Query($this->connection);
						call_user_func_array($value, array( $query ));
						$whereClause = $this->buildWhere($query->getOptions("where"), $options);
						if( !empty($whereClause) ) 
						{
							$str[] = " " . $key . " ( " . $whereClause . " )";
						}
					}
					else 
					{
						if( strpos($field, "|") ) 
						{
							$array = explode("|", $field);
							$item = array( );
							foreach( $array as $k ) 
							{
								$item[] = $this->parseWhereItem($k, $value, "", $options, $binds);
							}
							$str[] = " " . $key . " ( " . implode(" OR ", $item) . " )";
						}
						else 
						{
							if( strpos($field, "&") ) 
							{
								$array = explode("&", $field);
								$item = array( );
								foreach( $array as $k ) 
								{
									$item[] = $this->parseWhereItem($k, $value, "", $options, $binds);
								}
								$str[] = " " . $key . " ( " . implode(" AND ", $item) . " )";
							}
							else 
							{
								$field = (is_string($field) ? $field : "");
								$str[] = " " . $key . " " . $this->parseWhereItem($field, $value, $key, $options, $binds);
							}
						}
					}
				}
			}
			$whereStr .= (empty($whereStr) ? substr(implode(" ", $str), strlen($key) + 1) : implode(" ", $str));
		}
		return $whereStr;
	}
	protected function parseWhereItem($field, $val, $rule = "", $options = array( ), $binds = array( ), $bindName = NULL) 
	{
		$key = ($field ? $this->parseKey($field, $options, true) : "");
		if( !is_array($val) ) 
		{
			$val = (is_null($val) ? array( "null", "" ) : array( "=", $val ));
		}
		list($exp, $value) = $val;
		if( is_array($exp) ) 
		{
			$item = array_pop($val);
			if( is_string($item) && in_array($item, array( "AND", "and", "OR", "or" )) ) 
			{
				$rule = $item;
			}
			else 
			{
				array_push($val, $item);
			}
			foreach( $val as $k => $item ) 
			{
				$bindName = "where_" . str_replace(".", "_", $field) . "_" . $k;
				$str[] = $this->parseWhereItem($field, $item, $rule, $options, $binds, $bindName);
			}
			return "( " . implode(" " . $rule . " ", $str) . " )";
		}
		else 
		{
			if( !in_array($exp, $this->exp) ) 
			{
				$exp = strtolower($exp);
				if( isset($this->exp[$exp]) ) 
				{
					$exp = $this->exp[$exp];
				}
				else 
				{
					throw new \think\Exception("where express error:" . $exp);
				}
			}
			$bindName = ($bindName ?: "where_" . $rule . "_" . str_replace(array( ".", "-" ), "_", $field));
			if( preg_match("/\\W/", $bindName) ) 
			{
				$bindName = md5($bindName);
			}
			if( $value instanceof Expression ) 
			{
			}
			else 
			{
				if( is_object($value) && method_exists($value, "__toString") ) 
				{
					$value = $value->__toString();
				}
			}
			$bindType = (isset($binds[$field]) ? $binds[$field] : \PDO::PARAM_STR);
			if( is_scalar($value) && array_key_exists($field, $binds) && !in_array($exp, array( "EXP", "NOT NULL", "NULL", "IN", "NOT IN", "BETWEEN", "NOT BETWEEN" )) && strpos($exp, "TIME") === false && (strpos($value, ":") !== 0 || !$this->query->isBind(substr($value, 1))) ) 
			{
				if( $this->query->isBind($bindName) ) 
				{
					$bindName .= "_" . str_replace(".", "_", uniqid("", true));
				}
				$this->query->bind($bindName, $value, $bindType);
				$value = ":" . $bindName;
			}
			$whereStr = "";
			if( in_array($exp, array( "=", "<>", ">", ">=", "<", "<=" )) ) 
			{
				if( $value instanceof \Closure ) 
				{
					$whereStr .= $key . " " . $exp . " " . $this->parseClosure($value);
				}
				else 
				{
					$whereStr .= $key . " " . $exp . " " . $this->parseValue($value, $field);
				}
			}
			else 
			{
				if( "LIKE" == $exp || "NOT LIKE" == $exp ) 
				{
					if( is_array($value) ) 
					{
						foreach( $value as $item ) 
						{
							$array[] = $key . " " . $exp . " " . $this->parseValue($item, $field);
						}
						$logic = (isset($val[2]) ? $val[2] : "AND");
						$whereStr .= "(" . implode($array, " " . strtoupper($logic) . " ") . ")";
					}
					else 
					{
						$whereStr .= $key . " " . $exp . " " . $this->parseValue($value, $field);
					}
				}
				else 
				{
					if( "EXP" == $exp ) 
					{
						if( $value instanceof Expression ) 
						{
							$whereStr .= "( " . $key . " " . $value->getValue() . " )";
						}
						else 
						{
							throw new \think\Exception("where express error:" . $exp);
						}
					}
					else 
					{
						if( in_array($exp, array( "NOT NULL", "NULL" )) ) 
						{
							$whereStr .= $key . " IS " . $exp;
						}
						else 
						{
							if( in_array($exp, array( "NOT IN", "IN" )) ) 
							{
								if( $value instanceof \Closure ) 
								{
									$whereStr .= $key . " " . $exp . " " . $this->parseClosure($value);
								}
								else 
								{
									$value = array_unique((is_array($value) ? $value : explode(",", $value)));
									if( array_key_exists($field, $binds) ) 
									{
										$bind = array( );
										$array = array( );
										$i = 0;
										foreach( $value as $v ) 
										{
											$i++;
											if( $this->query->isBind($bindName . "_in_" . $i) ) 
											{
												$bindKey = $bindName . "_in_" . uniqid() . "_" . $i;
											}
											else 
											{
												$bindKey = $bindName . "_in_" . $i;
											}
											$bind[$bindKey] = array( $v, $bindType );
											$array[] = ":" . $bindKey;
										}
										$this->query->bind($bind);
										$zone = implode(",", $array);
									}
									else 
									{
										$zone = implode(",", $this->parseValue($value, $field));
									}
									$whereStr .= $key . " " . $exp . " (" . ((empty($zone) ? "''" : $zone)) . ")";
								}
							}
							else 
							{
								if( in_array($exp, array( "NOT BETWEEN", "BETWEEN" )) ) 
								{
									$data = (is_array($value) ? $value : explode(",", $value));
									if( array_key_exists($field, $binds) ) 
									{
										if( $this->query->isBind($bindName . "_between_1") ) 
										{
											$bindKey1 = $bindName . "_between_1" . uniqid();
											$bindKey2 = $bindName . "_between_2" . uniqid();
										}
										else 
										{
											$bindKey1 = $bindName . "_between_1";
											$bindKey2 = $bindName . "_between_2";
										}
										$bind = array( $bindKey1 => array( $data[0], $bindType ), $bindKey2 => array( $data[1], $bindType ) );
										$this->query->bind($bind);
										$between = ":" . $bindKey1 . " AND :" . $bindKey2;
									}
									else 
									{
										$between = $this->parseValue($data[0], $field) . " AND " . $this->parseValue($data[1], $field);
									}
									$whereStr .= $key . " " . $exp . " " . $between;
								}
								else 
								{
									if( in_array($exp, array( "NOT EXISTS", "EXISTS" )) ) 
									{
										if( $value instanceof \Closure ) 
										{
											$whereStr .= $exp . " " . $this->parseClosure($value);
										}
										else 
										{
											$whereStr .= $exp . " (" . $value . ")";
										}
									}
									else 
									{
										if( in_array($exp, array( "< TIME", "> TIME", "<= TIME", ">= TIME" )) ) 
										{
											$whereStr .= $key . " " . substr($exp, 0, 2) . " " . $this->parseDateTime($value, $field, $options, $bindName, $bindType);
										}
										else 
										{
											if( in_array($exp, array( "BETWEEN TIME", "NOT BETWEEN TIME" )) ) 
											{
												if( is_string($value) ) 
												{
													$value = explode(",", $value);
												}
												$whereStr .= $key . " " . substr($exp, 0, -4) . $this->parseDateTime($value[0], $field, $options, $bindName . "_between_1", $bindType) . " AND " . $this->parseDateTime($value[1], $field, $options, $bindName . "_between_2", $bindType);
											}
										}
									}
								}
							}
						}
					}
				}
			}
			return $whereStr;
		}
	}
	protected function parseClosure($call, $show = true) 
	{
		$query = new Query($this->connection);
		call_user_func_array($call, array( $query ));
		return $query->buildSql($show);
	}
	protected function parseDateTime($value, $key, $options = array( ), $bindName = NULL, $bindType = NULL) 
	{
		if( strpos($key, ".") ) 
		{
			list($table, $key) = explode(".", $key);
			if( isset($options["alias"]) && ($pos = array_search($table, $options["alias"])) ) 
			{
				$table = $pos;
			}
		}
		else 
		{
			$table = $options["table"];
		}
		$type = $this->query->getTableInfo($table, "type");
		if( isset($type[$key]) ) 
		{
			$info = $type[$key];
		}
		if( isset($info) ) 
		{
			if( is_string($value) ) 
			{
				$value = (strtotime($value) ?: $value);
			}
			if( preg_match("/(datetime|timestamp)/is", $info) ) 
			{
				$value = date("Y-m-d H:i:s", $value);
			}
			else 
			{
				if( preg_match("/(date)/is", $info) ) 
				{
					$value = date("Y-m-d", $value);
				}
			}
		}
		$bindName = ($bindName ?: $key);
		if( $this->query->isBind($bindName) ) 
		{
			$bindName .= "_" . str_replace(".", "_", uniqid("", true));
		}
		$this->query->bind($bindName, $value, $bindType);
		return ":" . $bindName;
	}
	protected function parseLimit($limit) 
	{
		return (!empty($limit) && false === strpos($limit, "(") ? " LIMIT " . $limit . " " : "");
	}
	protected function parseJoin($join, $options = array( )) 
	{
		$joinStr = "";
		if( !empty($join) ) 
		{
			foreach( $join as $item ) 
			{
				list($table, $type, $on) = $item;
				$condition = array( );
				foreach( (array) $on as $val ) 
				{
					if( $val instanceof Expression ) 
					{
						$condition[] = $val->getValue();
					}
					else 
					{
						if( strpos($val, "=") ) 
						{
							list($val1, $val2) = explode("=", $val, 2);
							$condition[] = $this->parseKey($val1, $options) . "=" . $this->parseKey($val2, $options);
						}
						else 
						{
							$condition[] = $val;
						}
					}
				}
				$table = $this->parseTable($table, $options);
				$joinStr .= " " . $type . " JOIN " . $table . " ON " . implode(" AND ", $condition);
			}
		}
		return $joinStr;
	}
	protected function parseOrder2($order, $options = array( )) 
	{
		if( empty($order) ) 
		{
			return "";
		}
		$array = array( );
		foreach( $order as $key => $val ) 
		{
			if( $val instanceof Expression ) 
			{
				$array[] = $val->getValue();
			}
			else 
			{
				if( "[rand]" == $val ) 
				{
					$array[] = $this->parseRand();
				}
				else 
				{
					if( is_numeric($key) ) 
					{
						list($key, $sort) = explode(" ", (strpos($val, " ") ? $val : $val . " "));
					}
					else 
					{
						$sort = $val;
					}
					$sort = strtoupper($sort);
					$sort = (in_array($sort, array( "ASC", "DESC" ), true) ? " " . $sort : "");
					$array[] = $this->parseKey($key, $options, true) . $sort;
				}
			}
		}
		$order = implode(",", $array);
		return (!empty($order) ? " ORDER BY " . $order : "");
	}
	protected function parseOrder($order, $options = array( )) 
	{
		if( is_array($order) ) 
		{
			$array = array( );
			foreach( $order as $key => $val ) 
			{
				if( is_numeric($key) ) 
				{
					if( "[rand]" == $val ) 
					{
						if( method_exists($this, "parseRand") ) 
						{
							$array[] = $this->parseRand();
						}
						else 
						{
							throw new \BadMethodCallException("method not exists:" . get_class($this) . "-> parseRand");
						}
					}
					else 
					{
						if( false === strpos($val, "(") ) 
						{
							$array[] = $this->parseKey($val, $options);
						}
						else 
						{
							$array[] = $val;
						}
					}
				}
				else 
				{
					$sort = (in_array(strtolower(trim($val)), array( "asc", "desc" )) ? " " . $val : "");
					$array[] = $this->parseKey($key, $options) . " " . $sort;
				}
			}
			$order = implode(",", $array);
		}
		return (!empty($order) ? " ORDER BY " . $order : "");
	}
	protected function parseGroup($group) 
	{
		return (!empty($group) ? " GROUP BY " . $this->parseKey($group) : "");
	}
	protected function parseHaving($having) 
	{
		return (!empty($having) ? " HAVING " . $having : "");
	}
	protected function parseComment($comment) 
	{
		if( false !== strpos($comment, "*/") ) 
		{
			$comment = strstr($comment, "*/", true);
		}
		return (!empty($comment) ? " /* " . $comment . " */" : "");
	}
	protected function parseDistinct($distinct) 
	{
		return (!empty($distinct) ? " DISTINCT " : "");
	}
	protected function parseUnion($union) 
	{
		if( empty($union) ) 
		{
			return "";
		}
		$type = $union["type"];
		unset($union["type"]);
		foreach( $union as $u ) 
		{
			if( $u instanceof \Closure ) 
			{
				$sql[] = $type . " " . $this->parseClosure($u);
			}
			else 
			{
				if( is_string($u) ) 
				{
					$sql[] = $type . " ( " . $this->parseSqlTable($u) . " )";
				}
			}
		}
		return " " . implode(" ", $sql);
	}
	protected function parseForce($index) 
	{
		if( empty($index) ) 
		{
			return "";
		}
		return sprintf(" FORCE INDEX ( %s ) ", (is_array($index) ? implode(",", $index) : $index));
	}
	protected function parseLock($lock = false) 
	{
		if( is_bool($lock) ) 
		{
			return ($lock ? " FOR UPDATE " : "");
		}
		if( is_string($lock) ) 
		{
			return " " . trim($lock) . " ";
		}
	}
	public function select($options = array( )) 
	{
		$sql = str_replace(array( "%TABLE%", "%DISTINCT%", "%FIELD%", "%JOIN%", "%WHERE%", "%GROUP%", "%HAVING%", "%ORDER%", "%LIMIT%", "%UNION%", "%LOCK%", "%COMMENT%", "%FORCE%" ), array( $this->parseTable($options["table"], $options), $this->parseDistinct($options["distinct"]), $this->parseField($options["field"], $options), $this->parseJoin($options["join"], $options), $this->parseWhere($options["where"], $options), $this->parseGroup($options["group"]), $this->parseHaving($options["having"]), $this->parseOrder($options["order"], $options), $this->parseLimit($options["limit"]), $this->parseUnion($options["union"]), $this->parseLock($options["lock"]), $this->parseComment($options["comment"]), $this->parseForce($options["force"]) ), $this->selectSql);
		return $sql;
	}
	public function insert(array $data, $options = array( ), $replace = false) 
	{
		$data = $this->parseData($data, $options);
		if( empty($data) ) 
		{
			return 0;
		}
		$fields = array_keys($data);
		$values = array_values($data);
		$sql = str_replace(array( "%INSERT%", "%TABLE%", "%FIELD%", "%DATA%", "%COMMENT%" ), array( ($replace ? "REPLACE" : "INSERT"), $this->parseTable($options["table"], $options), implode(" , ", $fields), implode(" , ", $values), $this->parseComment($options["comment"]) ), $this->insertSql);
		return $sql;
	}
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
			$values[] = "SELECT " . implode(",", $value);
			if( !isset($insertFields) ) 
			{
				$insertFields = array_keys($data);
			}
		}
		foreach( $insertFields as $field ) 
		{
			$fields[] = $this->parseKey($field, $options, true);
		}
		return str_replace(array( "%INSERT%", "%TABLE%", "%FIELD%", "%DATA%", "%COMMENT%" ), array( ($replace ? "REPLACE" : "INSERT"), $this->parseTable($options["table"], $options), implode(" , ", $insertFields), implode(" UNION ALL ", $values), $this->parseComment($options["comment"]) ), $this->insertAllSql);
	}
	public function selectInsert($fields, $table, $options) 
	{
		if( is_string($fields) ) 
		{
			$fields = explode(",", $fields);
		}
		$fields = array_map(array( $this, "parseKey" ), $fields);
		$sql = "INSERT INTO " . $this->parseTable($table, $options) . " (" . implode(",", $fields) . ") " . $this->select($options);
		return $sql;
	}
	public function update($data, $options) 
	{
		$table = $this->parseTable($options["table"], $options);
		$data = $this->parseData($data, $options);
		if( empty($data) ) 
		{
			return "";
		}
		foreach( $data as $key => $val ) 
		{
			$set[] = $key . "=" . $val;
		}
		$sql = str_replace(array( "%TABLE%", "%SET%", "%JOIN%", "%WHERE%", "%ORDER%", "%LIMIT%", "%LOCK%", "%COMMENT%" ), array( $this->parseTable($options["table"], $options), implode(",", $set), $this->parseJoin($options["join"], $options), $this->parseWhere($options["where"], $options), $this->parseOrder($options["order"], $options), $this->parseLimit($options["limit"]), $this->parseLock($options["lock"]), $this->parseComment($options["comment"]) ), $this->updateSql);
		return $sql;
	}
	public function delete($options) 
	{
		$sql = str_replace(array( "%TABLE%", "%USING%", "%JOIN%", "%WHERE%", "%ORDER%", "%LIMIT%", "%LOCK%", "%COMMENT%" ), array( $this->parseTable($options["table"], $options), (!empty($options["using"]) ? " USING " . $this->parseTable($options["using"], $options) . " " : ""), $this->parseJoin($options["join"], $options), $this->parseWhere($options["where"], $options), $this->parseOrder($options["order"], $options), $this->parseLimit($options["limit"]), $this->parseLock($options["lock"]), $this->parseComment($options["comment"]) ), $this->deleteSql);
		return $sql;
	}
}
?>