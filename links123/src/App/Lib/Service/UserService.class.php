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
	private $user_id = 0;
	/**
	 * 游客ID，用来在未登录状态，同样能存储复杂数据的方式
	 * 对无该需求的应用，应该取消游客ID的操作
	 * @var null
	 */
	private $guest_id = 0;
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
		//初始化用户状态：目前使用原本的状态判断，日后接入sso
		session_start();
		isset($_SESSION[C('MEMBER_AUTH_KEY')]) &&
		$this->user_id = @ intval($_SESSION[C('MEMBER_AUTH_KEY')]);
		if (empty($this->user_id)) {
			$user_str = $_COOKIE["USER_ID"];
			//没有用户session且自动登录标示Cookie USER_ID 存在执行自动登录过程
			if (!empty($user_str)) {
				$ret = explode("|", $user_str);
				$user_id = intval($ret[0]);
				$user_info = D("Member")->find($user_id);

				if (!empty($user_info) && md5($user_info['password'] . $user_info['nickname']) == $ret[1]) {
					$_SESSION[C('MEMBER_AUTH_KEY')] = $user_info['id'];
					$_SESSION['nickname'] = $user_info['nickname'];
					$_SESSION['face'] = empty($user_info['face'])?'face.jpg':$user_info['face'];
					$_SESSION['skinId'] = $user_info['skin'];
					$_SESSION['themeId'] = $user_info['themeId'];

					$this->user_id = $user_info['id'];

					//使用cookie过期时间来控制前台登陆的过期时间
					$home_session_expire = intval(D("Variable")->getVariable("home_session_expire"));
					cookie(md5("home_session_expire"), time(), $home_session_expire);
				}
			}
		}
		//对于刚注册用户，还是获取之前的游客ID，来保证同步数据的正确性
		$this->guest_id = cookie(md5('member_guest'));

		if($this->user_id){
			$this->is_login = true;
		}
	}
	/**
	 * @desc 存储用户变量
	 * @param $key
	 * @param $value
	 * @return boolen
	 */
	public function setVar($key,$value){
		$this->user_data[$key] = $value;
		$flag = '@var.'.$key;
		if($this->isLogin()){
			//实际存储
		}else{
			//设置更新标记
			$this->setMark($flag,self::MARK_UPDATE);
			//传递到会话中，并设定@var的命名空间
			return $this->setSession($flag,$value);
		}

	}
	/**
	 * @desc 获取用户变量
	 * @param $key
	 * @return string
	 */
	public function getVar($key){
		if(isset($this->user_data[$key])) return $this->user_data[$key];
		$flag = '@var.'.$key;
		if($this->isLogin()){
			if($this->getMark($flag,self::MARK_UPDATE)){
				$value = $this->getSession($flag);
				$this->setVar($key,$value);
			}else{
				//获取数据
			}
			return $value;
		}else{
			return $this->getSession($flag);
		}
	}

	/**
	 * @desc 存储用户会话数据
	 * @param $key
	 * @param $value
	 * @return boolen
	 */
	public function setSession($key,$value){
		return $this->_setCache($this->getSessionId().'@session.'.$key,$value,$this->time);
	}
	/**
	 * @desc 获取用户会话数据
	 * @param $key
	 * @return string
	 */
	public function getSession($key){
		return $this->_getCache($this->getSessionId().'@session.'.$key);
	}
	/**
	 * @desc 存储缓存数据
	 * @param $key
	 * @param $value
	 * @param $time 不设置则为用户当前登录生命周期
	 * @return boolen
	 */
	public function setCache($key,$value,$time=false){
		if($time===false) return $this->setSession($key,$value);
		$flag = '@cache.'.$key;
		$userId = $this->getUserId();
		//设置更新标记
		$this->setMark($flag,self::MARK_UPDATE);
		//设定逻辑
		return $this->_setCache($userId.$flag,$value,$time);
	}
	/**
	 * @desc 获取用户缓存
	 * @param $key
	 * @return string
	 */
	public function getCache($key){
		$flag = '@cache.'.$key;
		$userId = $this->getUserId();
		if($this->getMark($flag,self::MARK_UPDATE)){
			list($time,$value) = $this->_getCache($this->getSessionId().$flag);
			//同步游客缓存到用户缓存，并同步同样的失效时间
			$this->_setCache($userId.$flag,$value,$time - time());
		}else{
			list($time,$value) = $this->_getCache($userId.$flag);
		}
		return $value;
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
		$flag = '@mark.'.$key;
		$statusInt = $this->getSession($flag);
		if(!$statusInt) $statusInt = 0;
		return $this->setSession($flag,$statusInt | 1 << $mark-1);
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
		$flag = '@mark.'.$key;
		$statusInt = $this->getSession($flag);
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
	 * @desc 返回当前用户的userID,未登录返回游客ID
	 * 		 为了实现未登录用户的复杂数据存储，对没有该功能需求的应用，因取消游客ID的使用
	 * @return int
	 */
	public function getId(){
		return $this->user_id ? $this->user_id : $this->getGuestId();
	}
	public function getUserId(){
		return $this->user_id;
	}
	public function getGuestId(){
		if(!$this->guest_id){
			$guestModel = M('MemberGuest');
			$guest_id = $guestModel->add(array('create_time' => time(), 'status' => 1));
			if ($guest_id) {
				$this->guest_id = - $guest_id;
				if ($guestModel->where(array('id' => $guest_id))->save(array('mid' => $this->guest_id))) {
					cookie(md5('member_guest'), $this->guest_id, 365*24*60*60);
				}
			}
		}
		return $this->guest_id;
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

	/**
	 * @desc 验证当前登录状态
	 */
	public function token(){

	}

	/*
	 * 以下为内置函数
	 */
	/**
	 * @desc 内置实际的缓存操作,不处理逻辑，只处理存储
	 * @param $key
	 * @param $value
	 * @param $time
	 * @return boolen
	 */
	private function _setCache($key,$value,$time){
		$this->user_cache[$key] = $value;
		//简单设定失效时间，用于同步缓存中
		$value = ($time + time()).'|'.$value;
		//实际存储
	}

	/**
	 * @desc 内置实际的缓存操作，不处理逻辑，只处理读取
	 * @param $key
	 * @return array
	 */
	private function _getCache($key){
		if(isset($this->user_cache[$key])) return $this->user_cache[$key];
		//获取数据
		$time = 0;
		if($value) list($time,$value) = explode('|',$value);
		$this->user_cache[$key] = $value;
		return array($time,$this->user_cache[$key]);
	}
	private function get($url){
	}
}