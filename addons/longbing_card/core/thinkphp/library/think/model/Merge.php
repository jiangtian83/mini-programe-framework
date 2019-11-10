<?php  namespace think\model;
class Merge extends \think\Model 
{
	protected $relationModel = array( );
	protected $fk = "";
	protected $mapFields = array( );
	public function __construct($data = array( )) 
	{
		parent::__construct($data);
		if( empty($this->fk) ) 
		{
			$this->fk = strtolower($this->name) . "_id";
		}
	}
	public static function get($data = NULL, $with = array( ), $cache = false) 
	{
		$query = self::parseQuery($data, $with, $cache);
		$query = self::attachQuery($query);
		return $query->find($data);
	}
	protected static function attachQuery($query) 
	{
		$class = new static();
		$master = $class->name;
		$fields = self::getModelField($query, $master, "", $class->mapFields, $class->field);
		$query->alias($master)->field($fields);
		foreach( $class->relationModel as $key => $model ) 
		{
			$name = (is_int($key) ? $model : $key);
			$table = (is_int($key) ? $query->getTable($name) : $model);
			$query->join($table . " " . $name, $name . "." . $class->fk . "=" . $master . "." . $class->getPk());
			$fields = self::getModelField($query, $name, $table, $class->mapFields, $class->field);
			$query->field($fields);
		}
		return $query;
	}
	protected static function getModelField($query, $name, $table = "", $map = array( ), $fields = array( )) 
	{
		$fields = ($fields ?: $query->getTableInfo($table, "fields"));
		$array = array( );
		foreach( $fields as $field ) 
		{
			if( $key = array_search($name . "." . $field, $map) ) 
			{
				$array[] = $name . "." . $field . " AS " . $key;
			}
			else 
			{
				$array[] = $field;
			}
		}
		return $array;
	}
	public static function all($data = NULL, $with = array( ), $cache = false) 
	{
		$query = self::parseQuery($data, $with, $cache);
		$query = self::attachQuery($query);
		return $query->select($data);
	}
	protected function parseData($model, $data) 
	{
		$item = array( );
		foreach( $data as $key => $val ) 
		{
			if( $this->fk != $key && array_key_exists($key, $this->mapFields) ) 
			{
				list($name, $key) = explode(".", $this->mapFields[$key]);
				if( $model == $name ) 
				{
					$item[$key] = $val;
				}
			}
			else 
			{
				$item[$key] = $val;
			}
		}
		return $item;
	}
	public function save($data = array( ), $where = array( ), $sequence = NULL) 
	{
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
			}
		}
		$this->autoCompleteData($this->auto);
		if( $this->autoWriteTimestamp && $this->updateTime && !isset($this->data[$this->updateTime]) ) 
		{
			$this->setAttr($this->updateTime, null);
		}
		if( false === $this->trigger("before_write", $this) ) 
		{
			return false;
		}
		$db = $this->db();
		$db->startTrans();
		$pk = $this->getPk();
		try 
		{
			if( $this->isUpdate ) 
			{
				$this->autoCompleteData($this->update);
				if( false === $this->trigger("before_update", $this) ) 
				{
					return false;
				}
				if( empty($where) && !empty($this->updateWhere) ) 
				{
					$where = $this->updateWhere;
				}
				$data = $this->getChangedData();
				foreach( $this->data as $key => $val ) 
				{
					if( $this->isPk($key) ) 
					{
						$data[$key] = $val;
					}
				}
				$data = $this->parseData($this->name, $data);
				if( is_string($pk) && isset($data[$pk]) ) 
				{
					if( !isset($where[$pk]) ) 
					{
						unset($where);
						$where[$pk] = $data[$pk];
					}
					unset($data[$pk]);
				}
				$result = $db->strict(false)->where($where)->update($data);
				foreach( $this->relationModel as $key => $model ) 
				{
					$name = (is_int($key) ? $model : $key);
					$table = (is_int($key) ? $db->getTable($model) : $model);
					$data = $this->parseData($name, $data);
					if( \think\Db::table($table)->strict(false)->where($this->fk, $this->data[$this->getPk()])->update($data) ) 
					{
						$result = 1;
					}
				}
				$this->trigger("after_update", $this);
			}
			else 
			{
				$this->autoCompleteData($this->insert);
				if( $this->autoWriteTimestamp && $this->createTime && !isset($this->data[$this->createTime]) ) 
				{
					$this->setAttr($this->createTime, null);
				}
				if( false === $this->trigger("before_insert", $this) ) 
				{
					return false;
				}
				$data = $this->parseData($this->name, $this->data);
				$result = $db->name($this->name)->strict(false)->insert($data);
				if( $result ) 
				{
					$insertId = $db->getLastInsID($sequence);
					if( $insertId ) 
					{
						if( is_string($pk) ) 
						{
							$this->data[$pk] = $insertId;
						}
						$this->data[$this->fk] = $insertId;
					}
					$source = $this->data;
					if( $insertId && is_string($pk) && isset($source[$pk]) && $this->fk != $pk ) 
					{
						unset($source[$pk]);
					}
					foreach( $this->relationModel as $key => $model ) 
					{
						$name = (is_int($key) ? $model : $key);
						$table = (is_int($key) ? $db->getTable($model) : $model);
						$data = $this->parseData($name, $source);
						\think\Db::table($table)->strict(false)->insert($data);
					}
				}
				$this->isUpdate = true;
				$this->trigger("after_insert", $this);
			}
			$db->commit();
			$this->trigger("after_write", $this);
			$this->origin = $this->data;
			return $result;
		}
		catch( \Exception $e ) 
		{
			$db->rollback();
			throw $e;
		}
	}
	public function delete() 
	{
		if( false === $this->trigger("before_delete", $this) ) 
		{
			return false;
		}
		$db = $this->db();
		$db->startTrans();
		try 
		{
			$result = $db->delete($this->data);
			if( $result ) 
			{
				$pk = $this->data[$this->getPk()];
				foreach( $this->relationModel as $key => $model ) 
				{
					$table = (is_int($key) ? $db->getTable($model) : $model);
					$query = new \think\db\Query();
					$query->table($table)->where($this->fk, $pk)->delete();
				}
			}
			$this->trigger("after_delete", $this);
			$db->commit();
			return $result;
		}
		catch( \Exception $e ) 
		{
			$db->rollback();
			throw $e;
		}
	}
}
?>