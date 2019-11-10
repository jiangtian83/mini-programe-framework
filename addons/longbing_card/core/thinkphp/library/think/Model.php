<?php  namespace think;
abstract class Model implements \JsonSerializable, \ArrayAccess 
{
	protected static $links = array( );
	protected $connection = array( );
	protected $parent = NULL;
	protected $query = NULL;
	protected $name = NULL;
	protected $table = NULL;
	protected $class = NULL;
	private static $event = array( );
	protected $error = NULL;
	protected $validate = NULL;
	protected $pk = NULL;
	protected $field = array( );
	protected $except = array( );
	protected $disuse = array( );
	protected $readonly = array( );
	protected $visible = array( );
	protected $hidden = array( );
	protected $append = array( );
	protected $data = array( );
	protected $origin = array( );
	protected $relation = array( );
	protected $auto = array( );
	protected $insert = array( );
	protected $update = array( );
	protected $autoWriteTimestamp = NULL;
	protected $createTime = "create_time";
	protected $updateTime = "update_time";
	protected $dateFormat = NULL;
	protected $type = array( );
	protected $isUpdate = false;
	protected $force = false;
	protected $updateWhere = NULL;
	protected $failException = false;
	protected $useGlobalScope = true;
	protected $batchValidate = false;
	protected $resultSetType = NULL;
	protected $relationWrite = NULL;
	protected static $initialized = array( );
	protected static $readMaster = NULL;
	public function __construct($data = array( )) 
	{
		if( is_object($data) ) 
		{
			$this->data = get_object_vars($data);
		}
		else 
		{
			$this->data = $data;
		}
		if( $this->disuse ) 
		{
			foreach( (array) $this->disuse as $key ) 
			{
				if( array_key_exists($key, $this->data) ) 
				{
					unset($this->data[$key]);
				}
			}
		}
		$this->origin = $this->data;
		$this->class = get_called_class();
		if( empty($this->name) ) 
		{
			$name = str_replace("\\", "/", $this->class);
			$this->name = basename($name);
			if( Config::get("class_suffix") ) 
			{
				$suffix = basename(dirname($name));
				$this->name = substr($this->name, 0, 0 - strlen($suffix));
			}
		}
		if( is_null($this->autoWriteTimestamp) ) 
		{
			$this->autoWriteTimestamp = $this->getQuery()->getConfig("auto_timestamp");
		}
		if( is_null($this->dateFormat) ) 
		{
			$this->dateFormat = $this->getQuery()->getConfig("datetime_format");
		}
		if( is_null($this->resultSetType) ) 
		{
			$this->resultSetType = $this->getQuery()->getConfig("resultset_type");
		}
		$this->initialize();
	}
	public function readMaster($all = false) 
	{
		$model = ($all ? "*" : $this->class);
		static::$readMaster[$model] = true;
		return $this;
	}
	protected function buildQuery() 
	{
		if( !empty($this->connection) ) 
		{
			if( is_array($this->connection) ) 
			{
				$connection = array_merge(Config::get("database"), $this->connection);
			}
			else 
			{
				$connection = $this->connection;
			}
		}
		else 
		{
			$connection = array( );
		}
		$con = Db::connect($connection);
		$queryClass = ($this->query ?: $con->getConfig("query"));
		$query = new $queryClass($con, $this);
		if( isset(static::$readMaster["*"]) || isset(static::$readMaster[$this->class]) ) 
		{
			$query->master(true);
		}
		if( !empty($this->table) ) 
		{
			$query->setTable($this->table);
		}
		else 
		{
			$query->name($this->name);
		}
		if( !empty($this->pk) ) 
		{
			$query->pk($this->pk);
		}
		return $query;
	}
	public function newInstance($data = array( ), $isUpdate = false, $where = NULL) 
	{
		return (new static($data))->isUpdate($isUpdate, $where);
	}
	public function getQuery($buildNewQuery = false) 
	{
		if( $buildNewQuery ) 
		{
			return $this->buildQuery();
		}
		if( !isset(self::$links[$this->class]) ) 
		{
			self::$links[$this->class] = $this->buildQuery();
		}
		return self::$links[$this->class];
	}
	public function db($useBaseQuery = true, $buildNewQuery = true) 
	{
		$query = $this->getQuery($buildNewQuery);
		if( $useBaseQuery && method_exists($this, "base") ) 
		{
			call_user_func_array(array( $this, "base" ), array( $query ));
		}
		return $query;
	}
	protected function initialize() 
	{
		$class = get_class($this);
		if( !isset(static::$initialized[$class]) ) 
		{
			static::$initialized[$class] = true;
			static::init();
		}
	}
	protected static function init() 
	{
	}
	public function setParent($model) 
	{
		$this->parent = $model;
		return $this;
	}
	public function getParent() 
	{
		return $this->parent;
	}
	public function data($data, $value = NULL) 
	{
		if( is_string($data) ) 
		{
			$this->data[$data] = $value;
		}
		else 
		{
			$this->data = array( );
			if( is_object($data) ) 
			{
				$data = get_object_vars($data);
			}
			if( true === $value ) 
			{
				foreach( $data as $key => $value ) 
				{
					$this->setAttr($key, $value, $data);
				}
			}
			else 
			{
				$this->data = $data;
			}
		}
		return $this;
	}
	public function getData($name = NULL) 
	{
		if( is_null($name) ) 
		{
			return $this->data;
		}
		if( array_key_exists($name, $this->data) ) 
		{
			return $this->data[$name];
		}
		if( array_key_exists($name, $this->relation) ) 
		{
			return $this->relation[$name];
		}
		throw new \InvalidArgumentException("property not exists:" . $this->class . "->" . $name);
	}
	public function isAutoWriteTimestamp($auto) 
	{
		$this->autoWriteTimestamp = $auto;
		return $this;
	}
	public function force($force = true) 
	{
		$this->force = $force;
		return $this;
	}
	public function setAttr($name, $value, $data = array( )) 
	{
		if( is_null($value) && $this->autoWriteTimestamp && in_array($name, array( $this->createTime, $this->updateTime )) ) 
		{
			$value = $this->autoWriteTimestamp($name);
		}
		else 
		{
			$method = "set" . Loader::parseName($name, 1) . "Attr";
			if( method_exists($this, $method) ) 
			{
				$value = $this->$method($value, array_merge($this->data, $data), $this->relation);
			}
			else 
			{
				if( isset($this->type[$name]) ) 
				{
					$value = $this->writeTransform($value, $this->type[$name]);
				}
			}
		}
		$this->data[$name] = $value;
		return $this;
	}
	public function getRelation($name = NULL) 
	{
		if( is_null($name) ) 
		{
			return $this->relation;
		}
		if( array_key_exists($name, $this->relation) ) 
		{
			return $this->relation[$name];
		}
	}
	public function setRelation($name, $value) 
	{
		$this->relation[$name] = $value;
		return $this;
	}
	protected function autoWriteTimestamp($name) 
	{
		if( isset($this->type[$name]) ) 
		{
			$type = $this->type[$name];
			if( strpos($type, ":") ) 
			{
				list($type, $param) = explode(":", $type, 2);
			}
			switch( $type ) 
			{
				case "datetime": case "date": $format = (!empty($param) ? $param : $this->dateFormat);
				$value = $this->formatDateTime(time(), $format);
				break;
				case "timestamp": case "integer": default: $value = time();
				break;
			}
		}
		else 
		{
			if( is_string($this->autoWriteTimestamp) && in_array(strtolower($this->autoWriteTimestamp), array( "datetime", "date", "timestamp" )) ) 
			{
				$value = $this->formatDateTime(time(), $this->dateFormat);
			}
			else 
			{
				$value = $this->formatDateTime(time(), $this->dateFormat, true);
			}
		}
		return $value;
	}
	protected function formatDateTime($time, $format, $timestamp = false) 
	{
		if( false !== strpos($format, "\\") ) 
		{
			$time = new $format($time);
		}
		else 
		{
			if( !$timestamp && false !== $format ) 
			{
				$time = date($format, $time);
			}
		}
		return $time;
	}
	protected function writeTransform($value, $type) 
	{
		if( is_null($value) ) 
		{
			return NULL;
		}
		if( is_array($type) ) 
		{
			list($type, $param) = $type;
		}
		else 
		{
			if( strpos($type, ":") ) 
			{
				list($type, $param) = explode(":", $type, 2);
			}
		}
		switch( $type ) 
		{
			case "integer": $value = (int) $value;
			break;
			case "float": if( empty($param) ) 
			{
				$value = (double) $value;
			}
			else 
			{
				$value = (double) number_format($value, $param, ".", "");
			}
			break;
			case "boolean": $value = (bool) $value;
			break;
			case "timestamp": if( !is_numeric($value) ) 
			{
				$value = strtotime($value);
			}
			break;
			case "datetime": $format = (!empty($param) ? $param : $this->dateFormat);
			$value = (is_numeric($value) ? $value : strtotime($value));
			$value = $this->formatDateTime($value, $format);
			break;
			case "object": if( is_object($value) ) 
			{
				$value = json_encode($value, JSON_FORCE_OBJECT);
			}
			break;
			case "array": $value = (array) $value;
			case "json": $option = (!empty($param) ? (int) $param : JSON_UNESCAPED_UNICODE);
			$value = json_encode($value, $option);
			break;
			case "serialize": $value = serialize($value);
			break;
		}
		return $value;
	}
	public function getAttr($name) 
	{
		try 
		{
			$notFound = false;
			$value = $this->getData($name);
		}
		catch( \InvalidArgumentException $e ) 
		{
			$notFound = true;
			$value = null;
		}
		$method = "get" . Loader::parseName($name, 1) . "Attr";
		if( method_exists($this, $method) ) 
		{
			$value = $this->$method($value, $this->data, $this->relation);
		}
		else 
		{
			if( isset($this->type[$name]) ) 
			{
				$value = $this->readTransform($value, $this->type[$name]);
			}
			else 
			{
				if( in_array($name, array( $this->createTime, $this->updateTime )) ) 
				{
					if( is_string($this->autoWriteTimestamp) && in_array(strtolower($this->autoWriteTimestamp), array( "datetime", "date", "timestamp" )) ) 
					{
						$value = $this->formatDateTime(strtotime($value), $this->dateFormat);
					}
					else 
					{
						$value = $this->formatDateTime($value, $this->dateFormat);
					}
				}
				else 
				{
					if( $notFound ) 
					{
						$relation = Loader::parseName($name, 1, false);
						if( method_exists($this, $relation) ) 
						{
							$modelRelation = $this->$relation();
							$value = $this->getRelationData($modelRelation);
							$this->relation[$name] = $value;
						}
						else 
						{
							throw new \InvalidArgumentException("property not exists:" . $this->class . "->" . $name);
						}
					}
				}
			}
		}
		return $value;
	}
	protected function getRelationData(model\Relation $modelRelation) 
	{
		if( $this->parent && !$modelRelation->isSelfRelation() && get_class($modelRelation->getModel()) == get_class($this->parent) ) 
		{
			$value = $this->parent;
		}
		else 
		{
			if( method_exists($modelRelation, "getRelation") ) 
			{
				$value = $modelRelation->getRelation();
			}
			else 
			{
				throw new \BadMethodCallException("method not exists:" . get_class($modelRelation) . "-> getRelation");
			}
		}
		return $value;
	}
	protected function readTransform($value, $type) 
	{
		if( is_null($value) ) 
		{
			return NULL;
		}
		if( is_array($type) ) 
		{
			list($type, $param) = $type;
		}
		else 
		{
			if( strpos($type, ":") ) 
			{
				list($type, $param) = explode(":", $type, 2);
			}
		}
		switch( $type ) 
		{
			case "integer": $value = (int) $value;
			break;
			case "float": if( empty($param) ) 
			{
				$value = (double) $value;
			}
			else 
			{
				$value = (double) number_format($value, $param, ".", "");
			}
			break;
			case "boolean": $value = (bool) $value;
			break;
			case "timestamp": if( !is_null($value) ) 
			{
				$format = (!empty($param) ? $param : $this->dateFormat);
				$value = $this->formatDateTime($value, $format);
			}
			break;
			case "datetime": if( !is_null($value) ) 
			{
				$format = (!empty($param) ? $param : $this->dateFormat);
				$value = $this->formatDateTime(strtotime($value), $format);
			}
			break;
			case "json": $value = json_decode($value, true);
			break;
			case "array": $value = (empty($value) ? array( ) : json_decode($value, true));
			break;
			case "object": $value = (empty($value) ? new \stdClass() : json_decode($value));
			break;
			case "serialize": try 
			{
				$value = unserialize($value);
			}
			catch( \Exception $e ) 
			{
				$value = null;
			}
			break;
			default: if( false !== strpos($type, "\\") ) 
			{
				$value = new $type($value);
			}
		}
		return $value;
	}
	public function append($append = array( ), $override = false) 
	{
		$this->append = ($override ? $append : array_merge($this->append, $append));
		return $this;
	}
	public function appendRelationAttr($relation, $append) 
	{
		if( is_string($append) ) 
		{
			$append = explode(",", $append);
		}
		$relation = Loader::parseName($relation, 1, false);
		if( isset($this->relation[$relation]) ) 
		{
			$model = $this->relation[$relation];
		}
		else 
		{
			$model = $this->getRelationData($this->$relation());
		}
		if( $model instanceof Model ) 
		{
			foreach( $append as $key => $attr ) 
			{
				$key = (is_numeric($key) ? $attr : $key);
				if( isset($this->data[$key]) ) 
				{
					throw new Exception("bind attr has exists:" . $key);
				}
				$this->data[$key] = $model->getAttr($attr);
			}
		}
		return $this;
	}
	public function hidden($hidden = array( ), $override = false) 
	{
		$this->hidden = ($override ? $hidden : array_merge($this->hidden, $hidden));
		return $this;
	}
	public function visible($visible = array( ), $override = false) 
	{
		$this->visible = ($override ? $visible : array_merge($this->visible, $visible));
		return $this;
	}
	protected function parseAttr($attrs, &$result, $visible = true) 
	{
		$array = array( );
		foreach( $attrs as $key => $val ) 
		{
			if( is_array($val) ) 
			{
				if( $visible ) 
				{
					$array[] = $key;
				}
				$result[$key] = $val;
			}
			else 
			{
				if( strpos($val, ".") ) 
				{
					list($key, $name) = explode(".", $val);
					if( $visible ) 
					{
						$array[] = $key;
					}
					$result[$key][] = $name;
				}
				else 
				{
					$array[] = $val;
				}
			}
		}
		return $array;
	}
	protected function subToArray($model, $visible, $hidden, $key) 
	{
		if( isset($visible[$key]) ) 
		{
			$model->visible($visible[$key]);
		}
		else 
		{
			if( isset($hidden[$key]) ) 
			{
				$model->hidden($hidden[$key]);
			}
		}
		return $model->toArray();
	}
	public function toArray() 
	{
		$item = array( );
		$visible = array( );
		$hidden = array( );
		$data = array_merge($this->data, $this->relation);
		if( !empty($this->visible) ) 
		{
			$array = $this->parseAttr($this->visible, $visible);
			$data = array_intersect_key($data, array_flip($array));
		}
		else 
		{
			if( !empty($this->hidden) ) 
			{
				$array = $this->parseAttr($this->hidden, $hidden, false);
				$data = array_diff_key($data, array_flip($array));
			}
		}
		foreach( $data as $key => $val ) 
		{
			if( $val instanceof Model || $val instanceof model\Collection ) 
			{
				$item[$key] = $this->subToArray($val, $visible, $hidden, $key);
			}
			else 
			{
				if( is_array($val) && reset($val) instanceof Model ) 
				{
					$arr = array( );
					foreach( $val as $k => $value ) 
					{
						$arr[$k] = $this->subToArray($value, $visible, $hidden, $key);
					}
					$item[$key] = $arr;
				}
				else 
				{
					$item[$key] = $this->getAttr($key);
				}
			}
		}
		if( !empty($this->append) ) 
		{
			foreach( $this->append as $key => $name ) 
			{
				if( is_array($name) ) 
				{
					$relation = $this->getAttr($key);
					$item[$key] = $relation->append($name)->toArray();
				}
				else 
				{
					if( strpos($name, ".") ) 
					{
						list($key, $attr) = explode(".", $name);
						$relation = $this->getAttr($key);
						$item[$key] = $relation->append(array( $attr ))->toArray();
					}
					else 
					{
						$relation = Loader::parseName($name, 1, false);
						if( method_exists($this, $relation) ) 
						{
							$modelRelation = $this->$relation();
							$value = $this->getRelationData($modelRelation);
							if( method_exists($modelRelation, "getBindAttr") ) 
							{
								$bindAttr = $modelRelation->getBindAttr();
								if( $bindAttr ) 
								{
									foreach( $bindAttr as $key => $attr ) 
									{
										$key = (is_numeric($key) ? $attr : $key);
										if( isset($this->data[$key]) ) 
										{
											throw new Exception("bind attr has exists:" . $key);
										}
										$item[$key] = ($value ? $value->getAttr($attr) : null);
									}
									continue;
								}
							}
							$item[$name] = $value;
						}
						else 
						{
							$item[$name] = $this->getAttr($name);
						}
					}
				}
			}
		}
		return (!empty($item) ? $item : array( ));
	}
	public function toJson($options = JSON_UNESCAPED_UNICODE) 
	{
		return json_encode($this->toArray(), $options);
	}
	public function removeRelation() 
	{
		$this->relation = array( );
		return $this;
	}
	public function toCollection($collection) 
	{
		if( $this->resultSetType ) 
		{
			if( "collection" == $this->resultSetType ) 
			{
				$collection = new model\Collection($collection);
			}
			else 
			{
				if( false !== strpos($this->resultSetType, "\\") ) 
				{
					$class = $this->resultSetType;
					$collection = new $class($collection);
				}
			}
		}
		return $collection;
	}
	public function together($relation) 
	{
		if( is_string($relation) ) 
		{
			$relation = explode(",", $relation);
		}
		$this->relationWrite = $relation;
		return $this;
	}
	public function getPk($name = "") 
	{
		if( !empty($name) ) 
		{
			$table = $this->getQuery()->getTable($name);
			return $this->getQuery()->getPk($table);
		}
		if( empty($this->pk) ) 
		{
			$this->pk = $this->getQuery()->getPk();
		}
		return $this->pk;
	}
	protected function isPk($key) 
	{
		$pk = $this->getPk();
		if( is_string($pk) && $pk == $key ) 
		{
			return true;
		}
		if( is_array($pk) && in_array($key, $pk) ) 
		{
			return true;
		}
		return false;
	}
	public function save($data = array( ), $where = array( ), $sequence = NULL) 
	{
		if( is_string($data) ) 
		{
			$sequence = $data;
			$data = array( );
		}
		if( !empty($data) ) 
		{
			if( !$this->validateData($data) ) 
			{
				return false;
			}
			foreach( $data as $key => $value ) 
			{
				$this->setAttr($key, $value, $data);
			}
			if( !empty($where) ) 
			{
				$this->isUpdate = true;
				$this->updateWhere = $where;
			}
		}
		if( !empty($this->relationWrite) ) 
		{
			$relation = array( );
			foreach( $this->relationWrite as $key => $name ) 
			{
				if( is_array($name) ) 
				{
					if( key($name) === 0 ) 
					{
						$relation[$key] = array( );
						foreach( $name as $val ) 
						{
							if( isset($this->data[$val]) ) 
							{
								$relation[$key][$val] = $this->data[$val];
								unset($this->data[$val]);
							}
						}
					}
					else 
					{
						$relation[$key] = $name;
					}
				}
				else 
				{
					if( isset($this->relation[$name]) ) 
					{
						$relation[$name] = $this->relation[$name];
					}
					else 
					{
						if( isset($this->data[$name]) ) 
						{
							$relation[$name] = $this->data[$name];
							unset($this->data[$name]);
						}
					}
				}
			}
		}
		$this->autoCompleteData($this->auto);
		if( false === $this->trigger("before_write", $this) ) 
		{
			return false;
		}
		$pk = $this->getPk();
		if( $this->isUpdate ) 
		{
			$this->autoCompleteData($this->update);
			if( false === $this->trigger("before_update", $this) ) 
			{
				return false;
			}
			$data = $this->getChangedData();
			if( empty($data) || count($data) == 1 && is_string($pk) && isset($data[$pk]) ) 
			{
				if( isset($relation) ) 
				{
					$this->autoRelationUpdate($relation);
				}
				return 0;
			}
			if( $this->autoWriteTimestamp && $this->updateTime && !isset($data[$this->updateTime]) ) 
			{
				$data[$this->updateTime] = $this->autoWriteTimestamp($this->updateTime);
				$this->data[$this->updateTime] = $data[$this->updateTime];
			}
			if( empty($where) && !empty($this->updateWhere) ) 
			{
				$where = $this->updateWhere;
			}
			foreach( $this->data as $key => $val ) 
			{
				if( $this->isPk($key) ) 
				{
					$data[$key] = $val;
				}
			}
			$array = array( );
			foreach( (array) $pk as $key ) 
			{
				if( isset($data[$key]) ) 
				{
					$array[$key] = $data[$key];
					unset($data[$key]);
				}
			}
			if( !empty($array) ) 
			{
				$where = $array;
			}
			$allowFields = $this->checkAllowField(array_merge($this->auto, $this->update));
			if( !empty($allowFields) ) 
			{
				$result = $this->getQuery()->where($where)->strict(false)->field($allowFields)->update($data);
			}
			else 
			{
				$result = $this->getQuery()->where($where)->update($data);
			}
			if( isset($relation) ) 
			{
				$this->autoRelationUpdate($relation);
			}
			$this->trigger("after_update", $this);
		}
		else 
		{
			$this->autoCompleteData($this->insert);
			if( $this->autoWriteTimestamp ) 
			{
				if( $this->createTime && !isset($this->data[$this->createTime]) ) 
				{
					$this->data[$this->createTime] = $this->autoWriteTimestamp($this->createTime);
				}
				if( $this->updateTime && !isset($this->data[$this->updateTime]) ) 
				{
					$this->data[$this->updateTime] = $this->autoWriteTimestamp($this->updateTime);
				}
			}
			if( false === $this->trigger("before_insert", $this) ) 
			{
				return false;
			}
			$allowFields = $this->checkAllowField(array_merge($this->auto, $this->insert));
			if( !empty($allowFields) ) 
			{
				$result = $this->getQuery()->strict(false)->field($allowFields)->insert($this->data, false, false, $sequence);
			}
			else 
			{
				$result = $this->getQuery()->insert($this->data, false, false, $sequence);
			}
			if( $result && ($insertId = $this->getQuery()->getLastInsID($sequence)) ) 
			{
				foreach( (array) $pk as $key ) 
				{
					if( !isset($this->data[$key]) || "" == $this->data[$key] ) 
					{
						$this->data[$key] = $insertId;
					}
				}
			}
			if( isset($relation) ) 
			{
				foreach( $relation as $name => $val ) 
				{
					$method = Loader::parseName($name, 1, false);
					$this->$method()->save($val);
				}
			}
			$this->isUpdate = true;
			$this->trigger("after_insert", $this);
		}
		$this->trigger("after_write", $this);
		$this->origin = $this->data;
		return $result;
	}
	protected function checkAllowField($auto = array( )) 
	{
		if( true === $this->field ) 
		{
			$this->field = $this->getQuery()->getTableInfo("", "fields");
			$field = $this->field;
		}
		else 
		{
			if( !empty($this->field) ) 
			{
				$field = array_merge($this->field, $auto);
				if( $this->autoWriteTimestamp ) 
				{
					array_push($field, $this->createTime, $this->updateTime);
				}
			}
			else 
			{
				if( !empty($this->except) ) 
				{
					$fields = $this->getQuery()->getTableInfo("", "fields");
					$field = array_diff($fields, (array) $this->except);
					$this->field = $field;
				}
				else 
				{
					$field = array( );
				}
			}
		}
		if( $this->disuse ) 
		{
			$field = array_diff($field, (array) $this->disuse);
		}
		return $field;
	}
	protected function autoRelationUpdate($relation) 
	{
		foreach( $relation as $name => $val ) 
		{
			if( $val instanceof Model ) 
			{
				$val->save();
			}
			else 
			{
				unset($this->data[$name]);
				$model = $this->getAttr($name);
				if( $model instanceof Model ) 
				{
					$model->save($val);
				}
			}
		}
	}
	public function getChangedData() 
	{
		if( $this->force ) 
		{
			$data = $this->data;
		}
		else 
		{
			$data = array_udiff_assoc($this->data, $this->origin, function($a, $b) 
			{
				if( (empty($a) || empty($b)) && $a !== $b ) 
				{
					return 1;
				}
				return (is_object($a) || $a != $b ? 1 : 0);
			}
			);
		}
		if( !empty($this->readonly) ) 
		{
			foreach( $this->readonly as $key => $field ) 
			{
				if( isset($data[$field]) ) 
				{
					unset($data[$field]);
				}
			}
		}
		return $data;
	}
	public function setInc($field, $step = 1, $lazyTime = 0) 
	{
		$where = $this->getWhere();
		$result = $this->getQuery()->where($where)->setInc($field, $step, $lazyTime);
		if( true !== $result ) 
		{
			$this->data[$field] += $step;
		}
		return $result;
	}
	public function setDec($field, $step = 1, $lazyTime = 0) 
	{
		$where = $this->getWhere();
		$result = $this->getQuery()->where($where)->setDec($field, $step, $lazyTime);
		if( true !== $result ) 
		{
			$this->data[$field] -= $step;
		}
		return $result;
	}
	protected function getWhere() 
	{
		$pk = $this->getPk();
		if( is_string($pk) && isset($this->data[$pk]) ) 
		{
			$where = array( $pk => $this->data[$pk] );
		}
		else 
		{
			if( !empty($this->updateWhere) ) 
			{
				$where = $this->updateWhere;
			}
			else 
			{
				$where = null;
			}
		}
		return $where;
	}
	public function saveAll($dataSet, $replace = true) 
	{
		if( $this->validate ) 
		{
			$validate = $this->validate;
			foreach( $dataSet as $data ) 
			{
				if( !$this->validateData($data, $validate) ) 
				{
					return false;
				}
			}
		}
		$result = array( );
		$db = $this->getQuery();
		$db->startTrans();
		try 
		{
			$pk = $this->getPk();
			if( is_string($pk) && $replace ) 
			{
				$auto = true;
			}
			foreach( $dataSet as $key => $data ) 
			{
				if( $this->isUpdate || !empty($auto) && isset($data[$pk]) ) 
				{
					$result[$key] = self::update($data, array( ), $this->field);
				}
				else 
				{
					$result[$key] = self::create($data, $this->field);
				}
			}
			$db->commit();
			return $this->toCollection($result);
		}
		catch( \Exception $e ) 
		{
			$db->rollback();
			throw $e;
		}
	}
	public function allowField($field) 
	{
		if( is_string($field) ) 
		{
			$field = explode(",", $field);
		}
		$this->field = $field;
		return $this;
	}
	public function except($field) 
	{
		if( is_string($field) ) 
		{
			$field = explode(",", $field);
		}
		$this->except = $field;
		return $this;
	}
	public function readonly($field) 
	{
		if( is_string($field) ) 
		{
			$field = explode(",", $field);
		}
		$this->readonly = $field;
		return $this;
	}
	public function isUpdate($update = true, $where = NULL) 
	{
		$this->isUpdate = $update;
		if( !empty($where) ) 
		{
			$this->updateWhere = $where;
		}
		return $this;
	}
	protected function autoCompleteData($auto = array( )) 
	{
		foreach( $auto as $field => $value ) 
		{
			if( is_integer($field) ) 
			{
				$field = $value;
				$value = null;
			}
			if( !isset($this->data[$field]) ) 
			{
				$default = null;
			}
			else 
			{
				$default = $this->data[$field];
			}
			$this->setAttr($field, (!is_null($value) ? $value : $default));
		}
	}
	public function delete() 
	{
		if( false === $this->trigger("before_delete", $this) ) 
		{
			return false;
		}
		$where = $this->getWhere();
		$result = $this->getQuery()->where($where)->delete();
		if( !empty($this->relationWrite) ) 
		{
			foreach( $this->relationWrite as $key => $name ) 
			{
				$name = (is_numeric($key) ? $name : $key);
				$model = $this->getAttr($name);
				if( $model instanceof Model ) 
				{
					$model->delete();
				}
			}
		}
		$this->trigger("after_delete", $this);
		$this->origin = array( );
		return $result;
	}
	public function auto($fields) 
	{
		$this->auto = $fields;
		return $this;
	}
	public function validate($rule = true, $msg = array( ), $batch = false) 
	{
		if( is_array($rule) ) 
		{
			$this->validate = array( "rule" => $rule, "msg" => $msg );
		}
		else 
		{
			$this->validate = (true === $rule ? $this->name : $rule);
		}
		$this->batchValidate = $batch;
		return $this;
	}
	public function validateFailException($fail = true) 
	{
		$this->failException = $fail;
		return $this;
	}
	protected function validateData($data, $rule = NULL, $batch = NULL) 
	{
		$info = (is_null($rule) ? $this->validate : $rule);
		if( !empty($info) ) 
		{
			if( is_array($info) ) 
			{
				$validate = Loader::validate();
				$validate->rule($info["rule"]);
				$validate->message($info["msg"]);
			}
			else 
			{
				$name = (is_string($info) ? $info : $this->name);
				if( strpos($name, ".") ) 
				{
					list($name, $scene) = explode(".", $name);
				}
				$validate = Loader::validate($name);
				if( !empty($scene) ) 
				{
					$validate->scene($scene);
				}
			}
			$batch = (is_null($batch) ? $this->batchValidate : $batch);
			if( !$validate->batch($batch)->check($data) ) 
			{
				$this->error = $validate->getError();
				if( $this->failException ) 
				{
					throw new exception\ValidateException($this->error);
				}
				return false;
			}
			$this->validate = null;
		}
		return true;
	}
	public function getError() 
	{
		return $this->error;
	}
	public static function event($event, $callback, $override = false) 
	{
		$class = get_called_class();
		if( $override ) 
		{
			self::$event[$class][$event] = array( );
		}
		self::$event[$class][$event][] = $callback;
	}
	protected function trigger($event, &$params) 
	{
		if( isset(self::$event[$this->class][$event]) ) 
		{
			foreach( self::$event[$this->class][$event] as $callback ) 
			{
				if( is_callable($callback) ) 
				{
					$result = call_user_func_array($callback, array( $params ));
					if( false === $result ) 
					{
						return false;
					}
				}
			}
		}
		return true;
	}
	public static function create($data = array( ), $field = NULL) 
	{
		$model = new static();
		if( !empty($field) ) 
		{
			$model->allowField($field);
		}
		$model->isUpdate(false)->save($data, array( ));
		return $model;
	}
	public static function update($data = array( ), $where = array( ), $field = NULL) 
	{
		$model = new static();
		if( !empty($field) ) 
		{
			$model->allowField($field);
		}
		$result = $model->isUpdate(true)->save($data, $where);
		return $model;
	}
	public static function get($data, $with = array( ), $cache = false) 
	{
		if( is_null($data) ) 
		{
			return NULL;
		}
		if( true === $with || is_int($with) ) 
		{
			$cache = $with;
			$with = array( );
		}
		$query = static::parseQuery($data, $with, $cache);
		return $query->find($data);
	}
	public static function all($data = NULL, $with = array( ), $cache = false) 
	{
		if( true === $with || is_int($with) ) 
		{
			$cache = $with;
			$with = array( );
		}
		$query = static::parseQuery($data, $with, $cache);
		return $query->select($data);
	}
	protected static function parseQuery(&$data, $with, $cache) 
	{
		$result = self::with($with)->cache($cache);
		if( is_array($data) && key($data) !== 0 ) 
		{
			$result = $result->where($data);
			$data = null;
		}
		else 
		{
			if( $data instanceof \Closure ) 
			{
				call_user_func_array($data, array( $result ));
				$data = null;
			}
			else 
			{
				if( $data instanceof db\Query ) 
				{
					$result = $data->with($with)->cache($cache);
					$data = null;
				}
			}
		}
		return $result;
	}
	public static function destroy($data) 
	{
		$model = new static();
		$query = $model->db();
		if( empty($data) && 0 !== $data ) 
		{
			return 0;
		}
		if( is_array($data) && key($data) !== 0 ) 
		{
			$query->where($data);
			$data = null;
		}
		else 
		{
			if( $data instanceof \Closure ) 
			{
				call_user_func_array($data, array( $query ));
				$data = null;
			}
		}
		$resultSet = $query->select($data);
		$count = 0;
		if( $resultSet ) 
		{
			foreach( $resultSet as $data ) 
			{
				$result = $data->delete();
				$count += $result;
			}
		}
		return $count;
	}
	public static function scope($name) 
	{
		$model = new static();
		$query = $model->db();
		$params = func_get_args();
		array_shift($params);
		array_unshift($params, $query);
		if( $name instanceof \Closure ) 
		{
			call_user_func_array($name, $params);
		}
		else 
		{
			if( is_string($name) ) 
			{
				$name = explode(",", $name);
			}
		}
		if( is_array($name) ) 
		{
			foreach( $name as $scope ) 
			{
				$method = "scope" . trim($scope);
				if( method_exists($model, $method) ) 
				{
					call_user_func_array(array( $model, $method ), $params);
				}
			}
		}
		return $query;
	}
	public static function useGlobalScope($use) 
	{
		$model = new static();
		return $model->db($use);
	}
	public static function has($relation, $operator = ">=", $count = 1, $id = "*") 
	{
		$relation = (new static())->$relation();
		if( is_array($operator) || $operator instanceof \Closure ) 
		{
			return $relation->hasWhere($operator);
		}
		return $relation->has($operator, $count, $id);
	}
	public static function hasWhere($relation, $where = array( ), $fields = NULL) 
	{
		return (new static())->$relation()->hasWhere($where, $fields);
	}
	protected function parseModel($model) 
	{
		if( false === strpos($model, "\\") ) 
		{
			$path = explode("\\", get_called_class());
			array_pop($path);
			array_push($path, Loader::parseName($model, 1));
			$model = implode("\\", $path);
		}
		return $model;
	}
	public function relationQuery($relations) 
	{
		if( is_string($relations) ) 
		{
			$relations = explode(",", $relations);
		}
		foreach( $relations as $key => $relation ) 
		{
			$subRelation = "";
			$closure = null;
			if( $relation instanceof \Closure ) 
			{
				$closure = $relation;
				$relation = $key;
			}
			if( is_array($relation) ) 
			{
				$subRelation = $relation;
				$relation = $key;
			}
			else 
			{
				if( strpos($relation, ".") ) 
				{
					list($relation, $subRelation) = explode(".", $relation, 2);
				}
			}
			$method = Loader::parseName($relation, 1, false);
			$this->data[$relation] = $this->$method()->getRelation($subRelation, $closure);
		}
		return $this;
	}
	public function eagerlyResultSet(&$resultSet, $relation) 
	{
		$relations = (is_string($relation) ? explode(",", $relation) : $relation);
		foreach( $relations as $key => $relation ) 
		{
			$subRelation = "";
			$closure = false;
			if( $relation instanceof \Closure ) 
			{
				$closure = $relation;
				$relation = $key;
			}
			if( is_array($relation) ) 
			{
				$subRelation = $relation;
				$relation = $key;
			}
			else 
			{
				if( strpos($relation, ".") ) 
				{
					list($relation, $subRelation) = explode(".", $relation, 2);
				}
			}
			$relation = Loader::parseName($relation, 1, false);
			$this->$relation()->eagerlyResultSet($resultSet, $relation, $subRelation, $closure);
		}
	}
	public function eagerlyResult(&$result, $relation) 
	{
		$relations = (is_string($relation) ? explode(",", $relation) : $relation);
		foreach( $relations as $key => $relation ) 
		{
			$subRelation = "";
			$closure = false;
			if( $relation instanceof \Closure ) 
			{
				$closure = $relation;
				$relation = $key;
			}
			if( is_array($relation) ) 
			{
				$subRelation = $relation;
				$relation = $key;
			}
			else 
			{
				if( strpos($relation, ".") ) 
				{
					list($relation, $subRelation) = explode(".", $relation, 2);
				}
			}
			$relation = Loader::parseName($relation, 1, false);
			$this->$relation()->eagerlyResult($result, $relation, $subRelation, $closure);
		}
	}
	public function relationCount(&$result, $relation) 
	{
		$relations = (is_string($relation) ? explode(",", $relation) : $relation);
		foreach( $relations as $key => $relation ) 
		{
			$closure = false;
			if( $relation instanceof \Closure ) 
			{
				$closure = $relation;
				$relation = $key;
			}
			else 
			{
				if( is_string($key) ) 
				{
					$name = $relation;
					$relation = $key;
				}
			}
			$relation = Loader::parseName($relation, 1, false);
			$count = $this->$relation()->relationCount($result, $closure);
			if( !isset($name) ) 
			{
				$name = Loader::parseName($relation) . "_count";
			}
			$result->setAttr($name, $count);
		}
	}
	protected function getForeignKey($name) 
	{
		if( strpos($name, "\\") ) 
		{
			$name = basename(str_replace("\\", "/", $name));
		}
		return Loader::parseName($name) . "_id";
	}
	public function hasOne($model, $foreignKey = "", $localKey = "", $alias = array( ), $joinType = "INNER") 
	{
		$model = $this->parseModel($model);
		$localKey = ($localKey ?: $this->getPk());
		$foreignKey = ($foreignKey ?: $this->getForeignKey($this->name));
		return new model\relation\HasOne($this, $model, $foreignKey, $localKey, $joinType);
	}
	public function belongsTo($model, $foreignKey = "", $localKey = "", $alias = array( ), $joinType = "INNER") 
	{
		$model = $this->parseModel($model);
		$foreignKey = ($foreignKey ?: $this->getForeignKey($model));
		$localKey = ($localKey ?: (new $model())->getPk());
		$trace = debug_backtrace(false, 2);
		$relation = Loader::parseName($trace[1]["function"]);
		return new model\relation\BelongsTo($this, $model, $foreignKey, $localKey, $joinType, $relation);
	}
	public function hasMany($model, $foreignKey = "", $localKey = "") 
	{
		$model = $this->parseModel($model);
		$localKey = ($localKey ?: $this->getPk());
		$foreignKey = ($foreignKey ?: $this->getForeignKey($this->name));
		return new model\relation\HasMany($this, $model, $foreignKey, $localKey);
	}
	public function hasManyThrough($model, $through, $foreignKey = "", $throughKey = "", $localKey = "") 
	{
		$model = $this->parseModel($model);
		$through = $this->parseModel($through);
		$localKey = ($localKey ?: $this->getPk());
		$foreignKey = ($foreignKey ?: $this->getForeignKey($this->name));
		$throughKey = ($throughKey ?: $this->getForeignKey($through));
		return new model\relation\HasManyThrough($this, $model, $through, $foreignKey, $throughKey, $localKey);
	}
	public function belongsToMany($model, $table = "", $foreignKey = "", $localKey = "") 
	{
		$model = $this->parseModel($model);
		$name = Loader::parseName(basename(str_replace("\\", "/", $model)));
		$table = ($table ?: Loader::parseName($this->name) . "_" . $name);
		$foreignKey = ($foreignKey ?: $name . "_id");
		$localKey = ($localKey ?: $this->getForeignKey($this->name));
		return new model\relation\BelongsToMany($this, $model, $table, $foreignKey, $localKey);
	}
	public function morphMany($model, $morph = NULL, $type = "") 
	{
		$model = $this->parseModel($model);
		if( is_null($morph) ) 
		{
			$trace = debug_backtrace(false, 2);
			$morph = Loader::parseName($trace[1]["function"]);
		}
		$type = ($type ?: get_class($this));
		if( is_array($morph) ) 
		{
			list($morphType, $foreignKey) = $morph;
		}
		else 
		{
			$morphType = $morph . "_type";
			$foreignKey = $morph . "_id";
		}
		return new model\relation\MorphMany($this, $model, $foreignKey, $morphType, $type);
	}
	public function morphOne($model, $morph = NULL, $type = "") 
	{
		$model = $this->parseModel($model);
		if( is_null($morph) ) 
		{
			$trace = debug_backtrace(false, 2);
			$morph = Loader::parseName($trace[1]["function"]);
		}
		$type = ($type ?: get_class($this));
		if( is_array($morph) ) 
		{
			list($morphType, $foreignKey) = $morph;
		}
		else 
		{
			$morphType = $morph . "_type";
			$foreignKey = $morph . "_id";
		}
		return new model\relation\MorphOne($this, $model, $foreignKey, $morphType, $type);
	}
	public function morphTo($morph = NULL, $alias = array( )) 
	{
		$trace = debug_backtrace(false, 2);
		$relation = Loader::parseName($trace[1]["function"]);
		if( is_null($morph) ) 
		{
			$morph = $relation;
		}
		if( is_array($morph) ) 
		{
			list($morphType, $foreignKey) = $morph;
		}
		else 
		{
			$morphType = $morph . "_type";
			$foreignKey = $morph . "_id";
		}
		return new model\relation\MorphTo($this, $morphType, $foreignKey, $alias, $relation);
	}
	public function __call($method, $args) 
	{
		$query = $this->db(true, false);
		if( method_exists($this, "scope" . $method) ) 
		{
			$method = "scope" . $method;
			array_unshift($args, $query);
			call_user_func_array(array( $this, $method ), $args);
			return $this;
		}
		return call_user_func_array(array( $query, $method ), $args);
	}
	public static function __callStatic($method, $args) 
	{
		$model = new static();
		$query = $model->db();
		if( method_exists($model, "scope" . $method) ) 
		{
			$method = "scope" . $method;
			array_unshift($args, $query);
			call_user_func_array(array( $model, $method ), $args);
			return $query;
		}
		return call_user_func_array(array( $query, $method ), $args);
	}
	public function __set($name, $value) 
	{
		$this->setAttr($name, $value);
	}
	public function __get($name) 
	{
		return $this->getAttr($name);
	}
	public function __isset($name) 
	{
		try 
		{
			if( array_key_exists($name, $this->data) || array_key_exists($name, $this->relation) ) 
			{
				return true;
			}
			$this->getAttr($name);
			return true;
		}
		catch( \InvalidArgumentException $e ) 
		{
			return false;
		}
	}
	public function __unset($name) 
	{
		unset($this->data[$name]);
		unset($this->relation[$name]);
	}
	public function __toString() 
	{
		return $this->toJson();
	}
	public function jsonSerialize() 
	{
		return $this->toArray();
	}
	public function offsetSet($name, $value) 
	{
		$this->setAttr($name, $value);
	}
	public function offsetExists($name) 
	{
		return $this->__isset($name);
	}
	public function offsetUnset($name) 
	{
		$this->__unset($name);
	}
	public function offsetGet($name) 
	{
		return $this->getAttr($name);
	}
	public function __wakeup() 
	{
		$this->initialize();
	}
	protected static function beforeInsert($callback, $override = false) 
	{
		self::event("before_insert", $callback, $override);
	}
	protected static function afterInsert($callback, $override = false) 
	{
		self::event("after_insert", $callback, $override);
	}
	protected static function beforeUpdate($callback, $override = false) 
	{
		self::event("before_update", $callback, $override);
	}
	protected static function afterUpdate($callback, $override = false) 
	{
		self::event("after_update", $callback, $override);
	}
	protected static function beforeWrite($callback, $override = false) 
	{
		self::event("before_write", $callback, $override);
	}
	protected static function afterWrite($callback, $override = false) 
	{
		self::event("after_write", $callback, $override);
	}
	protected static function beforeDelete($callback, $override = false) 
	{
		self::event("before_delete", $callback, $override);
	}
	protected static function afterDelete($callback, $override = false) 
	{
		self::event("after_delete", $callback, $override);
	}
}
?>