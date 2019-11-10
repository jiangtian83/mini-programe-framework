<?php  namespace think\model\relation;
class BelongsToMany extends \think\model\Relation 
{
	protected $middle = NULL;
	protected $pivotName = NULL;
	protected $pivot = NULL;
	public function __construct(\think\Model $parent, $model, $table, $foreignKey, $localKey) 
	{
		$this->parent = $parent;
		$this->model = $model;
		$this->foreignKey = $foreignKey;
		$this->localKey = $localKey;
		if( false !== strpos($table, "\\") ) 
		{
			$this->pivotName = $table;
			$this->middle = basename(str_replace("\\", "/", $table));
		}
		else 
		{
			$this->middle = $table;
		}
		$this->query = (new $model())->db();
		$this->pivot = $this->newPivot();
		if( "think\\model\\Pivot" == get_class($this->pivot) ) 
		{
			$this->pivot->name($this->middle);
		}
	}
	public function pivot($pivot) 
	{
		$this->pivotName = $pivot;
		return $this;
	}
	protected function getUpdateWhere($data) 
	{
		return array( $this->localKey => $data[$this->localKey], $this->foreignKey => $data[$this->foreignKey] );
	}
	protected function newPivot($data = array( ), $isUpdate = false) 
	{
		$class = ($this->pivotName ?: "\\think\\model\\Pivot");
		$pivot = new $class($data, $this->parent, $this->middle);
		if( $pivot instanceof \think\model\Pivot ) 
		{
			return ($isUpdate ? $pivot->isUpdate(true, $this->getUpdateWhere($data)) : $pivot);
		}
		throw new \think\Exception("pivot model must extends: \\think\\model\\Pivot");
	}
	protected function hydratePivot($models) 
	{
		foreach( $models as $model ) 
		{
			$pivot = array( );
			foreach( $model->getData() as $key => $val ) 
			{
				if( strpos($key, "__") ) 
				{
					list($name, $attr) = explode("__", $key, 2);
					if( "pivot" == $name ) 
					{
						$pivot[$attr] = $val;
						unset($model->$key);
					}
				}
			}
			$model->setRelation("pivot", $this->newPivot($pivot, true));
		}
	}
	protected function buildQuery() 
	{
		$foreignKey = $this->foreignKey;
		$localKey = $this->localKey;
		$pk = $this->parent->getPk();
		$condition["pivot." . $localKey] = $this->parent->$pk;
		return $this->belongsToManyQuery($foreignKey, $localKey, $condition);
	}
	public function getRelation($subRelation = "", $closure = NULL) 
	{
		if( $closure ) 
		{
			call_user_func_array($closure, array( $this->query ));
		}
		$result = $this->buildQuery()->relation($subRelation)->select();
		$this->hydratePivot($result);
		return $result;
	}
	public function select($data = NULL) 
	{
		$result = $this->buildQuery()->select($data);
		$this->hydratePivot($result);
		return $result;
	}
	public function paginate($listRows = NULL, $simple = false, $config = array( )) 
	{
		$result = $this->buildQuery()->paginate($listRows, $simple, $config);
		$this->hydratePivot($result);
		return $result;
	}
	public function find($data = NULL) 
	{
		$result = $this->buildQuery()->find($data);
		if( $result ) 
		{
			$this->hydratePivot(array( $result ));
		}
		return $result;
	}
	public function selectOrFail($data = NULL) 
	{
		return $this->failException(true)->select($data);
	}
	public function findOrFail($data = NULL) 
	{
		return $this->failException(true)->find($data);
	}
	public function has($operator = ">=", $count = 1, $id = "*", $joinType = "INNER") 
	{
		return $this->parent;
	}
	public function hasWhere($where = array( ), $fields = NULL) 
	{
		throw new \think\Exception("relation not support: hasWhere");
	}
	public function wherePivot($field, $op = NULL, $condition = NULL) 
	{
		$field = "pivot." . $field;
		$this->query->where($field, $op, $condition);
		return $this;
	}
	public function eagerlyResultSet(&$resultSet, $relation, $subRelation, $closure) 
	{
		$localKey = $this->localKey;
		$foreignKey = $this->foreignKey;
		$pk = $resultSet[0]->getPk();
		$range = array( );
		foreach( $resultSet as $result ) 
		{
			if( isset($result->$pk) ) 
			{
				$range[] = $result->$pk;
			}
		}
		if( !empty($range) ) 
		{
			$data = $this->eagerlyManyToMany(array( "pivot." . $localKey => array( "in", $range ) ), $relation, $subRelation);
			$attr = \think\Loader::parseName($relation);
			foreach( $resultSet as $result ) 
			{
				if( !isset($data[$result->$pk]) ) 
				{
					$data[$result->$pk] = array( );
				}
				$result->setRelation($attr, $this->resultSetBuild($data[$result->$pk]));
			}
		}
	}
	public function eagerlyResult(&$result, $relation, $subRelation, $closure) 
	{
		$pk = $result->getPk();
		if( isset($result->$pk) ) 
		{
			$pk = $result->$pk;
			$data = $this->eagerlyManyToMany(array( "pivot." . $this->localKey => $pk ), $relation, $subRelation);
			if( !isset($data[$pk]) ) 
			{
				$data[$pk] = array( );
			}
			$result->setRelation(\think\Loader::parseName($relation), $this->resultSetBuild($data[$pk]));
		}
	}
	public function relationCount($result, $closure) 
	{
		$pk = $result->getPk();
		$count = 0;
		if( isset($result->$pk) ) 
		{
			$pk = $result->$pk;
			$count = $this->belongsToManyQuery($this->foreignKey, $this->localKey, array( "pivot." . $this->localKey => $pk ))->count();
		}
		return $count;
	}
	public function getRelationCountQuery($closure) 
	{
		return $this->belongsToManyQuery($this->foreignKey, $this->localKey, array( "pivot." . $this->localKey => array( "exp", \think\Db::raw("=" . $this->parent->getTable() . "." . $this->parent->getPk()) ) ))->fetchSql()->count();
	}
	protected function eagerlyManyToMany($where, $relation, $subRelation = "") 
	{
		$list = $this->belongsToManyQuery($this->foreignKey, $this->localKey, $where)->with($subRelation)->select();
		$data = array( );
		foreach( $list as $set ) 
		{
			$pivot = array( );
			foreach( $set->getData() as $key => $val ) 
			{
				if( strpos($key, "__") ) 
				{
					list($name, $attr) = explode("__", $key, 2);
					if( "pivot" == $name ) 
					{
						$pivot[$attr] = $val;
						unset($set->$key);
					}
				}
			}
			$set->setRelation("pivot", $this->newPivot($pivot, true));
			$data[$pivot[$this->localKey]][] = $set;
		}
		return $data;
	}
	protected function belongsToManyQuery($foreignKey, $localKey, $condition = array( )) 
	{
		$tableName = $this->query->getTable();
		$table = $this->pivot->getTable();
		$fields = $this->getQueryFields($tableName);
		$query = $this->query->field($fields)->field(true, false, $table, "pivot", "pivot__");
		if( empty($this->baseQuery) ) 
		{
			$relationFk = $this->query->getPk();
			$query->join(array( $table => "pivot" ), "pivot." . $foreignKey . "=" . $tableName . "." . $relationFk)->where($condition);
		}
		return $query;
	}
	public function save($data, array $pivot = array( )) 
	{
		return $this->attach($data, $pivot);
	}
	public function saveAll(array $dataSet, array $pivot = array( ), $samePivot = false) 
	{
		$result = false;
		foreach( $dataSet as $key => $data ) 
		{
			if( !$samePivot ) 
			{
				$pivotData = (isset($pivot[$key]) ? $pivot[$key] : array( ));
			}
			else 
			{
				$pivotData = $pivot;
			}
			$result = $this->attach($data, $pivotData);
		}
		return $result;
	}
	public function attach($data, $pivot = array( )) 
	{
		if( is_array($data) ) 
		{
			if( key($data) === 0 ) 
			{
				$id = $data;
			}
			else 
			{
				$model = new $this->model();
				$model->save($data);
				$id = $model->getLastInsID();
			}
		}
		else 
		{
			if( is_numeric($data) || is_string($data) ) 
			{
				$id = $data;
			}
			else 
			{
				if( $data instanceof \think\Model ) 
				{
					$relationFk = $data->getPk();
					$id = $data->$relationFk;
				}
			}
		}
		if( $id ) 
		{
			$pk = $this->parent->getPk();
			$pivot[$this->localKey] = $this->parent->$pk;
			$ids = (array) $id;
			foreach( $ids as $id ) 
			{
				$pivot[$this->foreignKey] = $id;
				$this->pivot->insert($pivot, true);
				$result[] = $this->newPivot($pivot, true);
			}
			if( count($result) == 1 ) 
			{
				$result = $result[0];
			}
			return $result;
		}
		else 
		{
			throw new \think\Exception("miss relation data");
		}
	}
	public function detach($data = NULL, $relationDel = false) 
	{
		if( is_array($data) ) 
		{
			$id = $data;
		}
		else 
		{
			if( is_numeric($data) || is_string($data) ) 
			{
				$id = $data;
			}
			else 
			{
				if( $data instanceof \think\Model ) 
				{
					$relationFk = $data->getPk();
					$id = $data->$relationFk;
				}
			}
		}
		$pk = $this->parent->getPk();
		$pivot[$this->localKey] = $this->parent->$pk;
		if( isset($id) ) 
		{
			$pivot[$this->foreignKey] = (is_array($id) ? array( "in", $id ) : $id);
		}
		$this->pivot->where($pivot)->delete();
		if( isset($id) && $relationDel ) 
		{
			$model = $this->model;
			$model::destroy($id);
		}
	}
	public function sync($ids, $detaching = true) 
	{
		$changes = array( "attached" => array( ), "detached" => array( ), "updated" => array( ) );
		$pk = $this->parent->getPk();
		$current = $this->pivot->where($this->localKey, $this->parent->$pk)->column($this->foreignKey);
		$records = array( );
		foreach( $ids as $key => $value ) 
		{
			if( !is_array($value) ) 
			{
				$records[$value] = array( );
			}
			else 
			{
				$records[$key] = $value;
			}
		}
		$detach = array_diff($current, array_keys($records));
		if( $detaching && 0 < count($detach) ) 
		{
			$this->detach($detach);
			$changes["detached"] = $detach;
		}
		foreach( $records as $id => $attributes ) 
		{
			if( !in_array($id, $current) ) 
			{
				$this->attach($id, $attributes);
				$changes["attached"][] = $id;
			}
			else 
			{
				if( 0 < count($attributes) && $this->attach($id, $attributes) ) 
				{
					$changes["updated"][] = $id;
				}
			}
		}
		return $changes;
	}
	protected function baseQuery() 
	{
		if( empty($this->baseQuery) && $this->parent->getData() ) 
		{
			$pk = $this->parent->getPk();
			$table = $this->pivot->getTable();
			$this->query->join(array( $table => "pivot" ), "pivot." . $this->foreignKey . "=" . $this->query->getTable() . "." . $this->query->getPk())->where("pivot." . $this->localKey, $this->parent->$pk);
			$this->baseQuery = true;
		}
	}
}
?>