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
	const SSO_INTERNAL_HOST = '';
	const SSO_OPEN_HOST = '';
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

	}
	/**
	 * @desc 获取用户变量
	 * @param $key
	 * @return string
	 */
	public function getVar($key){

	}
	/**
	 * @desc 存储缓存数据
	 * @param $key
	 * @param $value
	 * @param int $time 不设置则为用户当前登录生命周期
	 * @return boolen
	 */
	public function setCache($key,$value,$time=0){

	}
	/**
	 * @desc 获取用户缓存
	 * @param $key
	 * @return string
	 */
	public function getCache($key){

	}

	/**
	 * @desc 设定数据状态，以供同步数据时使用
	 * @param $key
	 * @param $status
	 * @return boolen
	 */
	public function setMark($key,$status){

	}

	/**
	 * @desc 返回设定的状态数组
	 * @param $key
	 * @param $status 如果设置则只返回该状态的布尔值
	 * @return mixed
	 */
	public function getMark($key,$status){

	}

	/**
	 * @desc 返回当前用户的sessionID
	 * @return int
	 */
	public function getSession(){
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

	}

	/**
	 * @desc 返回当前用户的用户信息
	 * @return array
	 */
	public function getUserInfo(){

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
}