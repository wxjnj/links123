<?php
/**
 * 统一用户服务接口
 * User: Go
 * Date: 13-12-4
 * Time: 下午9:30
 * 调用方式：D('User','Service');//全局单例模式
 * 接口说明：https://app.yinxiang.com/shard/s7/sh/1844768d-bc70-49fe-8231-318ce4b554c1/57011c48debfbe8950e97ac593a5033f
 */
class UserService{
	/**
	 * 用户ID
	 * @var null
	 */
	private $user_id = null;
	/**
	 * 当前sessionID
	 * @var null
	 */
	private $session_id = null;
	/**
	 * 当前登录状态
	 * @var bool
	 */
	private $is_login = false;
	/**
	 * 临时存储用户变量
	 * @var array
	 */
	private $user_data = array();
	/**
	 * 临时存储用户缓存
	 * @var array
	 */
	private $user_cache = array();
	/*
	 * SSO接口地址
	 */
	const SSO_INTERNAL_HOST = '';
	const SSO_OPEN_HOST = '';
	/*
	 * mark标记常量
	 */
	/**
	 * 新增标记
	 */
	const MARK_INSERT = 1;
	/**
	 * 更新标记
	 */
	const MARK_UPDATE = 2;
	/**
	 * 删除标记
	 */
	const MARK_DELETE = 3;
	public function __construct(){
		//自动获取当前用户信息
		//初始化用户状态
	}
	/**
	 * @desc 存储用户变量
	 * @param $key
	 * @param $value
	 * @return boolen
	 */
	public function setVar($key,$value){
		$this->user_data[$key] = $value;
		if($this->isLogin()){
			//实际存储
		}else{
			//设置更新标记
			$this->setMark('@var.'.$key,self::MARK_UPDATE);
			//传递到缓存中，并设定@var的命名空间
			return $this->setCache('@var.'.$key,$value);
		}

	}
	/**
	 * @desc 获取用户变量
	 * @param $key
	 * @return string
	 */
	public function getVar($key){
		if(isset($this->user_data[$key])) return $this->user_data[$key];
		if($this->isLogin()){
			if($this->getMark('@var.'.$key,self::MARK_UPDATE)){
				$value = $this->getCache('@var'.$key);
				$this->setVar($key,$value);
			}else{
				//获取数据
			}
			return $value;
		}else{
			return $this->getCache('@var'.$key);
		}
	}
	/**
	 * @desc 存储缓存数据
	 * @param $key
	 * @param $value
	 * @param int $time 不设置则为用户当前登录生命周期
	 * @return boolen
	 */
	public function setCache($key,$value,$time=0){
		$userid = $this->getUserId();
		//设定逻辑
		return $this->_setCache($key,$value,$time);
	}
	/**
	 * @desc 获取用户缓存
	 * @param $key
	 * @return string
	 */
	public function getCache($key){
		$userid = $this->getUserId();
		//设定逻辑
		return $this->_getCache($key);
	}

	/**
	 * @desc 设定数据状态，以供同步数据时使用
	 * @param $key
	 * @param $mark
	 * @return boolen
	 */
	public function setMark($key,$mark){
		//对于登录用户，无须设置同步标记
		if($this->isLogin()) return true;
		$statusInt = $this->getCache('@mark.'.$key);
		if(!$statusInt) $statusInt = 0;
		return $this->setCache('@mark.'.$key,$statusInt | 1 << $mark-1);
	}

	/**
	 * @desc 返回设定的状态数组
	 * @param $key
	 * @param $mark 如果设置则只返回该状态的布尔值
	 * @return mixed
	 */
	public function getMark($key,$mark=false){
		//对于未登录用户，无须进行同步操作
		if(!$this->isLogin()) return false;
		$statusInt = $this->getCache('@mark.'.$key);
		if($mark === false){
			return $statusInt;
		}else{
			return $this->judgeMark($statusInt,$mark);
		}
	}

	/**
	 * @desc 判断标识的简化接口
	 * @param $value
	 * @param $mark
	 * @return boolen
	 */
	public function judgeMark($value,$mark){
		return $value & 1 << $mark-1;
	}

	/**
	 * @desc 返回当前用户的sessionID
	 * @return int
	 */
	public function getSessionId(){
		return $this->session_id;
	}

	/*
	 * 以下为SSO接口
	 */

	/**
	 * @desc 返回当前用户的userID
	 * @return int
	 */
	public function getUserId(){
		return $this->user_id;
	}

	/**
	 * @desc 返回当前用户的登录状态
	 * @return boolen
	 */
	public function isLogin(){
		return $this->is_login;
	}

	/**
	 * @desc 返回当前用户的用户信息
	 * @return array
	 */
	public function getUserInfo(){
		if($this->isLogin()){
			$user = array();
			return array(
				'user_name'=>$user['user_name'],
				'user_avatar'=>$user['user_avatar']
			);
		}else{
			return array(
				'user_name'=>'游客',
				'user_avatar'=>'face.jpg'
			);
		}

	}

	/**
	 * @desc 登录操作接口
	 * @param $username
	 * @param $password
	 * @param $autologin
	 * @return boolen
	 */
	public function login($username,$password,$autologin){

	}

	/**
	 * @desc 登出操作接口
	 * @return boolen
	 */
	public function logout(){

	}

	/**
	 * @desc 注册操作接口
	 * @param $username
	 * @param $email
	 * @param $password
	 * @return boolen
	 */
	public function regist($username,$email,$password){

	}

	/**
	 * @desc 修改密码接口
	 * @param $oldpassword
	 * @param $newpassword
	 * @return boolen
	 */
	public function changePassword($oldpassword,$newpassword){

	}

	/**
	 * @desc 重置密码接口
	 * @param $email
	 * @return boolen
	 */
	public function resetPassword($email){

	}

	/**
	 * @desc 上传头像接口
	 * @param $file
	 * @return boolen
	 */
	public function uploadAvatar($file){

	}

	/*
	 * 以下为内置函数
	 */
	private function _setCache($key,$value,$time){
		$this->user_cache[$key] = $value;
		//实际存储
	}
	private function _getCache($key){
		if(isset($this->user_cache[$key])) return $this->user_cache[$key];
		//获取数据
		return $this->user_cache[$key];
	}
	private function get($url){
	}
}