<?php  namespace think\model\relation;
class BelongsTo extends OneToOne 
{
	public function __construct(\think\Model $parent, $model, $foreignKey, $localKey, $joinType = "INNER", $relation = NULL) 
	{
		$this->parent = $parent;
		$this->model = $model;
		$this->foreignKey = $foreignKey;
		$this->localKey = $localKey;
		$this->joinType = $joinType;
		$this->query = (new $model())->db();
		$this->relation = $relation;
	}
	public function getRelation($subRelation = "", $closure = NULL) 
	{
		$foreignKey = $this->foreignKey;
		if( $closure ) 
		{
			call_user_func_array($closure, array( $this->query ));
		}
		$relationModel = $this->query->removeWhereField($this->localKey)->where($this->localKey, $this->parent->$foreignKey)->relation($subRelation)->find();
		if( $relationModel ) 
		{
			$relationModel->setParent(clone $this->parent);
		}
		return $relationModel;
	}
	public function has($operator = ">=", $count = 1, $id = "*") 
	{
		return $this->parent;
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
		return $this->parent->db()->alias($model)->field($fields)->join(array( $table => $relation ), $model . "." . $this->foreignKey . "=" . $relation . "." . $this->localKey, $this->joinType)->where($where);
	}
	protected function eagerlySet(&$resultSet, $relation, $subRelation, $closure) 
	{
		$localKey = $this->localKey;
		$foreignKey = $this->foreignKey;
		$range = array( );
		foreach( $resultSet as $result ) 
		{
			if( isset($result->$foreignKey) ) 
			{
				$range[] = $result->$foreignKey;
			}
		}
		if( !empty($range) ) 
		{
			$this->query->removeWhereField($localKey);
			$data = $this->eagerlyWhere($this->query, array( $localKey => array( "in", $range ) ), $localKey, $relation, $subRelation, $closure);
			$attr = \think\Loader::parseName($relation);
			foreach( $resultSet as $result ) 
			{
				if( !isset($data[$result->$foreignKey]) ) 
				{
					$relationModel = null;
				}
				else 
				{
					$relationModel = $data[$result->$foreignKey];
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
		$this->query->removeWhereField($localKey);
		$data = $this->eagerlyWhere($this->query, array( $localKey => $result->$foreignKey ), $localKey, $relation, $subRelation, $closure);
		if( !isset($data[$result->$foreignKey]) ) 
		{
			$relationModel = null;
		}
		else 
		{
			$relationModel = $data[$result->$foreignKey];
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
	public function associate($model) 
	{
		$foreignKey = $this->foreignKey;
		$pk = $model->getPk();
		$this->parent->setAttr($foreignKey, $model->$pk);
		$this->parent->save();
		return $this->parent->setRelation($this->relation, $model);
	}
	public function dissociate() 
	{
		$foreignKey = $this->foreignKey;
		$this->parent->setAttr($foreignKey, null);
		$this->parent->save();
		return $this->parent->setRelation($this->relation, null);
	}
	protected function baseQuery() 
	{
		if( empty($this->baseQuery) ) 
		{
			if( isset($this->parent->
			{
				$this->foreignKey}
			) ) 
			{
				$this->query->where($this->localKey, "=", $this->parent->
				{
					$this->foreignKey}
				);
			}
			$this->baseQuery = true;
		}
	}
}
?>