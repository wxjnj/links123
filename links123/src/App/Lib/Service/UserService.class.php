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
	private $service =null;
	/*
	 * mark标记常量
	 */
	/**
	 * 重置标记
	 */
	const MARK_CLEAN = 0;
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
		//根据配置参数，确定是否开启sso接口
		$class = C('MEMBER_SSO') ? 'UserServiceSSO':'UserServiceDefault';
		$this->service = new $class();
	}
	public function __call($fname,$param){
		return call_user_func_array(array($this->service,$fname),$param);
	}
}
class UserServiceDefault{
	/**
	 * 用户ID
	 * @var null
	 */
	protected $user_id = 0;
	/**
	 * 游客ID，用来在未登录状态，同样能存储复杂数据的方式
	 * 对无该需求的应用，应该取消游客ID的操作
	 * @var null
	 */
	protected $guest_id = 0;
	/**
	 * 当前sessionID
	 * @var null
	 */
	protected $session_id = null;
	/**
	 * 当前登录状态
	 * @var bool
	 */
	protected $is_login = false;
	/**
	 * 当前登录有效期
	 * @var int
	 */
	protected $expire_time = 0;
	/**
	 * 临时存储用户变量
	 * @var array
	 */
	protected $user_data = array();
	/**
	 * 临时存储用户缓存
	 * @var array
	 */
	protected $user_cache = array();
	/**
	 *错误信息
	 * @var string
	 */
	protected $error = "";

