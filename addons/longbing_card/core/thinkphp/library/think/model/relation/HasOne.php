<?php  namespace think\model\relation;
class HasOne extends OneToOne 
{
	public function __construct(\think\Model $parent, $model, $foreignKey, $localKey, $joinType = "INNER") 
	{
		$this->parent = $parent;
		$this->model = $model;
		$this->foreignKey = $foreignKey;
		$this->localKey = $localKey;
		$this->joinType = $joinType;
		$this->query = (new $model())->db();
	}
	public function getRelation($subRelation = "", $closure = NULL) 
	{
		$localKey = $this->localKey;
		if( $closure ) 
		{
			call_user_func_array($closure, array( $this->query ));
		}
		$relationModel = $this->query->removeWhereField($this->foreignKey)->where($this->foreignKey, $this->parent->$localKey)->relation($subRelation)->find();
		if( $relationModel ) 
		{
			$relationModel->setParent(clone $this->parent);
		}
		return $relationModel;
	}
	public function has() 
	{
		$table = $this->query->getTable();
		$model = basename(str_replace("\\", "/", get_class($this->parent)));
		$relation = basename(str_replace("\\", "/", $this->model));
		$localKey = $this->localKey;
		$foreignKey = $this->foreignKey;
		return $this->parent->db()->alias($model)->whereExists(function($query) use ($table, $model, $relation, $localKey, $foreignKey) 
		{
			$query->table(array( $table => $relation ))->field($relation . "." . $foreignKey)->whereExp($model . "." . $localKey, "=" . $relation . "." . $foreignKey);
		}
		);
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
		return $this->parent->db()->alias($model)->field($fields)->join(array( $table => $relation ), $model . "." . $this->localKey . "=" . $relation . "." . $this->foreignKey, $this->joinType)->where($where);
	}
	protected function eagerlySet(&$resultSet, $relation, $subRelation, $closure) 
	{
		$localKey = $this->localKey;
		$foreignKey = $this->foreignKey;
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
			$this->query->removeWhereField($foreignKey);
			$data = $this->eagerlyWhere($this->query, array( $foreignKey => array( "in", $range ) ), $foreignKey, $relation, $subRelation, $closure);
			$attr = \think\Loader::parseName($relation);
			foreach( $resultSet as $result ) 
			{
				if( !isset($data[$result->$localKey]) ) 
				{
					$relationModel = null;
				}
				else 
				{
					$relationModel = $data[$result->$localKey];
					$relationModel->setParent(clone $result);
					$relationModel->isUpdate(true);
				}
				if( !empty($this->bindAttr) ) 
				{
					$this->bindAttr($relationModel, $result, $this->bindAttr);
				}
				else 
				{
					$result->setRelation($attr, $relationModel);
				}
			}
		}
	}
	protected function eagerlyOne(&$result, $relation, $subRelation, $closure) 
	{
		$localKey = $this->localKey;
		$foreignKey = $this->foreignKey;
		$this->query->removeWhereField($foreignKey);
		$data = $this->eagerlyWhere($this->query, array( $foreignKey => $result->$localKey ), $foreignKey, $relation, $subRelation, $closure);
		if( !isset($data[$result->$localKey]) ) 
		{
			$relationModel = null;
		}
		else 
		{
			$relationModel = $data[$result->$localKey];
			$relationModel->setParent(clone $result);
			$relationModel->isUpdate(true);
		}
		if( !empty($this->bindAttr) ) 
		{
			$this->bindAttr($relationModel, $result, $this->bindAttr);
		}
		else 
		{
			$result->setRelation(\think\Loader::parseName($relation), $relationModel);
		}
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
				$this->query->where($this->foreignKey, "=", $this->parent->
				{
					$this->localKey}
				);
			}
			$this->baseQuery = true;
		}
	}
}
?>