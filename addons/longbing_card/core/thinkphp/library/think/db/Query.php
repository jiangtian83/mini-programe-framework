<?php  namespace think\db;
class Query 
{
	protected $connection = NULL;
	protected $builder = NULL;
	protected $model = NULL;
	protected $table = "";
	protected $name = "";
	protected $pk = NULL;
	protected $prefix = "";
	protected $options = array( );
	protected $bind = array( );
	protected static $info = array( );
	private static $event = array( );
	private static $readMaster = array( );
	public function __construct(Connection $connection = NULL, $model = NULL) 
	{
		$this->connection = ($connection ?: \think\Db::connect(array( ), true));
		$this->prefix = $this->connection->getConfig("prefix");
		$this->model = $model;
		$this->setBuilder();
	}
	public function __call($method, $args) 
	{
		if( strtolower(substr($method, 0, 5)) == "getby" ) 
		{
			$field = \think\Loader::parseName(substr($method, 5));
			$where[$field] = $args[0];
			return $this->where($where)->find();
		}
		if( strtolower(substr($method, 0, 10)) == "getfieldby" ) 
		{
			$name = \think\Loader::parseName(substr($method, 10));
			$where[$name] = $args[0];
			return $this->where($where)->value($args[1]);
		}
		throw new \think\Exception("method not exist:" . "think\\db\\Query" . "->" . $method);
	}
	public function getConnection() 
	{
		return $this->connection;
	}
	public function connect($config) 
	{
		$this->connection = \think\Db::connect($config);
		$this->setBuilder();
		$this->prefix = $this->connection->getConfig("prefix");
		return $this;
	}
	protected function setBuilder() 
	{
		$class = $this->connection->getBuilder();
		$this->builder = new $class($this->connection, $this);
	}
	public function getModel() 
	{
		return $this->model;
	}
	public function readMaster($allTable = false) 
	{
		if( $allTable ) 
		{
			$table = "*";
		}
		else 
		{
			$table = (isset($this->options["table"]) ? $this->options["table"] : $this->getTable());
		}
		static::$readMaster[$table] = true;
		return $this;
	}
	public function getBuilder() 
	{
		return $this->builder;
	}
	public function name($name) 
	{
		$this->name = $name;
		return $this;
	}
	public function setTable($table) 
	{
		$this->table = $table;
		return $this;
	}
	public function getTable($name = "") 
	{
		if( $name || empty($this->table) ) 
		{
			$name = ($name ?: $this->name);
			$tableName = $this->prefix;
			if( $name ) 
			{
				$tableName .= \think\Loader::parseName($name);
			}
		}
		else 
		{
			$tableName = $this->table;
		}
		return $tableName;
	}
	public function parseSqlTable($sql) 
	{
		if( false !== strpos($sql, "__") ) 
		{
			$prefix = $this->prefix;
			$sql = preg_replace_callback("/__([A-Z0-9_-]+)__/sU", function($match) use ($prefix) 
			{
				return $prefix . strtolower($match[1]);
			}
			, $sql);
		}
		return $sql;
	}
	public function query($sql, $bind = array( ), $master = false, $class = false) 
	{
		return $this->connection->query($sql, $bind, $master, $class);
	}
	public function execute($sql, $bind = array( )) 
	{
		return $this->connection->execute($sql, $bind, $this);
	}
	public function getLastInsID($sequence = NULL) 
	{
		return $this->connection->getLastInsID($sequence);
	}
	public function getLastSql() 
	{
		return $this->connection->getLastSql();
	}
	public function transaction($callback) 
	{
		return $this->connection->transaction($callback);
	}
	public function startTrans() 
	{
		$this->connection->startTrans();
	}
	public function commit() 
	{
		$this->connection->commit();
	}
	public function rollback() 
	{
		$this->connection->rollback();
	}
	public function batchQuery($sql = array( ), $bind = array( )) 
	{
		return $this->connection->batchQuery($sql, $bind);
	}
	public function getConfig($name = "") 
	{
		return $this->connection->getConfig($name);
	}
	public function getPartitionTableName($data, $field, $rule = array( )) 
	{
		if( $field && isset($data[$field]) ) 
		{
			$value = $data[$field];
			$type = $rule["type"];
			switch( $type ) 
			{
				case "id": $step = $rule["expr"];
				$seq = floor($value / $step) + 1;
				break;
				case "year": if( !is_numeric($value) ) 
				{
					$value = strtotime($value);
				}
				$seq = date("Y", $value) - $rule["expr"] + 1;
				break;
				case "mod": $seq = $value % $rule["num"] + 1;
				break;
				case "md5": $seq = ord(substr(md5($value), 0, 1)) % $rule["num"] + 1;
				break;
				default: if( function_exists($type) ) 
				{
					$seq = ord(substr($type($value), 0, 1)) % $rule["num"] + 1;
				}
				else 
				{
					$seq = ord($value[0]) % $rule["num"] + 1;
				}
			}
			return $this->getTable() . "_" . $seq;
		}
		$tableName = array( );
		for( $i = 0; $i < $rule["num"]; $i++ ) 
		{
			$tableName[] = "SELECT * FROM " . $this->getTable() . "_" . ($i + 1);
		}
		$tableName = "( " . implode(" UNION ", $tableName) . ") AS " . $this->name;
		return $tableName;
	}
	public function value($field, $default = NULL, $force = false) 
	{
		$result = false;
		if( empty($this->options["fetch_sql"]) && !empty($this->options["cache"]) ) 
		{
			$cache = $this->options["cache"];
			if( empty($this->options["table"]) ) 
			{
				$this->options["table"] = $this->getTable();
			}
			$key = (is_string($cache["key"]) ? $cache["key"] : md5($this->connection->getConfig("database") . "." . $field . serialize($this->options) . serialize($this->bind)));
			$result = \think\Cache::get($key);
		}
		if( false === $result ) 
		{
			if( isset($this->options["field"]) ) 
			{
				unset($this->options["field"]);
			}
			$pdo = $this->field($field)->limit(1)->getPdo();
			if( is_string($pdo) ) 
			{
				return $pdo;
			}
			$result = $pdo->fetchColumn();
			if( $force ) 
			{
				$result += 0;
			}
			if( isset($cache) && false !== $result ) 
			{
				$this->cacheData($key, $result, $cache);
			}
		}
		else 
		{
			$this->options = array( );
		}
		return (false !== $result ? $result : $default);
	}
	public function column($field, $key = "") 
	{
		$result = false;
		if( empty($this->options["fetch_sql"]) && !empty($this->options["cache"]) ) 
		{
			$cache = $this->options["cache"];
			if( empty($this->options["table"]) ) 
			{
				$this->options["table"] = $this->getTable();
			}
			$guid = (is_string($cache["key"]) ? $cache["key"] : md5($this->connection->getConfig("database") . "." . $field . serialize($this->options) . serialize($this->bind)));
			$result = \think\Cache::get($guid);
		}
		if( false === $result ) 
		{
			if( isset($this->options["field"]) ) 
			{
				unset($this->options["field"]);
			}
			if( is_null($field) ) 
			{
				$field = "*";
			}
			else 
			{
				if( $key && "*" != $field ) 
				{
					$field = $key . "," . $field;
				}
			}
			$pdo = $this->field($field)->getPdo();
			if( is_string($pdo) ) 
			{
				return $pdo;
			}
			if( 1 == $pdo->columnCount() ) 
			{
				$result = $pdo->fetchAll(\PDO::FETCH_COLUMN);
			}
			else 
			{
				$resultSet = $pdo->fetchAll(\PDO::FETCH_ASSOC);
				if( $resultSet ) 
				{
					$fields = array_keys($resultSet[0]);
					$count = count($fields);
					$key1 = array_shift($fields);
					$key2 = ($fields ? array_shift($fields) : "");
					$key = ($key ?: $key1);
					if( strpos($key, ".") ) 
					{
						list($alias, $key) = explode(".", $key);
					}
					foreach( $resultSet as $val ) 
					{
						if( 2 < $count ) 
						{
							$result[$val[$key]] = $val;
						}
						else 
						{
							if( 2 == $count ) 
							{
								$result[$val[$key]] = $val[$key2];
							}
							else 
							{
								if( 1 == $count ) 
								{
									$result[$val[$key]] = $val[$key1];
								}
							}
						}
					}
				}
				else 
				{
					$result = array( );
				}
			}
			if( isset($cache) && isset($guid) ) 
			{
				$this->cacheData($guid, $result, $cache);
			}
		}
		else 
		{
			$this->options = array( );
		}
		return $result;
	}
	public function count($field = "*") 
	{
		if( isset($this->options["group"]) ) 
		{
			$options = $this->getOptions();
			$subSql = $this->options($options)->field("count(" . $field . ")")->bind($this->bind)->buildSql();
			return $this->table(array( $subSql => "_group_count_" ))->value("COUNT(*) AS tp_count", 0, true);
		}
		return $this->value("COUNT(" . $field . ") AS tp_count", 0, true);
	}
	public function sum($field) 
	{
		return $this->value("SUM(" . $field . ") AS tp_sum", 0, true);
	}
	public function min($field, $force = true) 
	{
		return $this->value("MIN(" . $field . ") AS tp_min", 0, $force);
	}
	public function max($field, $force = true) 
	{
		return $this->value("MAX(" . $field . ") AS tp_max", 0, $force);
	}
	public function avg($field) 
	{
		return $this->value("AVG(" . $field . ") AS tp_avg", 0, true);
	}
	public function setField($field, $value = "") 
	{
		if( is_array($field) ) 
		{
			$data = $field;
		}
		else 
		{
			$data[$field] = $value;
		}
		return $this->update($data);
	}
	public function setInc($field, $step = 1, $lazyTime = 0) 
	{
		$condition = (!empty($this->options["where"]) ? $this->options["where"] : array( ));
		if( empty($condition) ) 
		{
			throw new \think\Exception("no data to update");
		}
		if( 0 < $lazyTime ) 
		{
			$guid = md5($this->getTable() . "_" . $field . "_" . serialize($condition) . serialize($this->bind));
			$step = $this->lazyWrite("inc", $guid, $step, $lazyTime);
			if( false === $step ) 
			{
				$this->options = array( );
				return true;
			}
		}
		return $this->setField($field, array( "inc", $step ));
	}
	public function setDec($field, $step = 1, $lazyTime = 0) 
	{
		$condition = (!empty($this->options["where"]) ? $this->options["where"] : array( ));
		if( empty($condition) ) 
		{
			throw new \think\Exception("no data to update");
		}
		if( 0 < $lazyTime ) 
		{
			$guid = md5($this->getTable() . "_" . $field . "_" . serialize($condition) . serialize($this->bind));
			$step = $this->lazyWrite("dec", $guid, $step, $lazyTime);
			if( false === $step ) 
			{
				$this->options = array( );
				return true;
			}
			return $this->setField($field, array( "inc", $step ));
		}
		return $this->setField($field, array( "dec", $step ));
	}
	protected function lazyWrite($type, $guid, $step, $lazyTime) 
	{
		if( !\think\Cache::has($guid . "_time") ) 
		{
			\think\Cache::set($guid . "_time", $_SERVER["REQUEST_TIME"], 0);
			\think\Cache::$type($guid, $step);
		}
		else 
		{
			if( \think\Cache::get($guid . "_time") + $lazyTime < $_SERVER["REQUEST_TIME"] ) 
			{
				$value = \think\Cache::$type($guid, $step);
				\think\Cache::rm($guid);
				\think\Cache::rm($guid . "_time");
				return (0 === $value ? false : $value);
			}
			\think\Cache::$type($guid, $step);
		}
		return false;
	}
	public function join($join, $condition = NULL, $type = "INNER") 
	{
		if( empty($condition) ) 
		{
			foreach( $join as $key => $value ) 
			{
				if( is_array($value) && 2 <= count($value) ) 
				{
					$this->join($value[0], $value[1], (isset($value[2]) ? $value[2] : $type));
				}
			}
		}
		else 
		{
			$table = $this->getJoinTable($join);
			$this->options["join"][] = array( $table, strtoupper($type), $condition );
		}
		return $this;
	}
	protected function getJoinTable($join, &$alias = NULL) 
	{
		if( is_array($join) ) 
		{
			$table = $join;
			$alias = array_shift($join);
		}
		else 
		{
			$join = trim($join);
			if( false !== strpos($join, "(") ) 
			{
				$table = $join;
			}
			else 
			{
				$prefix = $this->prefix;
				if( strpos($join, " ") ) 
				{
					list($table, $alias) = explode(" ", $join);
				}
				else 
				{
					$table = $join;
					if( false === strpos($join, ".") && 0 !== strpos($join, "__") ) 
					{
						$alias = $join;
					}
				}
				if( $prefix && false === strpos($table, ".") && 0 !== strpos($table, $prefix) && 0 !== strpos($table, "__") ) 
				{
					$table = $this->getTable($table);
				}
			}
			if( isset($alias) && $table != $alias ) 
			{
				$table = array( $table => $alias );
			}
		}
		return $table;
	}
	public function union($union, $all = false) 
	{
		$this->options["union"]["type"] = ($all ? "UNION ALL" : "UNION");
		if( is_array($union) ) 
		{
			$this->options["union"] = array_merge($this->options["union"], $union);
		}
		else 
		{
			$this->options["union"][] = $union;
		}
		return $this;
	}
	public function field($field, $except = false, $tableName = "", $prefix = "", $alias = "") 
	{
		if( empty($field) ) 
		{
			return $this;
		}
		if( $field instanceof Expression ) 
		{
			$this->options["field"][] = $field;
			return $this;
		}
		if( is_string($field) ) 
		{
			if( preg_match("/[\\<'\\\"\\(]/", $field) ) 
			{
				return $this->fieldRaw($field);
			}
			$field = array_map("trim", explode(",", $field));
		}
		if( true === $field ) 
		{
			$fields = $this->getTableInfo(($tableName ?: (isset($this->options["table"]) ? $this->options["table"] : "")), "fields");
			$field = ($fields ?: array( "*" ));
		}
		else 
		{
			if( $except ) 
			{
				$fields = $this->getTableInfo(($tableName ?: (isset($this->options["table"]) ? $this->options["table"] : "")), "fields");
				$field = ($fields ? array_diff($fields, $field) : $field);
			}
		}
		if( $tableName ) 
		{
			$prefix = ($prefix ?: $tableName);
			foreach( $field as $key => $val ) 
			{
				if( is_numeric($key) ) 
				{
					$val = $prefix . "." . $val . (($alias ? " AS " . $alias . $val : ""));
				}
				$field[$key] = $val;
			}
		}
		if( isset($this->options["field"]) ) 
		{
			$field = array_merge((array) $this->options["field"], $field);
		}
		$this->options["field"] = array_unique($field);
		return $this;
	}
	public function fieldRaw($field, array $bind = array( )) 
	{
		$this->options["field"][] = $this->raw($field);
		if( $bind ) 
		{
			$this->bind($bind);
		}
		return $this;
	}
	public function data($field, $value = NULL) 
	{
		if( is_array($field) ) 
		{
			$this->options["data"] = (isset($this->options["data"]) ? array_merge($this->options["data"], $field) : $field);
		}
		else 
		{
			$this->options["data"][$field] = $value;
		}
		return $this;
	}
	public function inc($field, $step = 1) 
	{
		$fields = (is_string($field) ? explode(",", $field) : $field);
		foreach( $fields as $field ) 
		{
			$this->data($field, array( "inc", $step ));
		}
		return $this;
	}
	public function dec($field, $step = 1) 
	{
		$fields = (is_string($field) ? explode(",", $field) : $field);
		foreach( $fields as $field ) 
		{
			$this->data($field, array( "dec", $step ));
		}
		return $this;
	}
	public function exp($field, $value) 
	{
		$this->data($field, $this->raw($value));
		return $this;
	}
	public function raw($value) 
	{
		return new Expression($value);
	}
	public function view($join, $field = true, $on = NULL, $type = "INNER") 
	{
		$this->options["view"] = true;
		if( is_array($join) && key($join) === 0 ) 
		{
			foreach( $join as $key => $val ) 
			{
				$this->view($val[0], $val[1], (isset($val[2]) ? $val[2] : null), (isset($val[3]) ? $val[3] : "INNER"));
			}
		}
		else 
		{
			$fields = array( );
			$table = $this->getJoinTable($join, $alias);
			if( true === $field ) 
			{
				$fields = $alias . ".*";
			}
			else 
			{
				if( is_string($field) ) 
				{
					$field = explode(",", $field);
				}
				foreach( $field as $key => $val ) 
				{
					if( is_numeric($key) ) 
					{
						$fields[] = $alias . "." . $val;
						$this->options["map"][$val] = $alias . "." . $val;
					}
					else 
					{
						if( preg_match("/[,=\\.'\\\"\\(\\s]/", $key) ) 
						{
							$name = $key;
						}
						else 
						{
							$name = $alias . "." . $key;
						}
						$fields[$name] = $val;
						$this->options["map"][$val] = $name;
					}
				}
			}
			$this->field($fields);
			if( $on ) 
			{
				$this->join($table, $on, $type);
			}
			else 
			{
				$this->table($table);
			}
		}
		return $this;
	}
	public function partition($data, $field, $rule = array( )) 
	{
		$this->options["table"] = $this->getPartitionTableName($data, $field, $rule);
		return $this;
	}
	public function where($field, $op = NULL, $condition = NULL) 
	{
		$param = func_get_args();
		array_shift($param);
		$this->parseWhereExp("AND", $field, $op, $condition, $param);
		return $this;
	}
	public function whereOr($field, $op = NULL, $condition = NULL) 
	{
		$param = func_get_args();
		array_shift($param);
		$this->parseWhereExp("OR", $field, $op, $condition, $param);
		return $this;
	}
	public function whereXor($field, $op = NULL, $condition = NULL) 
	{
		$param = func_get_args();
		array_shift($param);
		$this->parseWhereExp("XOR", $field, $op, $condition, $param);
		return $this;
	}
	public function whereRaw($where, $bind = array( ), $logic = "AND") 
	{
		$this->options["where"][$logic][] = $this->raw($where);
		if( $bind ) 
		{
			$this->bind($bind);
		}
		return $this;
	}
	public function whereOrRaw($where, $bind = array( )) 
	{
		return $this->whereRaw($where, $bind, "OR");
	}
	public function whereNull($field, $logic = "AND") 
	{
		$this->parseWhereExp($logic, $field, "null", null, array( ), true);
		return $this;
	}
	public function whereNotNull($field, $logic = "AND") 
	{
		$this->parseWhereExp($logic, $field, "notnull", null, array( ), true);
		return $this;
	}
	public function whereExists($condition, $logic = "AND") 
	{
		$this->options["where"][strtoupper($logic)][] = array( "exists", $condition );
		return $this;
	}
	public function whereNotExists($condition, $logic = "AND") 
	{
		$this->options["where"][strtoupper($logic)][] = array( "not exists", $condition );
		return $this;
	}
	public function whereIn($field, $condition, $logic = "AND") 
	{
		$this->parseWhereExp($logic, $field, "in", $condition, array( ), true);
		return $this;
	}
	public function whereNotIn($field, $condition, $logic = "AND") 
	{
		$this->parseWhereExp($logic, $field, "not in", $condition, array( ), true);
		return $this;
	}
	public function whereLike($field, $condition, $logic = "AND") 
	{
		$this->parseWhereExp($logic, $field, "like", $condition, array( ), true);
		return $this;
	}
	public function whereNotLike($field, $condition, $logic = "AND") 
	{
		$this->parseWhereExp($logic, $field, "not like", $condition, array( ), true);
		return $this;
	}
	public function whereBetween($field, $condition, $logic = "AND") 
	{
		$this->parseWhereExp($logic, $field, "between", $condition, array( ), true);
		return $this;
	}
	public function whereNotBetween($field, $condition, $logic = "AND") 
	{
		$this->parseWhereExp($logic, $field, "not between", $condition, array( ), true);
		return $this;
	}
	public function whereExp($field, $condition, $logic = "AND") 
	{
		$this->parseWhereExp($logic, $field, "exp", $this->raw($condition), array( ), true);
		return $this;
	}
	public function useSoftDelete($field, $condition = NULL) 
	{
		if( $field ) 
		{
			$this->options["soft_delete"] = array( $field, ($condition ?: array( "null", "" )) );
		}
		return $this;
	}
	protected function parseWhereExp($logic, $field, $op, $condition, $param = array( ), $strict = false) 
	{
		$logic = strtoupper($logic);
		if( $field instanceof \Closure ) 
		{
			$this->options["where"][$logic][] = (is_string($op) ? array( $op, $field ) : $field);
		}
		else 
		{
			if( is_string($field) && !empty($this->options["via"]) && !strpos($field, ".") ) 
			{
				$field = $this->options["via"] . "." . $field;
			}
			if( $field instanceof Expression ) 
			{
				return $this->whereRaw($field, (is_array($op) ? $op : array( )));
			}
			if( $strict ) 
			{
				$where[$field] = array( $op, $condition );
				$this->options["multi"][$logic][$field][] = $where[$field];
			}
			else 
			{
				if( is_string($field) && preg_match("/[,=\\>\\<'\\\"\\(\\s]/", $field) ) 
				{
					$where[] = array( "exp", $this->raw($field) );
					if( is_array($op) ) 
					{
						$this->bind($op);
					}
				}
				else 
				{
					if( is_null($op) && is_null($condition) ) 
					{
						if( is_array($field) ) 
						{
							$where = $field;
							foreach( $where as $k => $val ) 
							{
								$this->options["multi"][$logic][$k][] = $val;
							}
						}
						else 
						{
							if( $field && is_string($field) ) 
							{
								$where[$field] = array( "null", "" );
								$this->options["multi"][$logic][$field][] = $where[$field];
							}
						}
					}
					else 
					{
						if( is_array($op) ) 
						{
							$where[$field] = $param;
						}
						else 
						{
							if( in_array(strtolower($op), array( "null", "notnull", "not null" )) ) 
							{
								$where[$field] = array( $op, "" );
								$this->options["multi"][$logic][$field][] = $where[$field];
							}
							else 
							{
								if( is_null($condition) ) 
								{
									$where[$field] = array( "eq", $op );
									$this->options["multi"][$logic][$field][] = $where[$field];
								}
								else 
								{
									if( "exp" == strtolower($op) ) 
									{
										$where[$field] = array( "exp", $this->raw($condition) );
										if( isset($param[2]) && is_array($param[2]) ) 
										{
											$this->bind($param[2]);
										}
									}
									else 
									{
										$where[$field] = array( $op, $condition );
									}
									$this->options["multi"][$logic][$field][] = $where[$field];
								}
							}
						}
					}
				}
			}
			if( !empty($where) ) 
			{
				if( !isset($this->options["where"][$logic]) ) 
				{
					$this->options["where"][$logic] = array( );
				}
				if( is_string($field) && $this->checkMultiField($field, $logic) ) 
				{
					$where[$field] = $this->options["multi"][$logic][$field];
				}
				else 
				{
					if( is_array($field) ) 
					{
						foreach( $field as $key => $val ) 
						{
							if( $this->checkMultiField($key, $logic) ) 
							{
								$where[$key] = $this->options["multi"][$logic][$key];
							}
						}
					}
				}
				$this->options["where"][$logic] = array_merge($this->options["where"][$logic], $where);
			}
		}
	}
	private function checkMultiField($field, $logic) 
	{
		return isset($this->options["multi"][$logic][$field]) && 1 < count($this->options["multi"][$logic][$field]);
	}
	public function removeWhereField($field, $logic = "AND") 
	{
		$logic = strtoupper($logic);
		if( isset($this->options["where"][$logic][$field]) ) 
		{
			unset($this->options["where"][$logic][$field]);
			unset($this->options["multi"][$logic][$field]);
		}
		return $this;
	}
	public function removeOption($option = true) 
	{
		if( true === $option ) 
		{
			$this->options = array( );
		}
		else 
		{
			if( is_string($option) && isset($this->options[$option]) ) 
			{
				unset($this->options[$option]);
			}
		}
		return $this;
	}
	public function limit($offset, $length = NULL) 
	{
		if( is_null($length) && strpos($offset, ",") ) 
		{
			list($offset, $length) = explode(",", $offset);
		}
		$this->options["limit"] = intval($offset) . (($length ? "," . intval($length) : ""));
		return $this;
	}
	public function page($page, $listRows = NULL) 
	{
		if( is_null($listRows) && strpos($page, ",") ) 
		{
			list($page, $listRows) = explode(",", $page);
		}
		$this->options["page"] = array( intval($page), intval($listRows) );
		return $this;
	}
	public function paginate($listRows = NULL, $simple = false, $config = array( )) 
	{
		if( is_int($simple) ) 
		{
			$total = $simple;
			$simple = false;
		}
		if( is_array($listRows) ) 
		{
			$config = array_merge(\think\Config::get("paginate"), $listRows);
			$listRows = $config["list_rows"];
		}
		else 
		{
			$config = array_merge(\think\Config::get("paginate"), $config);
			$listRows = ($listRows ?: $config["list_rows"]);
		}
		$class = (false !== strpos($config["type"], "\\") ? $config["type"] : "\\think\\paginator\\driver\\" . ucwords($config["type"]));
		$page = (isset($config["page"]) ? (int) $config["page"] : call_user_func(array( $class, "getCurrentPage" ), $config["var_page"]));
		$page = ($page < 1 ? 1 : $page);
		$config["path"] = (isset($config["path"]) ? $config["path"] : call_user_func(array( $class, "getCurrentPath" )));
		if( !isset($total) && !$simple ) 
		{
			$options = $this->getOptions();
			unset($this->options["order"]);
			unset($this->options["limit"]);
			unset($this->options["page"]);
			unset($this->options["field"]);
			$bind = $this->bind;
			$total = $this->count();
			$results = $this->options($options)->bind($bind)->page($page, $listRows)->select();
		}
		else 
		{
			if( $simple ) 
			{
				$results = $this->limit(($page - 1) * $listRows, $listRows + 1)->select();
				$total = null;
			}
			else 
			{
				$results = $this->page($page, $listRows)->select();
			}
		}
		return $class::make($results, $listRows, $page, $total, $simple, $config);
	}
	public function table($table) 
	{
		if( is_string($table) ) 
		{
			if( strpos($table, ")") ) 
			{
			}
			else 
			{
				if( strpos($table, ",") ) 
				{
					$tables = explode(",", $table);
					$table = array( );
					foreach( $tables as $item ) 
					{
						list($item, $alias) = explode(" ", trim($item));
						if( $alias ) 
						{
							$this->alias(array( $item => $alias ));
							$table[$item] = $alias;
						}
						else 
						{
							$table[] = $item;
						}
					}
				}
				else 
				{
					if( strpos($table, " ") ) 
					{
						list($table, $alias) = explode(" ", $table);
						$table = array( $table => $alias );
						$this->alias($table);
					}
				}
			}
		}
		else 
		{
			$tables = $table;
			$table = array( );
			foreach( $tables as $key => $val ) 
			{
				if( is_numeric($key) ) 
				{
					$table[] = $val;
				}
				else 
				{
					$this->alias(array( $key => $val ));
					$table[$key] = $val;
				}
			}
		}
		$this->options["table"] = $table;
		return $this;
	}
	public function using($using) 
	{
		$this->options["using"] = $using;
		return $this;
	}
	public function order($field, $order = NULL) 
	{
		if( empty($field) ) 
		{
			return $this;
		}
		if( $field instanceof Expression ) 
		{
			$this->options["order"][] = $field;
			return $this;
		}
		if( is_string($field) ) 
		{
			if( !empty($this->options["via"]) ) 
			{
				$field = $this->options["via"] . "." . $field;
			}
			if( strpos($field, ",") ) 
			{
				$field = array_map("trim", explode(",", $field));
			}
			else 
			{
				$field = (empty($order) ? $field : array( $field => $order ));
			}
		}
		else 
		{
			if( !empty($this->options["via"]) ) 
			{
				foreach( $field as $key => $val ) 
				{
					if( is_numeric($key) ) 
					{
						$field[$key] = $this->options["via"] . "." . $val;
					}
					else 
					{
						$field[$this->options["via"] . "." . $key] = $val;
						unset($field[$key]);
					}
				}
			}
		}
		if( !isset($this->options["order"]) ) 
		{
			$this->options["order"] = array( );
		}
		if( is_array($field) ) 
		{
			$this->options["order"] = array_merge($this->options["order"], $field);
		}
		else 
		{
			$this->options["order"][] = $field;
		}
		return $this;
	}
	public function orderRaw($field, array $bind = array( )) 
	{
		$this->options["order"][] = $this->raw($field);
		if( $bind ) 
		{
			$this->bind($bind);
		}
		return $this;
	}
	public function cache($key = true, $expire = NULL, $tag = NULL) 
	{
		if( $key instanceof \DateTime || is_numeric($key) && is_null($expire) ) 
		{
			$expire = $key;
			$key = true;
		}
		if( false !== $key ) 
		{
			$this->options["cache"] = array( "key" => $key, "expire" => $expire, "tag" => $tag );
		}
		return $this;
	}
	public function group($group) 
	{
		$this->options["group"] = $group;
		return $this;
	}
	public function having($having) 
	{
		$this->options["having"] = $having;
		return $this;
	}
	public function lock($lock = false) 
	{
		$this->options["lock"] = $lock;
		$this->options["master"] = true;
		return $this;
	}
	public function distinct($distinct) 
	{
		$this->options["distinct"] = $distinct;
		return $this;
	}
	public function alias($alias) 
	{
		if( is_array($alias) ) 
		{
			foreach( $alias as $key => $val ) 
			{
				if( false !== strpos($key, "__") ) 
				{
					$table = $this->parseSqlTable($key);
				}
				else 
				{
					$table = $key;
				}
				$this->options["alias"][$table] = $val;
			}
		}
		else 
		{
			if( isset($this->options["table"]) ) 
			{
				$table = (is_array($this->options["table"]) ? key($this->options["table"]) : $this->options["table"]);
				if( false !== strpos($table, "__") ) 
				{
					$table = $this->parseSqlTable($table);
				}
			}
			else 
			{
				$table = $this->getTable();
			}
			$this->options["alias"][$table] = $alias;
		}
		return $this;
	}
	public function force($force) 
	{
		$this->options["force"] = $force;
		return $this;
	}
	public function comment($comment) 
	{
		$this->options["comment"] = $comment;
		return $this;
	}
	public function fetchSql($fetch = true) 
	{
		$this->options["fetch_sql"] = $fetch;
		return $this;
	}
	public function fetchPdo($pdo = true) 
	{
		$this->options["fetch_pdo"] = $pdo;
		return $this;
	}
	public function master() 
	{
		$this->options["master"] = true;
		return $this;
	}
	public function strict($strict = true) 
	{
		$this->options["strict"] = $strict;
		return $this;
	}
	public function failException($fail = true) 
	{
		$this->options["fail"] = $fail;
		return $this;
	}
	public function sequence($sequence = NULL) 
	{
		$this->options["sequence"] = $sequence;
		return $this;
	}
	public function pk($pk) 
	{
		$this->pk = $pk;
		return $this;
	}
	public function whereTime($field, $op, $range = NULL) 
	{
		if( is_null($range) ) 
		{
			if( is_array($op) ) 
			{
				$range = $op;
			}
			else 
			{
				switch( strtolower($op) ) 
				{
					case "today": case "d": $range = array( "today", "tomorrow" );
					break;
					case "week": case "w": $range = array( "this week 00:00:00", "next week 00:00:00" );
					break;
					case "month": case "m": $range = array( "first Day of this month 00:00:00", "first Day of next month 00:00:00" );
					break;
					case "year": case "y": $range = array( "this year 1/1", "next year 1/1" );
					break;
					case "yesterday": $range = array( "yesterday", "today" );
					break;
					case "last week": $range = array( "last week 00:00:00", "this week 00:00:00" );
					break;
					case "last month": $range = array( "first Day of last month 00:00:00", "first Day of this month 00:00:00" );
					break;
					case "last year": $range = array( "last year 1/1", "this year 1/1" );
					break;
					default: $range = $op;
				}
			}
			$op = (is_array($range) ? "between" : ">");
		}
		$this->where($field, strtolower($op) . " time", $range);
		return $this;
	}
	public function getTableInfo($tableName = "", $fetch = "") 
	{
		if( !$tableName ) 
		{
			$tableName = $this->getTable();
		}
		if( is_array($tableName) ) 
		{
			$tableName = (key($tableName) ?: current($tableName));
		}
		if( strpos($tableName, ",") ) 
		{
			return false;
		}
		$tableName = $this->parseSqlTable($tableName);
		if( strpos($tableName, ")") ) 
		{
			return array( );
		}
		list($guid) = explode(" ", $tableName);
		$db = $this->getConfig("database");
		if( !isset(self::$info[$db . "." . $guid]) ) 
		{
			if( !strpos($guid, ".") ) 
			{
				$schema = $db . "." . $guid;
			}
			else 
			{
				$schema = $guid;
			}
			if( !\think\App::$debug && is_file(RUNTIME_PATH . "schema/" . $schema . ".php") ) 
			{
				$info = include(RUNTIME_PATH . "schema/" . $schema . ".php");
			}
			else 
			{
				$info = $this->connection->getFields($guid);
			}
			$fields = array_keys($info);
			$bind = $type = array( );
			foreach( $info as $key => $val ) 
			{
				$type[$key] = $val["type"];
				$bind[$key] = $this->getFieldBindType($val["type"]);
				if( !empty($val["primary"]) ) 
				{
					$pk[] = $key;
				}
			}
			if( isset($pk) ) 
			{
				$pk = (1 < count($pk) ? $pk : $pk[0]);
			}
			else 
			{
				$pk = null;
			}
			self::$info[$db . "." . $guid] = array( "fields" => $fields, "type" => $type, "bind" => $bind, "pk" => $pk );
		}
		return ($fetch ? self::$info[$db . "." . $guid][$fetch] : self::$info[$db . "." . $guid]);
	}
	public function getPk($options = "") 
	{
		if( !empty($this->pk) ) 
		{
			$pk = $this->pk;
		}
		else 
		{
			$pk = $this->getTableInfo((is_array($options) ? $options["table"] : $options), "pk");
		}
		return $pk;
	}
	public function getTableFields($table = "") 
	{
		return $this->getTableInfo(($table ?: $this->getOptions("table")), "fields");
	}
	public function getFieldsType($table = "") 
	{
		return $this->getTableInfo(($table ?: $this->getOptions("table")), "type");
	}
	public function getFieldsBind($table = "") 
	{
		$types = $this->getFieldsType($table);
		$bind = array( );
		if( $types ) 
		{
			foreach( $types as $key => $type ) 
			{
				$bind[$key] = $this->getFieldBindType($type);
			}
		}
		return $bind;
	}
	protected function getFieldBindType($type) 
	{
		if( 0 === strpos($type, "set") || 0 === strpos($type, "enum") ) 
		{
			$bind = \PDO::PARAM_STR;
		}
		else 
		{
			if( preg_match("/(int|double|float|decimal|real|numeric|serial|bit)/is", $type) ) 
			{
				$bind = \PDO::PARAM_INT;
			}
			else 
			{
				if( preg_match("/bool/is", $type) ) 
				{
					$bind = \PDO::PARAM_BOOL;
				}
				else 
				{
					$bind = \PDO::PARAM_STR;
				}
			}
		}
		return $bind;
	}
	public function bind($key, $value = false, $type = \PDO::PARAM_STR) 
	{
		if( is_array($key) ) 
		{
			$this->bind = array_merge($this->bind, $key);
		}
		else 
		{
			$this->bind[$key] = array( $value, $type );
		}
		return $this;
	}
	public function isBind($key) 
	{
		return isset($this->bind[$key]);
	}
	protected function options(array $options) 
	{
		$this->options = $options;
		return $this;
	}
	public function getOptions($name = "") 
	{
		if( "" === $name ) 
		{
			return $this->options;
		}
		return (isset($this->options[$name]) ? $this->options[$name] : null);
	}
	public function with($with) 
	{
		if( empty($with) ) 
		{
			return $this;
		}
		if( is_string($with) ) 
		{
			$with = explode(",", $with);
		}
		$first = true;
		$class = $this->model;
		foreach( $with as $key => $relation ) 
		{
			$subRelation = "";
			$closure = false;
			if( $relation instanceof \Closure ) 
			{
				$closure = $relation;
				$relation = $key;
				$with[$key] = $key;
			}
			else 
			{
				if( is_array($relation) ) 
				{
					$subRelation = $relation;
					$relation = $key;
				}
				else 
				{
					if( is_string($relation) && strpos($relation, ".") ) 
					{
						$with[$key] = $relation;
						list($relation, $subRelation) = explode(".", $relation, 2);
					}
				}
			}
			$relation = \think\Loader::parseName($relation, 1, false);
			$model = $class->$relation();
			if( $model instanceof \think\model\relation\OneToOne && 0 == $model->getEagerlyType() ) 
			{
				$model->eagerly($this, $relation, $subRelation, $closure, $first);
				$first = false;
			}
			else 
			{
				if( $closure ) 
				{
					$with[$key] = $closure;
				}
			}
		}
		$this->via();
		if( isset($this->options["with"]) ) 
		{
			$this->options["with"] = array_merge($this->options["with"], $with);
		}
		else 
		{
			$this->options["with"] = $with;
		}
		return $this;
	}
	public function withCount($relation, $subQuery = true) 
	{
		if( !$subQuery ) 
		{
			$this->options["with_count"] = $relation;
		}
		else 
		{
			$relations = (is_string($relation) ? explode(",", $relation) : $relation);
			if( !isset($this->options["field"]) ) 
			{
				$this->field("*");
			}
			foreach( $relations as $key => $relation ) 
			{
				$closure = false;
				if( $relation instanceof \Closure ) 
				{
					$closure = $relation;
					$relation = $key;
				}
				$relation = \think\Loader::parseName($relation, 1, false);
				$count = "(" . $this->model->$relation()->getRelationCountQuery($closure) . ")";
				$this->field(array( $count => \think\Loader::parseName($relation) . "_count" ));
			}
		}
		return $this;
	}
	public function withField($field) 
	{
		$this->options["with_field"] = $field;
		return $this;
	}
	public function via($via = "") 
	{
		$this->options["via"] = $via;
		return $this;
	}
	public function relation($relation) 
	{
		if( empty($relation) ) 
		{
			return $this;
		}
		if( is_string($relation) ) 
		{
			$relation = explode(",", $relation);
		}
		if( isset($this->options["relation"]) ) 
		{
			$this->options["relation"] = array_merge($this->options["relation"], $relation);
		}
		else 
		{
			$this->options["relation"] = $relation;
		}
		return $this;
	}
	protected function parsePkWhere($data, &$options) 
	{
		$pk = $this->getPk($options);
		$table = (is_array($options["table"]) ? key($options["table"]) : $options["table"]);
		if( !empty($options["alias"][$table]) ) 
		{
			$alias = $options["alias"][$table];
		}
		if( is_string($pk) ) 
		{
			$key = (isset($alias) ? $alias . "." . $pk : $pk);
			if( is_array($data) ) 
			{
				$where[$key] = (isset($data[$pk]) ? $data[$pk] : array( "in", $data ));
			}
			else 
			{
				$where[$key] = (strpos($data, ",") ? array( "IN", $data ) : $data);
			}
		}
		else 
		{
			if( is_array($pk) && is_array($data) && !empty($data) ) 
			{
				foreach( $pk as $key ) 
				{
					if( isset($data[$key]) ) 
					{
						$attr = (isset($alias) ? $alias . "." . $key : $key);
						$where[$attr] = $data[$key];
					}
					else 
					{
						throw new \think\Exception("miss complex primary data");
					}
				}
			}
		}
		if( !empty($where) ) 
		{
			if( isset($options["where"]["AND"]) ) 
			{
				$options["where"]["AND"] = array_merge($options["where"]["AND"], $where);
			}
			else 
			{
				$options["where"]["AND"] = $where;
			}
		}
	}
	public function insert(array $data = array( ), $replace = false, $getLastInsID = false, $sequence = NULL) 
	{
		$options = $this->parseExpress();
		$data = array_merge($options["data"], $data);
		$sql = $this->builder->insert($data, $options, $replace);
		$bind = $this->getBind();
		if( $options["fetch_sql"] ) 
		{
			return $this->connection->getRealSql($sql, $bind);
		}
		$result = (0 === $sql ? 0 : $this->execute($sql, $bind, $this));
		if( $result ) 
		{
			$sequence = ($sequence ?: (isset($options["sequence"]) ? $options["sequence"] : null));
			$lastInsId = $this->getLastInsID($sequence);
			if( $lastInsId ) 
			{
				$pk = $this->getPk($options);
				if( is_string($pk) ) 
				{
					$data[$pk] = $lastInsId;
				}
			}
			$options["data"] = $data;
			$this->trigger("after_insert", $options);
			if( $getLastInsID ) 
			{
				return $lastInsId;
			}
		}
		return $result;
	}
	public function insertGetId(array $data, $replace = false, $sequence = NULL) 
	{
		return $this->insert($data, $replace, true, $sequence);
	}
	public function insertAll(array $dataSet, $replace = false, $limit = NULL) 
	{
		$options = $this->parseExpress();
		if( !is_array(reset($dataSet)) ) 
		{
			return false;
		}
		if( is_null($limit) ) 
		{
			$sql = $this->builder->insertAll($dataSet, $options, $replace);
		}
		else 
		{
			$array = array_chunk($dataSet, $limit, true);
			foreach( $array as $item ) 
			{
				$sql[] = $this->builder->insertAll($item, $options, $replace);
			}
		}
		$bind = $this->getBind();
		if( $options["fetch_sql"] ) 
		{
			return $this->connection->getRealSql($sql, $bind);
		}
		if( is_array($sql) ) 
		{
			return $this->batchQuery($sql, $bind, $this);
		}
		return $this->execute($sql, $bind, $this);
	}
	public function selectInsert($fields, $table) 
	{
		$options = $this->parseExpress();
		$table = $this->parseSqlTable($table);
		$sql = $this->builder->selectInsert($fields, $table, $options);
		$bind = $this->getBind();
		if( $options["fetch_sql"] ) 
		{
			return $this->connection->getRealSql($sql, $bind);
		}
		return $this->execute($sql, $bind, $this);
	}
	public function update(array $data = array( )) 
	{
		$options = $this->parseExpress();
		$data = array_merge($options["data"], $data);
		$pk = $this->getPk($options);
		if( isset($options["cache"]) && is_string($options["cache"]["key"]) ) 
		{
			$key = $options["cache"]["key"];
		}
		if( empty($options["where"]) ) 
		{
			if( is_string($pk) && isset($data[$pk]) ) 
			{
				$where[$pk] = $data[$pk];
				if( !isset($key) ) 
				{
					$key = "think:" . $options["table"] . "|" . $data[$pk];
				}
				unset($data[$pk]);
			}
			else 
			{
				if( is_array($pk) ) 
				{
					foreach( $pk as $field ) 
					{
						if( isset($data[$field]) ) 
						{
							$where[$field] = $data[$field];
							unset($data[$field]);
						}
						else 
						{
							throw new \think\Exception("miss complex primary data");
						}
					}
				}
			}
			if( !isset($where) ) 
			{
				throw new \think\Exception("miss update condition");
			}
			$options["where"]["AND"] = $where;
		}
		else 
		{
			if( !isset($key) && is_string($pk) && isset($options["where"]["AND"][$pk]) ) 
			{
				$key = $this->getCacheKey($options["where"]["AND"][$pk], $options, $this->bind);
			}
		}
		$sql = $this->builder->update($data, $options);
		$bind = $this->getBind();
		if( $options["fetch_sql"] ) 
		{
			return $this->connection->getRealSql($sql, $bind);
		}
		if( isset($key) && \think\Cache::get($key) ) 
		{
			\think\Cache::rm($key);
		}
		else 
		{
			if( !empty($options["cache"]["tag"]) ) 
			{
				\think\Cache::clear($options["cache"]["tag"]);
			}
		}
		$result = ("" == $sql ? 0 : $this->execute($sql, $bind, $this));
		if( $result ) 
		{
			if( is_string($pk) && isset($where[$pk]) ) 
			{
				$data[$pk] = $where[$pk];
			}
			else 
			{
				if( is_string($pk) && isset($key) && strpos($key, "|") ) 
				{
					list($a, $val) = explode("|", $key);
					$data[$pk] = $val;
				}
			}
			$options["data"] = $data;
			$this->trigger("after_update", $options);
		}
		return $result;
	}
	public function getPdo() 
	{
		$options = $this->parseExpress();
		$sql = $this->builder->select($options);
		$bind = $this->getBind();
		if( $options["fetch_sql"] ) 
		{
			return $this->connection->getRealSql($sql, $bind);
		}
		return $this->query($sql, $bind, $options["master"], true);
	}
	public function select($data = NULL) 
	{
		if( $data instanceof Query ) 
		{
			return $data->select();
		}
		if( $data instanceof \Closure ) 
		{
			call_user_func_array($data, array( $this ));
			$data = null;
		}
		$options = $this->parseExpress();
		if( false === $data ) 
		{
			$options["fetch_sql"] = true;
		}
		else 
		{
			if( !is_null($data) ) 
			{
				$this->parsePkWhere($data, $options);
			}
		}
		$resultSet = false;
		if( empty($options["fetch_sql"]) && !empty($options["cache"]) ) 
		{
			$cache = $options["cache"];
			unset($options["cache"]);
			$key = (is_string($cache["key"]) ? $cache["key"] : md5($this->connection->getConfig("database") . "." . serialize($options) . serialize($this->bind)));
			$resultSet = \think\Cache::get($key);
		}
		if( false === $resultSet ) 
		{
			$sql = $this->builder->select($options);
			$bind = $this->getBind();
			if( $options["fetch_sql"] ) 
			{
				return $this->connection->getRealSql($sql, $bind);
			}
			$options["data"] = $data;
			if( $resultSet = $this->trigger("before_select", $options) ) 
			{
			}
			else 
			{
				$resultSet = $this->query($sql, $bind, $options["master"], $options["fetch_pdo"]);
				if( $resultSet instanceof \PDOStatement ) 
				{
					return $resultSet;
				}
			}
			if( isset($cache) && false !== $resultSet ) 
			{
				$this->cacheData($key, $resultSet, $cache);
			}
		}
		if( !empty($this->model) ) 
		{
			if( 0 < count($resultSet) ) 
			{
				foreach( $resultSet as $key => $result ) 
				{
					$model = $this->model->newInstance($result);
					$model->isUpdate(true);
					if( !empty($options["relation"]) ) 
					{
						$model->relationQuery($options["relation"]);
					}
					if( !empty($options["with_count"]) ) 
					{
						$model->relationCount($model, $options["with_count"]);
					}
					$resultSet[$key] = $model;
				}
				if( !empty($options["with"]) ) 
				{
					$model->eagerlyResultSet($resultSet, $options["with"]);
				}
				$resultSet = $model->toCollection($resultSet);
			}
			else 
			{
				$resultSet = $this->model->toCollection($resultSet);
			}
		}
		else 
		{
			if( "collection" == $this->connection->getConfig("resultset_type") ) 
			{
				$resultSet = new \think\Collection($resultSet);
			}
		}
		if( !empty($options["fail"]) && count($resultSet) == 0 ) 
		{
			$this->throwNotFound($options);
		}
		return $resultSet;
	}
	protected function cacheData($key, $data, $config = array( )) 
	{
		if( isset($config["tag"]) ) 
		{
			\think\Cache::tag($config["tag"])->set($key, $data, $config["expire"]);
		}
		else 
		{
			\think\Cache::set($key, $data, $config["expire"]);
		}
	}
	protected function getCacheKey($value, $options, $bind = array( )) 
	{
		if( is_scalar($value) ) 
		{
			$data = $value;
		}
		else 
		{
			if( is_array($value) && is_string($value[0]) && "eq" == strtolower($value[0]) ) 
			{
				$data = $value[1];
			}
		}
		$prefix = $this->connection->getConfig("database") . ".";
		if( isset($data) ) 
		{
			return "think:" . $prefix . ((is_array($options["table"]) ? key($options["table"]) : $options["table"])) . "|" . $data;
		}
		try 
		{
			return md5($prefix . serialize($options) . serialize($bind));
		}
		catch( \Exception $e ) 
		{
			throw new \think\Exception("closure not support cache(true)");
		}
	}
	public function find($data = NULL) 
	{
		if( $data instanceof Query ) 
		{
			return $data->find();
		}
		if( $data instanceof \Closure ) 
		{
			call_user_func_array($data, array( $this ));
			$data = null;
		}
		$options = $this->parseExpress();
		$pk = $this->getPk($options);
		if( !is_null($data) ) 
		{
			$this->parsePkWhere($data, $options);
		}
		else 
		{
			if( !empty($options["cache"]) && true === $options["cache"]["key"] && is_string($pk) && isset($options["where"]["AND"][$pk]) ) 
			{
				$key = $this->getCacheKey($options["where"]["AND"][$pk], $options, $this->bind);
			}
		}
		$options["limit"] = 1;
		$result = false;
		if( empty($options["fetch_sql"]) && !empty($options["cache"]) ) 
		{
			$cache = $options["cache"];
			if( true === $cache["key"] && !is_null($data) && !is_array($data) ) 
			{
				$key = "think:" . $this->connection->getConfig("database") . "." . ((is_array($options["table"]) ? key($options["table"]) : $options["table"])) . "|" . $data;
			}
			else 
			{
				if( is_string($cache["key"]) ) 
				{
					$key = $cache["key"];
				}
				else 
				{
					if( !isset($key) ) 
					{
						$key = md5($this->connection->getConfig("database") . "." . serialize($options) . serialize($this->bind));
					}
				}
			}
			$result = \think\Cache::get($key);
		}
		if( false === $result ) 
		{
			$sql = $this->builder->select($options);
			$bind = $this->getBind();
			if( $options["fetch_sql"] ) 
			{
				return $this->connection->getRealSql($sql, $bind);
			}
			if( is_string($pk) && !is_array($data) ) 
			{
				if( isset($key) && strpos($key, "|") ) 
				{
					list($a, $val) = explode("|", $key);
					$item[$pk] = $val;
				}
				else 
				{
					$item[$pk] = $data;
				}
				$data = $item;
			}
			$options["data"] = $data;
			if( $result = $this->trigger("before_find", $options) ) 
			{
			}
			else 
			{
				$resultSet = $this->query($sql, $bind, $options["master"], $options["fetch_pdo"]);
				if( $resultSet instanceof \PDOStatement ) 
				{
					return $resultSet;
				}
				$result = (isset($resultSet[0]) ? $resultSet[0] : null);
			}
			if( isset($cache) && $result ) 
			{
				$this->cacheData($key, $result, $cache);
			}
		}
		if( !empty($result) ) 
		{
			if( !empty($this->model) ) 
			{
				$result = $this->model->newInstance($result);
				$result->isUpdate(true, (isset($options["where"]["AND"]) ? $options["where"]["AND"] : null));
				if( !empty($options["relation"]) ) 
				{
					$result->relationQuery($options["relation"]);
				}
				if( !empty($options["with"]) ) 
				{
					$result->eagerlyResult($result, $options["with"]);
				}
				if( !empty($options["with_count"]) ) 
				{
					$result->relationCount($result, $options["with_count"]);
				}
			}
		}
		else 
		{
			if( !empty($options["fail"]) ) 
			{
				$this->throwNotFound($options);
			}
		}
		return $result;
	}
	protected function throwNotFound($options = array( )) 
	{
		if( !empty($this->model) ) 
		{
			$class = get_class($this->model);
			throw new exception\ModelNotFoundException("model data Not Found:" . $class, $class, $options);
		}
		$table = (is_array($options["table"]) ? key($options["table"]) : $options["table"]);
		throw new exception\DataNotFoundException("table data not Found:" . $table, $table, $options);
	}
	public function selectOrFail($data = NULL) 
	{
		return $this->failException(true)->select($data);
	}
	public function findOrFail($data = NULL) 
	{
		return $this->failException(true)->find($data);
	}
	public function chunk($count, $callback, $column = NULL, $order = "asc") 
	{
		$options = $this->getOptions();
		if( empty($options["table"]) ) 
		{
			$options["table"] = $this->getTable();
		}
		$column = ($column ?: $this->getPk($options));
		if( isset($options["order"]) ) 
		{
			if( \think\App::$debug ) 
			{
				throw new \LogicException("chunk not support call order");
			}
			unset($options["order"]);
		}
		$bind = $this->bind;
		if( is_array($column) ) 
		{
			$times = 1;
			$query = $this->options($options)->page($times, $count);
		}
		else 
		{
			if( strpos($column, ".") ) 
			{
				list($alias, $key) = explode(".", $column);
			}
			else 
			{
				$key = $column;
			}
			$query = $this->options($options)->limit($count);
		}
		$resultSet = $query->order($column, $order)->select();
		while( 0 < count($resultSet) ) 
		{
			if( $resultSet instanceof \think\Collection ) 
			{
				$resultSet = $resultSet->all();
			}
			if( false === call_user_func($callback, $resultSet) ) 
			{
				return false;
			}
			if( is_array($column) ) 
			{
				$times++;
				$query = $this->options($options)->page($times, $count);
			}
			else 
			{
				$end = end($resultSet);
				$lastId = (is_array($end) ? $end[$key] : $end->getData($key));
				$query = $this->options($options)->limit($count)->where($column, ("asc" == strtolower($order) ? ">" : "<"), $lastId);
			}
			$resultSet = $query->bind($bind)->order($column, $order)->select();
		}
		return true;
	}
	public function getBind() 
	{
		$bind = $this->bind;
		$this->bind = array( );
		return $bind;
	}
	public function buildSql($sub = true) 
	{
		return ($sub ? "( " . $this->select(false) . " )" : $this->select(false));
	}
	public function delete($data = NULL) 
	{
		$options = $this->parseExpress();
		$pk = $this->getPk($options);
		if( isset($options["cache"]) && is_string($options["cache"]["key"]) ) 
		{
			$key = $options["cache"]["key"];
		}
		if( !is_null($data) && true !== $data ) 
		{
			if( !isset($key) && !is_array($data) ) 
			{
				$key = "think:" . $options["table"] . "|" . $data;
			}
			$this->parsePkWhere($data, $options);
		}
		else 
		{
			if( !isset($key) && is_string($pk) && isset($options["where"]["AND"][$pk]) ) 
			{
				$key = $this->getCacheKey($options["where"]["AND"][$pk], $options, $this->bind);
			}
		}
		if( true !== $data && empty($options["where"]) ) 
		{
			throw new \think\Exception("delete without condition");
		}
		$sql = $this->builder->delete($options);
		$bind = $this->getBind();
		if( $options["fetch_sql"] ) 
		{
			return $this->connection->getRealSql($sql, $bind);
		}
		if( isset($key) && \think\Cache::get($key) ) 
		{
			\think\Cache::rm($key);
		}
		else 
		{
			if( !empty($options["cache"]["tag"]) ) 
			{
				\think\Cache::clear($options["cache"]["tag"]);
			}
		}
		$result = $this->execute($sql, $bind, $this);
		if( $result ) 
		{
			if( !is_array($data) && is_string($pk) && isset($key) && strpos($key, "|") ) 
			{
				list($a, $val) = explode("|", $key);
				$item[$pk] = $val;
				$data = $item;
			}
			$options["data"] = $data;
			$this->trigger("after_delete", $options);
		}
		return $result;
	}
	protected function parseExpress() 
	{
		$options = $this->options;
		if( empty($options["table"]) ) 
		{
			$options["table"] = $this->getTable();
		}
		if( !isset($options["where"]) ) 
		{
			$options["where"] = array( );
		}
		else 
		{
			if( isset($options["view"]) ) 
			{
				foreach( array( "AND", "OR" ) as $logic ) 
				{
					if( isset($options["where"][$logic]) ) 
					{
						foreach( $options["where"][$logic] as $key => $val ) 
						{
							if( array_key_exists($key, $options["map"]) ) 
							{
								$options["where"][$logic][$options["map"][$key]] = $val;
								unset($options["where"][$logic][$key]);
							}
						}
					}
				}
				if( isset($options["order"]) ) 
				{
					if( is_string($options["order"]) ) 
					{
						$options["order"] = explode(",", $options["order"]);
					}
					foreach( $options["order"] as $key => $val ) 
					{
						if( is_numeric($key) ) 
						{
							if( strpos($val, " ") ) 
							{
								list($field, $sort) = explode(" ", $val);
								if( array_key_exists($field, $options["map"]) ) 
								{
									$options["order"][$options["map"][$field]] = $sort;
									unset($options["order"][$key]);
								}
							}
							else 
							{
								if( array_key_exists($val, $options["map"]) ) 
								{
									$options["order"][$options["map"][$val]] = "asc";
									unset($options["order"][$key]);
								}
							}
						}
						else 
						{
							if( array_key_exists($key, $options["map"]) ) 
							{
								$options["order"][$options["map"][$key]] = $val;
								unset($options["order"][$key]);
							}
						}
					}
				}
			}
		}
		if( !isset($options["field"]) ) 
		{
			$options["field"] = "*";
		}
		if( !isset($options["data"]) ) 
		{
			$options["data"] = array( );
		}
		if( !isset($options["strict"]) ) 
		{
			$options["strict"] = $this->getConfig("fields_strict");
		}
		foreach( array( "master", "lock", "fetch_pdo", "fetch_sql", "distinct" ) as $name ) 
		{
			if( !isset($options[$name]) ) 
			{
				$options[$name] = false;
			}
		}
		if( isset(static::$readMaster["*"]) || is_string($options["table"]) && isset(static::$readMaster[$options["table"]]) ) 
		{
			$options["master"] = true;
		}
		foreach( array( "join", "union", "group", "having", "limit", "order", "force", "comment" ) as $name ) 
		{
			if( !isset($options[$name]) ) 
			{
				$options[$name] = "";
			}
		}
		if( isset($options["page"]) ) 
		{
			list($page, $listRows) = $options["page"];
			$page = (0 < $page ? $page : 1);
			$listRows = (0 < $listRows ? $listRows : (is_numeric($options["limit"]) ? $options["limit"] : 20));
			$offset = $listRows * ($page - 1);
			$options["limit"] = $offset . "," . $listRows;
		}
		$this->options = array( );
		return $options;
	}
	public static function event($event, $callback) 
	{
		self::$event[$event] = $callback;
	}
	protected function trigger($event, $params = array( )) 
	{
		$result = false;
		if( isset(self::$event[$event]) ) 
		{
			$callback = self::$event[$event];
			$result = call_user_func_array($callback, array( $params, $this ));
		}
		return $result;
	}
}
?>