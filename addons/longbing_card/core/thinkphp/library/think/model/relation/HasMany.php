<?php  namespace think\model\relation;
class HasMany extends \think\model\Relation 
{
	public function __construct(\think\Model $parent, $model, $foreignKey, $localKey) 
	{
		$this->parent = $parent;
		$this->model = $model;
		$this->foreignKey = $foreignKey;
		$this->localKey = $localKey;
		$this->query = (new $model())->db();
	}
	public function getRelation($subRelation = "", $closure = NULL) 
	{
		if( $closure ) 
		{
			call_user_func_array($closure, array( $this->query ));
		}
		$list = $this->relation($subRelation)->select();
		$parent = clone $this->parent;
		foreach( $list as &$model ) 
		{
			$model->setParent($parent);
		}
		return $list;
	}
	public function eagerlyResultSet(&$resultSet, $relation, $subRelation, $closure) 
	{
		$localKey = $this->localKey;
		$range = array( );
		foreach( $resultSet as $result ) 
		{
			if( isset($result->$localKey) ) 
			{
				$range[] = $result->$localKey;
			}
		}
		if( !empty($range) ) 
		{
			$data = $this->eagerlyOneToMany($this->query, array( $this->foreignKey => array( "in", $range ) ), $relation, $subRelation, $closure);
			$attr = \think\Loader::parseName($relation);
			foreach( $resultSet as $result ) 
			{
				if( !isset($data[$result->$localKey]) ) 
				{
					$data[$result->$localKey] = array( );
				}
				foreach( $data[$result->$localKey] as &$relationModel ) 
				{
					$relationModel->setParent(clone $result);
				}
				$result->setRelation($attr, $this->resultSetBuild($data[$result->$localKey]));
			}
		}
	}
	public function eagerlyResult(&$result, $relation, $subRelation, $closure) 
	{
		$localKey = $this->localKey;
		if( isset($result->$localKey) ) 
		{
			$data = $this->eagerlyOneToMany($this->query, array( $this->foreignKey => $result->$localKey ), $relation, $subRelation, $closure);
			if( !isset($data[$result->$localKey]) ) 
			{
				$data[$result->$localKey] = array( );
			}
			foreach( $data[$result->$localKey] as &$relationModel ) 
			{
				$relationModel->setParent(clone $result);
			}
			$result->setRelation(\think\Loader::parseName($relation), $this->resultSetBuild($data[$result->$localKey]));
		}
	}
	public function relationCount($result, $closure) 
	{
		$localKey = $this->localKey;
		$count = 0;
		if( isset($result->$localKey) ) 
		{
			if( $closure ) 
			{
				call_user_func_array($closure, array( $this->query ));
			}
			$count = $this->query->where($this->foreignKey, $result->$localKey)->count();
		}
		return $count;
	}
	public function getRelationCountQuery($closure) 
	{
		if( $closure ) 
		{
			call_user_func_array($closure, array( $this->query ));
		}
		$localKey = ($this->localKey ?: $this->parent->getPk());
		return $this->query->whereExp($this->foreignKey, "=" . $this->parent->getTable() . "." . $localKey)->fetchSql()->count();
	}
	protected function eagerlyOneToMany($model, $where, $relation, $subRelation = "", $closure = false) 
	{
		$foreignKey = $this->foreignKey;
		if( $closure ) 
		{
			call_user_func_array($closure, array( $model ));
		}
		$list = $model->removeWhereField($foreignKey)->where($where)->with($subRelation)->select();
		$data = array( );
		foreach( $list as $set ) 
		{
			$data[$set->$foreignKey][] = $set;
		}
		return $data;
	}
	public function save($data) 
	{
		if( $data instanceof \think\Model ) 
		{
			$data = $data->getData();
		}
		$model = new $this->model();
		$data[$this->foreignKey] = $this->parent->
		{
			$this->localKey}
		;
		return ($model->save($data) ? $model : false);
	}
	public function saveAll(array $dataSet) 
	{
		$result = false;
		foreach( $dataSet as $key => $data ) 
		{
			$result = $this->save($data);
		}
		return $result;
	}
	public function has($operator = ">=", $count = 1, $id = "*", $joinType = "INNER") 
	{
		$table = $this->query->getTable();
		$model = basename(str_replace("\\", "/", get_class($this->parent)));
		$relation = basename(str_replace("\\", "/", $this->model));
		return $this->parent->db()->alias($model)->field($model . ".*")->join(array( $table => $relation ), $model . "." . $this->localKey . "=" . $relation . "." . $this->foreignKey, $joinType)->group($relation . "." . $this->foreignKey)->having("count(" . $id . ")" . $operator . $count);
	}
	public function hasWhere($where = array( ), $fields = NULL) 
	{
		$table = $this->query->getTable();
		$model = basename(str_replace("\\", "/", get_class($this->parent)));
		$relation = basename(str_replace("\\", "/", $this->model));
		if( is_array($where) ) 
		{
			foreach( $where as $key => $val ) 
			{
				if( false === strpos($key, ".") ) 
				{
					$where[$relation . "." . $key] = $val;
					unset($where[$key]);
				}
			}
		}
		$fields = $this->getRelationQueryFields($fields, $model);
		return $this->parent->db()->alias($model)->field($fields)->group($model . "." . $this->localKey)->join(array( $table => $relation ), $model . "." . $this->localKey . "=" . $relation . "." . $this->foreignKey)->where($where);
	}
	protected function baseQuery() 
	{
		if( empty($this->baseQuery) ) 
		{
			if( isset($this->parent->
			{
				$this->localKey}
			) ) 
			{
				$this->query->where($this->foreignKey, $this->parent->
				{
					$this->localKey}
				);
			}
			$this->baseQuery = true;
		}
	}
}
?>