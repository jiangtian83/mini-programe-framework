<?php  namespace Guzzle\Cache;
interface CacheAdapterInterface 
{
	public function contains($id, array $options);
	public function delete($id, array $options);
	public function fetch($id, array $options);
	public function save($id, $data, $lifeTime, array $options);
}
?>