<?php

/**
 * @name UserAction.class.php
 * @package Admin
 * @desc 后台用户模块
 * @author lawrence UPDATE 2013-08-20
 * @version 0.0.1
 */
class UserAction extends CommonAction {

    protected function _filter(&$map,&$param){
        if (!isset($_SESSION[C('ADMIN_AUTH_KEY')])) {
    		$map['id'] = array('gt',1);
    	}
    	if ( isset($_REQUEST['nickname'])) {
    		$nickname = $_REQUEST['nickname'];
    	}
    	if (!empty($nickname)) {
    		$map['nickname'] = array('like',"%".$nickname."%");
    	}
    	$this->assign('nickname',$nickname);
    	$param['nickname'] = $nickname;
    }

	/**
	 * @desc 检查帐号
	 * @see UserAction::checkAccount()
	 */
    public function checkAccount() {
        if(!preg_match('/^[a-z]\w{4,}$/i',$_POST['account'])) {
            $this->error('用户名必须是字母，且5位以上！');
        }
        $User = M("User");
        // 检测用户名是否冲突
        $name  = $_REQUEST['account'];
        $result  = $User->getByAccount($name);
        if($result){
            $this->error('该用户名已经存在！');
        }else {
            $this->success('该用户名可以使用！');
        }
    }

	/**
	 * @desc 插入数据
	 * @see UserAction::insert()
	 */
    public function insert() {
        // 创建数据对象
        $User = D("User");
        if(!$User->create()) {
            $this->error($User->getError());
        }else{
            // 写入帐号数据
        	$result = $User->add();
            if($result) {
                $this->addRole($result);
                $this->success('用户添加成功！');
            }else{
                $this->error('用户添加失败！');
            }
        }
    }
	
	/**
	 * @desc 添加权限
	 * @see UserAction::addRole()
	 */
    protected function addRole($userId) {
        $RoleUser = M("RoleUser");
        $RoleUser->user_id = $userId;
        $RoleUser->role_id = 3;
        $RoleUser->add();
    }

	/**
	 * @desc 重置密码
	 * @see UserAction::resetPwd()
	 */
    public function resetPwd() {
        $id  = $_POST['id'];
        $password = $_POST['password'];
        if(''== trim($password)) {
            $this->error('密码不能为空！');
        }
        $User = M('User');
        $User->password	= md5($password);
        $User->id =	$id;
        $result	= $User->save();
        if(false !== $result) {
            $this->success("密码修改为$password");
        }else {
            $this->error('重置密码失败！');
        }
    }
}