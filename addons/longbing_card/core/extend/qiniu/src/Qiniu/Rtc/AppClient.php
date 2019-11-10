<?php  namespace Qiniu\Rtc;
class AppClient 
{
	private $auth = NULL;
	private $baseURL = NULL;
	public function __construct(\Qiniu\Auth $auth) 
	{
		$this->auth = $auth;
		$this->baseURL = sprintf("%s/%s/apps", \Qiniu\Config::RTCAPI_HOST, \Qiniu\Config::RTCAPI_VERSION);
	}
	public function createApp($hub, $title, $maxUsers = NULL, $noAutoKickUser = NULL) 
	{
		$params["hub"] = $hub;
		$params["title"] = $title;
		if( !empty($maxUsers) ) 
		{
			$params["maxUsers"] = $maxUsers;
		}
		if( !empty($noAutoKickUser) ) 
		{
			$params["noAutoKickUser"] = $noAutoKickUser;
		}
		$body = json_encode($params);
		$ret = $this->post($this->baseURL, $body);
		return $ret;
	}
	public function updateApp($appId, $hub, $title, $maxUsers = NULL, $mergePublishRtmp = NULL, $noAutoKickUser = NULL) 
	{
		$url = $this->baseURL . "/" . $appId;
		$params["hub"] = $hub;
		$params["title"] = $title;
		if( !empty($maxUsers) ) 
		{
			$params["maxUsers"] = $maxUsers;
		}
		if( !empty($noAutoKickUser) ) 
		{
			$params["noAutoKickUser"] = $noAutoKickUser;
		}
		if( !empty($mergePublishRtmp) ) 
		{
			$params["mergePublishRtmp"] = $mergePublishRtmp;
		}
		$body = json_encode($params);
		$ret = $this->post($url, $body);
		return $ret;
	}
	public function getApp($appId) 
	{
		$url = $this->baseURL . "/" . $appId;
		$ret = $this->get($url);
		return $ret;
	}
	public function deleteApp($appId) 
	{
		$url = $this->baseURL . "/" . $appId;
		list(, $err) = $this->delete($url);
		return $err;
	}
	public function listUser($appId, $roomName) 
	{
		$url = sprintf("%s/%s/rooms/%s/users", $this->baseURL, $appId, $roomName);
		$ret = $this->get($url);
		return $ret;
	}
	public function kickUser($appId, $roomName, $userId) 
	{
		$url = sprintf("%s/%s/rooms/%s/users/%s", $this->baseURL, $appId, $roomName, $userId);
		list(, $err) = $this->delete($url);
		return $err;
	}
	public function listActiveRooms($appId, $prefix = NULL, $offset = NULL, $limit = NULL) 
	{
		if( isset($prefix) ) 
		{
			$query["prefix"] = $prefix;
		}
		if( isset($offset) ) 
		{
			$query["offset"] = $offset;
		}
		if( isset($limit) ) 
		{
			$query["limit"] = $limit;
		}
		if( isset($query) && !empty($query) ) 
		{
			$query = "?" . http_build_query($query);
			$url = sprintf("%s/%s/rooms%s", $this->baseURL, $appId, $query);
		}
		else 
		{
			$url = sprintf("%s/%s/rooms", $this->baseURL, $appId);
		}
		$ret = $this->get($url);
		return $ret;
	}
	public function appToken($appId, $roomName, $userId, $expireAt, $permission) 
	{
		$params["appId"] = $appId;
		$params["userId"] = $userId;
		$params["roomName"] = $roomName;
		$params["permission"] = $permission;
		$params["expireAt"] = $expireAt;
		$appAccessString = json_encode($params);
		return $this->auth->signWithData($appAccessString);
	}
	private function get($url, $cType = NULL) 
	{
		$rtcToken = $this->auth->authorizationV2($url, "GET", null, $cType);
		$rtcToken["Content-Type"] = $cType;
		$ret = \Qiniu\Http\Client::get($url, $rtcToken);
		if( !$ret->ok() ) 
		{
			return array( null, new \Qiniu\Http\Error($url, $ret) );
		}
		return array( $ret->json(), null );
	}
	private function delete($url, $contentType = "application/json") 
	{
		$rtcToken = $this->auth->authorizationV2($url, "DELETE", null, $contentType);
		$rtcToken["Content-Type"] = $contentType;
		$ret = \Qiniu\Http\Client::delete($url, $rtcToken);
		if( !$ret->ok() ) 
		{
			return array( null, new \Qiniu\Http\Error($url, $ret) );
		}
		return array( $ret->json(), null );
	}
	private function post($url, $body, $contentType = "application/json") 
	{
		$rtcToken = $this->auth->authorizationV2($url, "POST", $body, $contentType);
		$rtcToken["Content-Type"] = $contentType;
		$ret = \Qiniu\Http\Client::post($url, $body, $rtcToken);
		if( !$ret->ok() ) 
		{
			return array( null, new \Qiniu\Http\Error($url, $ret) );
		}
		$r = ($ret->body === null ? array( ) : $ret->json());
		return array( $r, null );
	}
}
?>