	public function __construct(){
		$this->token();
		$this->init();
	}
	protected function init(){

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
			$this->setMark($flag,UserService::MARK_UPDATE);
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
			if($this->getMark($flag,UserService::MARK_UPDATE)){
				$value = $this->getSession($flag);
				$this->setVar($key,$value);
				$this->cleanMark($flag,UserService::MARK_CLEAN);
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
		return $this->_setCache($this->getSessionId().'@session.'.$key,$value,$this->expire_time - time());
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
		$this->setMark($flag,UserService::MARK_UPDATE);
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
		if($this->getMark($flag,UserService::MARK_UPDATE)){
			list($time,$value) = $this->_getCache($this->getSessionId().$flag);
			//同步游客缓存到用户缓存，并同步同样的失效时间
			$this->_setCache($userId.$flag,$value,$time - time());
			$this->cleanMark($flag,UserService::MARK_CLEAN);
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
		if($mark == UserService::MARK_CLEAN) return $this->cleanMark($key);
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
	 * @desc 清除设定的状态，在同步后需自行调用
	 * @param $key
	 * @param $mark
	 * @return boolen
	 */
	public function cleanMark($key,$mark=UserService::MARK_CLEAN){
		$flag = '@mark.'.$key;
		if($mark == UserService::MARK_CLEAN){//清除全部状态
			return $this->setSession($flag,UserService::MARK_CLEAN);
		}else{
			$statusInt = $this->getSession($flag);
			if(!$statusInt) return true;
			return $this->setSession($flag,$statusInt & 1 << $mark-1 ^ $statusInt);
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
	 * 获取错误信息
	 * @return string
	 */
	public function getError(){
		return $this->error;
	}

	/**
	 * @desc 返回当前用户的sessionID
	 * @return int
	 */
	public function getSessionId(){
		return $this->session_id;
	}

	/**
	 * @desc 返回当前用户的userID,未登录返回游客ID
	 * 		 为了实现未登录用户的复杂数据存储，对没有该功能需求的应用，因取消游客ID的使用
	 * @return int
	 */
	public function getId(){
		return $this->user_id ? $this->user_id : $this->getGuestId();
	}

	/**
	 * 对于没有同步要求的数据，或者明确要userid的数据，因直接使用该接口
	 * @return null
	 */
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

	/*
	 * 以下为SSO接口所需扩展
	 */

	/**
	 * @desc 返回当前用户的用户信息
	 * @param mixed $user_id
	 * @return array
	 */
	public function getUserInfo($user_id=false){
		$filePath = '/Public/Uploads/Faces/';
		if($user_id === false) {
			//获取当前用户的信息
			if($this->isLogin()){
				return array(
					'nickname'=>$_SESSION['nickname'],
					'avatar'=>$filePath.$_SESSION['face']
				);
			}else{
				return array(
					'nickname'=>'游客',
					'avatar'=>$filePath.'face.jpg'
				);
			}
		}elseif($user_id==0){
			//返回游客信息
			return array(
				'nickname'=>'游客',
				'avatar'=>$filePath.'face.jpg'
			);
		}else{
			$isSingle = false;
			if(!is_array($user_id)){
				$user_id = array($user_id);
				$isSingle = true;
			}
			$member = M("Member");
			$list = $member->where("id in ('".implode("','",$user_id)."')")->select();
			if(!$list) return array();
			if($isSingle){
				$row = $list[0];
				if(!$row['face']){
					$row['avatar'] = $filePath.'face.jpg';
				}else{
					$row['avatar'] = $filePath.$row['face'];
				}
				return $row;
			}
			$list_tmp = array();
			foreach($list as $row){
				if(!$row['face']){
					$row['avatar'] = $filePath.'face.jpg';
				}else{
					$row['avatar'] = $filePath.$row['face'];
				}
				$list_tmp[$row['id']] = $row;
			}
			return $list_tmp;
		}
	}

	/**
	 * @desc 登录操作接口
	 * @param $nickname
	 * @param $password
	 * @param $autologin
	 * @return boolen
	 */
	public function login($nickname,$password,$autologin){
		if (checkEmail($nickname)) {
			$param = 'email';
		} else if (checkName($nickname)) {
			$param = 'nickname';
		} else {
			$this->error = "用户名有不法字符";
			//echo json_encode(array("code"=>501, "content" => "用户名有不法字符"));
			//return false;
			return 202;
		}
		$member = M("Member");
		$mbrNow = $member->where("$param = '%s'", $nickname)->find();

		if (empty($mbrNow)) {
			$this->error = "用户名不存在";
			//echo json_encode(array("code"=>502, "content" => "用户名不存在"));
			//return false;
			return 203;
		}
		if ($mbrNow['status'] == -1) {
			$this->error = "已禁用！";
			//echo json_encode(array("code"=>403, "content" => "已禁用！"));
			//return false;
			return 204;
		}

		$password = md5(md5($password).$mbrNow['salt']);
		if ($password != $mbrNow['password']) {
			// 用户登录输入错误密码次数计数
			//isset($_SESSION['userLoginCounterPaswd']) ? $_SESSION['userLoginCounterPaswd']++ : $_SESSION['userLoginCounterPaswd'] = 1;

			//if ($_SESSION['userLoginCounterPaswd'] > 2){    // 输入错误密码次数超过2次给用户不同的提示信息
			//	echo json_encode(array("code"=>504, "content" => "建议检查用户名是否正确"));
			//}
			//else {
			//	echo json_encode(array("code"=>503, "content" => "密码与用户名不符"));
			//}
			$this->error = "密码与用户名不符";
			return 205;
		}
		$_SESSION[C('MEMBER_AUTH_KEY')] = $mbrNow['id'];
		$_SESSION['nickname'] = $mbrNow['nickname'];
		$_SESSION['face'] = empty($mbrNow['face']) ? 'face.jpg' : $mbrNow['face'];
		$_SESSION['skinId'] = $mbrNow['skin'];
		$_SESSION['themeId'] = $mbrNow['theme'];
		$_SESSION['myarea_sort'] = $mbrNow['myarea_sort'] ? explode(',', $mbrNow['myarea_sort']) : '';
		$str = $mbrNow['id'] . "|" . md5($mbrNow['password'] . $mbrNow['nickname']);
		cookie(md5(C('MEMBER_AUTH_KEY')), $str, intval(D("Variable")->getVariable("home_session_expire")));//设置cookie记录用户登录信息，提供给英语角同步登录 Adam 2013.09.27 @todo 安全性，下一步进行单点登录优化

		//使用cookie过期时间来控制前台登陆的过期时间
		cookie(md5('home_session_expire'), time(), intval(D("Variable")->getVariable("home_session_expire")));

		//如果选中下次自动登录，记录用户信息
		if (intval($autologin) == 1) {
			$str = $mbrNow['id'] . "|" . md5($mbrNow['password'] . $mbrNow['nickname']);
			$auto_login_time = intval(D("Variable")->getVariable("auto_login_time"));
			cookie("USER_ID", $str, $auto_login_time ? : 60*60*24*7);
		}
		$this->is_login = true;
		return 200;
	}

	/**
	 * @desc 登出操作接口
	 * @return boolen
	 */
	public function logout(){
		unset($_SESSION[C('MEMBER_AUTH_KEY')]);
		unset($_SESSION['nickname']);
		unset($_SESSION['face']);
		session_destroy();
		cookie(md5(C('MEMBER_AUTH_KEY')), null);//设置cookie记录用户登录信息，提供给英语角同步登录 Adam 2013.09.27 @todo 安全性，下一步进行单点登录优化
		cookie("USER_ID", null);//退出清除下次自动登录
		$this->is_login = false;
        $this->user_id = 0;
		return true;
	}

	/**
	 * @desc 注册操作接口
	 * @param $nickname
	 * @param $email
	 * @param $password
	 * @return boolen
	 */
	public function regist($nickname,$email,$password){

		$member = M("Member");

		if ($member->where("nickname = '%s'", $nickname)->select()) {
			$this->error = "该昵称已注册过";
			//echo '该昵称已注册过';
			//return false;
			return 210;
		}

		if ($member->where("email = '%s'", $email)->select()) {
			$this->error = "该邮箱已注册过";
			//echo '该邮箱已注册过';
			//return false;
			return 213;
		}

		import("@.ORG.String");
		$data['nickname'] = $nickname;
		$data['email'] = $email;
		$data['salt'] = String::randString();
		$data['password'] = md5(md5($password) . $data['salt']);
		$data['status'] = 1;
		$data['create_time'] = time();

		if (false !== $member->add($data)) {
			$_SESSION[C('MEMBER_AUTH_KEY')] = $member->getLastInsID();
			$_SESSION['nickname'] = $nickname;
			$_SESSION['face'] = 'face.jpg';
			//给新增用户添加默认自留地
			$myareaModel = D("Myarea");
			$default_myarea = $myareaModel->field("web_name, url, sort")->where("mid = 0")->Group("url")->order("sort ASC")->limit(30)->select();

			foreach ($default_myarea as $value) {
				$value['create_time'] = &$data['create_time'];
				$value['mid'] = &$_SESSION[C('MEMBER_AUTH_KEY')];
				$myareaModel->add($value);
			}

			$home_session_expire = intval(D("Variable")->getVariable("home_session_expire"));
			cookie(md5("home_session_expire"), time(), $home_session_expire);

			$str = $_SESSION[C('MEMBER_AUTH_KEY')] . "|" . md5($data['password'] . $data['nickname']);
			cookie(md5(C('MEMBER_AUTH_KEY')), $str, intval(D("Variable")->getVariable("home_session_expire")));//设置cookie记录用户登录信息，提供给英语角同步登录 Adam 2013.09.27 @todo 安全性，下一步进行单点登录优化

			$this->is_login = true;
			//echo "regOK";
			return 200;
		} else {
			$this->error = "会员注册失败";
			Log::write('会员注册失败：' . $member->getLastSql(), Log::SQL);
			return 211;
		}
	}

	/**
	 * 修改用户信息
	 * @param $nickname
	 * @param $email
	 * @param $password
	 * @return int
	 */
	public function updateUser($nickname,$email,$password){
		$member = M("Member");
		if (!empty($nickname) && $member->where("id <> '%d' and nickname = '%s'", $this->user_id, $nickname)->find()) {
			$this->error = "该昵称已被使用，请换一个！";
			//echo "该昵称已被使用，请换一个！";
			//return false;
			return 210;
		}
		if (!empty($email) && $member->where("id <> '%d' and email = '%s'", $this->user_id, $email)->find()) {
			$this->error = "该email已被使用，请换一个！";
			//echo "该email已被使用，请换一个！";
			//return false;
			return 213;
		}
		$mem = $member->where("id = '%d'", $this->user_id)->find();
		$data = array();
		if(!empty($nickname) && $nickname != $mem['nickname']){
			$data['nickname'] = $nickname;
		}
		if(!empty($email) && $email != $mem['email']){
			$data['email'] = $email;
		}
		if(!empty($password)){
			$password = md5(md5($password) . $mem['slat']);
			$data['password'] = $password;
		}
		if (false === $member->where("id = '%d'", $this->user_id)->save($data)) {
			Log::write('保存失败：' . $member->getLastSql(), Log::SQL);
			//echo "保存昵称失败！";
			$this->error = "保存昵称失败！";
			return 212;
		} else {
			if(!empty($nickname)) $_SESSION['nickname'] = $nickname;
			//echo "saveOK";
			return 200;
		}
	}

	/**
	 * @desc 修改昵称接口
	 * @param $nickname
	 * @return mixed
	 */
	public function changeNickname($nickname){
		return $this->updateUser($nickname,'','');
	}

	/**
	 * @desc 修改邮箱接口
	 * @param $email
	 * @return mixed
	 */
	public function changeEmail($email){
		return $this->updateUser('',$email,'');
	}
	/**
	 * @desc 修改密码接口
	 * @param $password
	 * @return boolen
	 */
	public function changePassword($password){
		return $this->updateUser('','',$password);
	}

	/**
	 * @desc 重置密码接口
	 * @param $email
	 * @return boolen
	 */
	public function resetPassword($email){

		$mbr = M("Member");
		$mbrNow = $mbr->getByEmail($email);

		if ($mbrNow) {
			import("@.ORG.String");
			$password = String::randString();
			if (false !== $mbr->where("id = '%d'", $mbrNow['id'])->setField('password', md5(md5($password) . $mbrNow['salt']))) {
				$mail = array();
				$mail['mailto'] = $email;
				$mail['title'] = "[另客网]忘记密码";
				$mail['content'] = "您好，您的新密码是：" . $password . "<br /><br />为了您的账户安全，请登录后尽快修改您的密码，谢谢！<br /><br />--------------------<br /><br />（这是一封自动发送的邮件，请不要直接回复）";
				if (sendMail($mail)) {
					$mailserver = 'mail.' . substr($email, strpos($email, '@') + 1);
					$mailserver = strtolower($mailserver);
					$mailserver = str_replace('gmail', 'google', $mailserver);
					$mailserver = str_replace('mail.hotmail.com', 'www.hotmail.com', $mailserver);
					//echo "sendOK|" . $mailserver;
					return 200;
				} else {
					$this->error = "发送新密码失败！";
					//echo "发送新密码失败！";
					return 219;
				}
			}
		} else {
			$this->error = "未发现您输入的邮箱！";
			//echo "未发现您输入的邮箱！";
			return 203;
		}
	}

	/**
	 * @desc 上传头像接口
	 * @param $file 传递真实的文件路径
	 * @return boolen
	 */
	public function uploadAvatar($file){
		$face = basename($file);
		$member = M("Member");
		if (false === $member->where("id = '%d'", $this->getUserId())->setField('face', $face)) {
			$this->error = "设定头像失败";
			Log::write('设定头像失败：' . $member->getLastSql(), Log::SQL);
			//echo "设定头像失败！";
			return 303;
		} else {
			$_SESSION['face'] = $face;
			//echo "saveOK";
			return 200;
		}
	}

	/**
	 * @desc 验证当前登录状态
	 */
	public function token(){
		//自动获取当前用户信息
        //初始化用户状态：目前使用原本的状态判断，日后接入sso
		session_start();
		isset($_SESSION[C('MEMBER_AUTH_KEY')]) &&
		$this->user_id = @ intval($_SESSION[C('MEMBER_AUTH_KEY')]);
        $user_str = $_COOKIE[md5(C('MEMBER_AUTH_KEY'))];
		$auto_user_str = $_COOKIE['USER_ID'];
        //无登陆标识、无自动登录标识
        if(empty($user_str) && empty($auto_user_str)){
            $this->logout();
            $this->getGuestId();
        }else{
            if(empty($this->user_id)){
                if(empty($user_str)){
                    $user_str = $auto_user_str;
                }
                //主站已登录则英语角自动登录
                $ret = explode("|", $user_str);
                $user_id = intval($ret[0]);
                $user_info = D("Member")->find($user_id);

                if (!empty($user_info) && md5($user_info['password'] . $user_info['nickname']) == $ret[1]) {
                    $_SESSION[C('MEMBER_AUTH_KEY')] = $user_info['id'];
                    $_SESSION['nickname'] = $user_info['nickname'];
                    $_SESSION['face'] = empty($user_info['face']) ? 'face.jpg' : $user_info['face'];
                    $_SESSION['skinId'] = $user_info['skin'];

                    $this->user_id = $user_info['id'];

                    //使用cookie过期时间来控制前台登陆的过期时间
                    $home_session_expire = intval(D("Variable")->getVariable("home_session_expire"));
                    cookie(md5("home_session_expire"), time(), $home_session_expire);
                }
            }
        }
	}
	/*
	 * 异步登录操作
	 */
	/**
	 * 异步登录操作，对于其他域名应用的登录做出登录响应
	 */
	public function onsynlogin(){
		return true;
	}
	/**
	 * 输出符合异步登录的代码到客户端执行，其他应用需要执行onsynlogin操作相应该请求
	 * @return bool|string
	 */
	public function synlogin(){
		return false;
	}

	/**
	 * 异步操作是否执行，如果需要执行，则调用synlogin
	 * @return bool
	 */
	public function needSyn(){
		return false;
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
	protected function _setCache($key,$value,$time){
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
	protected function _getCache($key){
		if(isset($this->user_cache[$key])) return $this->user_cache[$key];
		//获取数据
		$time = 0;
		if($value) list($time,$value) = explode('|',$value);
		$this->user_cache[$key] = $value;
		return array($time,$this->user_cache[$key]);
	}
}

class UserServiceSSO extends UserServiceDefault{
	private $token = false;
	private $token_cookie = '_lnk_token';
	/*
	 * SSO接口地址
	 */
	const SSO_INTERNAL_HOST = 'http://sso.links123.cn/';
	const SSO_OPEN_HOST = 'http://avatar.links123.cn/';

	/*
	 * 接口调用后的回调函数，各应用的实际逻辑处理代码
	 */
	/**
	 * 登录后，及每次验证后调用
	 * @return bool
	 */
	protected function logined(){
		//已经登录
		if(!empty($_SESSION[C('MEMBER_AUTH_KEY')])) return true;
		if(empty($this->user_id)) return false;
		$this->updateUsered();
	}

	/**
	 * 退出删除验证后调用
	 */
	protected function logouted(){
		unset($_SESSION[C('MEMBER_AUTH_KEY')]);
		unset($_SESSION['nickname']);
		unset($_SESSION['face']);
		session_destroy();
	}

	/**
	 * 注册后调用，并随后自动登录
	 */
	protected function registed(){
		//给新增用户添加默认自留地
		$myareaModel = D("Myarea");
		$default_myarea = $myareaModel->field("web_name, url, sort")->where("mid = 0")->Group("url")->order("sort ASC")->limit(30)->select();

		foreach ($default_myarea as $value) {
			$value['create_time'] = &$data['create_time'];
			$value['mid'] = $this->user_id;
			$myareaModel->add($value);
		}
	}

	/**
	 * 更新用户信息后调用：昵称，邮箱，密码，头像
	 */
	protected function updateUsered(){
		$member = M("Member");
		$return = $this->getUser();
		if(is_array($return)){
			$_SESSION[C('MEMBER_AUTH_KEY')] = $this->user_id;
			$_SESSION['nickname'] = $return['nickname'];
			$_SESSION['face'] = $return['avatar'];
			$mbrNow = $member->where("id = '%s'",  $this->user_id)->find();
			$_SESSION['skinId'] = $mbrNow['skin'];
			$_SESSION['themeId'] = $mbrNow['theme'];
			$_SESSION['myarea_sort'] = $mbrNow['myarea_sort'] ? explode(',', $mbrNow['myarea_sort']) : '';
		}
	}
	/**
	 * @desc 返回当前用户的用户信息
	 * @param mixed $user_id
	 * @return array
	 */
	public function getUserInfo($user_id=false){
		$default_face = '/Public/Uploads/Faces/face.jpg';
		if($user_id === false){
			if($this->isLogin()){
				return array(
					'nickname'=>$_SESSION['nickname'],
					'avatar'=>empty($_SESSION['face']) ? $default_face : self::SSO_OPEN_HOST.$_SESSION['face']
				);
			}else{
				return array(
					'nickname'=>'游客',
					'avatar'=>$default_face
				);
			}
		}elseif($user_id==0){
			//返回游客信息
			return array(
			'nickname'=>'游客',
			'avatar'=>$default_face
			);
		}else{
			$isSingle = false;
			if(!is_array($user_id)){
				$user_id = array($user_id);
				$isSingle = true;
			}
			$list = array();
			//此处需优化
			foreach($user_id as $uid){
				$list[] = $this->getUser($uid);
			}
			if(!$list) return array();
			if($isSingle){
				$row = $list[0];
				if(!$row['face']){
					$row['avatar'] = $default_face;
				}else{
					$row['avatar'] = self::SSO_OPEN_HOST.$row['avatar'];
				}
				return $row;
			}
			$list_tmp = array();
			foreach($list as $row){
				if(!$row['avatar']){
					$row['avatar'] = $default_face;
				}else{
					$row['avatar'] = self::SSO_OPEN_HOST.$row['avatar'];
				}
				$list_tmp[$row['id']] = $row;
			}
			return $list_tmp;
		}
	}
	/*
	 * 自定义代码结束，下面是接口代码，保持一致
	 */
	/*
	 * sso支持异步登录功能
	 */
	/**
	 * 异步登录操作，对于其他域名应用的登录做出登录响应
	 */
	public function onsynlogin(){
		$token = $_GET['token'];
		$expire = $_GET['expire'];
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');//设置P3P
		if ($expire == 0) {
			$expire = time();
		}
		cookie($this->token_cookie,$token,$expire);
		//设定已同步标识
		cookie("_lnk_syned", "1", $expire);
	}

	/**
	 * 输出符合异步登录的代码到客户端执行，其他应用需要执行onsynlogin操作相应该请求
	 * @return bool|string
	 */
	public function synlogin(){
		$syned = empty($_COOKIE['_lnk_syned']) ? 0 : 1;
		$token = $_COOKIE[$this->token_cookie];
		$token_expire = $_COOKIE['_lnk_token_expire'];
		if(!$syned && $_COOKIE['_lnk_apps']){
			$apps = json_decode($_COOKIE['_lnk_apps']);
			$synstr = '';
			foreach($apps as $value){
				if($value != $_SERVER['HTTP_HOST']){
					$synstr.="<script type='text/javascript' src='http://".$value."/SSO?token=".$token."&expire=".$token_expire."'></script>";
				}
			}
			cookie("_lnk_needsyn",null,time() - 86400);//去除同步token标识
			return $synstr;
		}
		return false;
	}

	/**
	 * 异步操作是否执行，如果需要执行，则调用synlogin
	 * @return bool
	 */
	public function needSyn(){
		return empty($_COOKIE['_lnk_needsyn']) ? false : true;
	}
	/*
	 * 以下为接口操作
	 */

	/**
	 * @desc 验证当前登录状态
	 */
	public function token(){
		if(empty($_COOKIE[$this->token_cookie])) return false;
		$this->token = $_COOKIE[$this->token_cookie];
		list($status,$info) = $this->request('get','auth/'.$this->token);
		if($status == 200){
			$this->user_id = $info['user_id'];
			$this->expire_time = $info['expiry_time'];
			$this->logined();
			cookie($this->token_cookie,$this->token,$info['expiry_time']);
			return $status;
		}else{
			$this->error = $info['message'];
			$this->logouted();
			cookie($this->token_cookie,null,time() - 86400);//token过期
			cookie("_lnk_syned", null, time() - 3600);
			cookie("_lnk_apps", null, time() - 3600);
			cookie("_lnk_token_expire", null, time() - 3600);
		}
		return $info['code'];
	}
	/**
	 * @desc 登录操作接口
	 * @param $nickname
	 * @param $password
	 * @param $autologin
	 * @return int
	 */
	public function login($nickname,$password,$autologin){
		$param = array(
			'username'=>$nickname,
			'password'=>$password,
			'autologin'=>$autologin
		);
		list($status,$info) = $this->request('post','auth',$param);
		if($status == 201){
			$this->token = $info['token'];
			$this->user_id = $info['user_id'];
			$this->expire_time = $info['expiry_time'];
			cookie($this->token_cookie,$info['token'],$info['expiry_time']);
			cookie("_lnk_token_expire", $info['expiry_time'], $info['expiry_time']);//保存token本地cookie过期时间
			cookie("_lnk_apps", json_encode($info['apps']), $info['expiry_time']);//保存sso站点列表
			cookie("_lnk_needsyn", "1", $info['expiry_time']);//需要同步token标识
			$this->logined();

			$this->is_login = true;
			return 200;
		}
		$this->error = $info['message'];
		return $info['code'];
	}
	/**
	 * @desc 登出操作接口
	 * @return int
	 */
	public function logout(){
		list($status,$info) = $this->request('delete','auth/'.$this->token);
		if($status == 200){
			cookie($this->token_cookie,null,time()-86400);
			cookie("_lnk_needsyn", null, time() - 3600);
			cookie("_lnk_apps", null, time() - 3600);
			cookie("_lnk_token_expire", null, time() - 3600);
			$this->logouted();

			$this->is_login = false;
            $this->user_id = 0;
			return $status;
		}
		$this->error = $info['message'];
		return $info['code'];
	}
	/**
	 * @desc 注册操作接口
	 * @param $nickname
	 * @param $email
	 * @param $password
	 * @return int
	 */
	public function regist($nickname,$email,$password){
		$param = array(
			'nickname'=>$nickname,
			'email'=>$email,
			'password'=>$password
		);
		list($status,$info) = $this->request('post','users',$param);
		if($status == 201){
			$this->user_id = $info['id'];
			$this->registed($nickname,$email,$password);
			$this->login($nickname,$password,false);
			return 200;
		}
		$this->error = $info['message'];
		return $info['code'];
	}

	/**
	 * 修改用户信息
	 * @param $nickname
	 * @param $email
	 * @param $password
	 * @return int
	 */
	public function updateUser($nickname,$email,$password){
		$param = array(
			'nickname'=>$nickname,
			'email'=>$email,
			'password'=>$password
		);
		list($status,$info) = $this->request('put','users/'.$this->user_id.'?token='.$this->token,$param);
		if($status == 204){
			$this->updateUsered();
			return 200;
		}
		$this->error = $info['message'];
		return $info['code'];
	}

	/**
	 * @desc 修改昵称接口
	 * @param $nickname
	 * @return mixed
	 */
	public function changeNickname($nickname){
		return $this->updateUser($nickname,'','');
	}

	/**
	 * @desc 修改邮箱接口
	 * @param $email
	 * @return mixed
	 */
	public function changeEmail($email){
		return $this->updateUser('',$email,'');
	}
	/**
	 * @desc 修改密码接口
	 * @param $password
	 * @return boolen
	 */
	public function changePassword($password){
		return $this->updateUser('','',$password);
	}

	/**
	 * @desc 重置密码接口
	 * @param $email
	 * @return boolen
	 */
	public function resetPassword($email){
		list($status,$info) = $this->request('post','users/'.urlencode($email).'/password_reset');
		if($status == 204){
			return 200;
		}
		$this->error = $info['message'];
		return $info['code'];
	}

	/**
	 * @desc 获取当前用户信息，或传入参数USERID
	 * @param $user_id 可选，为空则查询当前用户信息
	 * @return mixed
	 */
	public function getUser($user_id=false){
		list($status,$info) = $this->request('get','users/'.($user_id ? $user_id : $this->user_id));
		if($status == 200){
			return $info;
		}else{
			$this->error = $info['message'];
			return $info['code'];
		}
	}

	/**
	 * @desc 上传头像接口
	 * @param $file
	 * @return boolen
	 */
	public function uploadAvatar($file){
		$param = file_get_contents($file);
		list($status,$info) = $this->request('post','users/'.$this->user_id.'/avatar?token='.$this->token,$param);
		if($status == 201){
			$this->updateUsered();
			return 200;
		}
		$this->error = $info['message'];
		return $info['code'];
	}
	private function request($method,$url,$param=array()){
		$url = self::SSO_INTERNAL_HOST.$url;

		$method = strtoupper($method);
		$request_headers['Content-Type'] = 'application/x-www-form-urlencoded';
		//上传头像的数据不用json
		$body = is_array($param) ?  json_encode($param) : $param;
		$curl_handle = curl_init();
		// Set default options.
		curl_setopt ( $curl_handle, CURLOPT_URL, $url );
		curl_setopt ( $curl_handle, CURLOPT_HEADER, true );
		curl_setopt ( $curl_handle, CURLOPT_RETURNTRANSFER, true );
		if($request_headers){
			$temp_headers = array ();
			foreach ( $request_headers as $k => $v ) {
				$temp_headers [] = $k . ': ' . $v;
			}
			curl_setopt ( $curl_handle, CURLOPT_HTTPHEADER, $temp_headers );
		}
		switch ($method) {
			case 'POST':
				curl_setopt ( $curl_handle, CURLOPT_POST, true );
				break;
			case 'HEAD':
				curl_setopt ( $curl_handle, CURLOPT_CUSTOMREQUEST,'HEAD');
				curl_setopt ( $curl_handle, CURLOPT_NOBODY, 1 );
				break;
			default : // Assumed GET
				curl_setopt ( $curl_handle, CURLOPT_CUSTOMREQUEST, $method );
				break;
		}
		curl_setopt ( $curl_handle, CURLOPT_POSTFIELDS, $body );

		$response = curl_exec( $curl_handle );
		$header_size = curl_getinfo ( $curl_handle, CURLINFO_HEADER_SIZE );
		$response_body = substr ( $response, $header_size );
		$response_code = curl_getinfo ( $curl_handle, CURLINFO_HTTP_CODE );
		curl_close($curl_handle);
		return array($response_code,json_decode($response_body,true));
	}
}