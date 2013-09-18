<?php
/**
 * @name UserAction.class.php
 * @package Admin
 * @desc 后台管理-用户
 * @author Lee Frank 2013-09-13
 * @version 1.0
 */
 
class UserAction extends CommonAction {

	/**
	 * @desc 用户查询条件
     * @author Lee UPDATE 2013-08-27
	 * @param array $map SQL条件数组
     * @param array $param 参数数组
	 * @return array    
	 */   
    protected function _filter(&$map, &$param) {
    	$nickname = $this->_param('nickname');
        if (!isset($_SESSION[C('ADMIN_AUTH_KEY')])) {
    		$map['id'] = array('gt', 1);
    	}
        
    	if (isset($nickname) && !empty($nickname)) {
    		$map['nickname'] = array('like', "%".$nickname."%");
    	}
        
    	$this->assign('nickname', $nickname);
    	$param['nickname'] = $nickname;
    }

	/**
	 * @desc 检查用户名
     * @author Lee UPDATE 2013-08-27  
	 */
    public function checkAccount() {
    	$account = $this->_param('account');
        if(!preg_match('/^[a-z]\w{4,}$/i', $account)) {
            $this->error('用户名必须是字母，且5位以上！');
            exit(0);
        }
        
        $User = M("User");
        
        //检查用户名是否存在
        $result  = $User->getByAccount($account);
        !empty($result) ? $this->error('该用户名已经存在！') : $this->success('该用户名可以使用！');
        
        exit(0);
    }

	/**
	 * @desc 用户添加保存-插入数据库
     * @author Lee UPDATE 2013-08-27  
	 */
    public function insert() {
        $User = D("User");
        if(!$User->create()) {
            $this->error($User->getError());
        }else {
        	$result = $User->add();
            if($result) {
                //添加权限
                $this->addRole($result);
                $this->success('用户添加成功！');
            } else {
                $this->error('用户添加失败！');
            }
        }
        exit(0);
    }
	     
	/**
	 * @desc 用户添加权限
     * @author Lee UPDATE 2013-08-27  
	 */     
    protected function addRole($userId) {
        $RoleUser = M("RoleUser");
        
        $RoleUser->user_id = $userId;
        $RoleUser->role_id = 3;
        $RoleUser->add();
    }

	/**
	 * @desc 用户重置密码
     * @author Lee UPDATE 2013-08-27  
	 */      
    public function resetPwd() {
        $id  = $this->_param('id');
        $password = $this->_param('password');
        if('' == trim($password)) {
            $this->error('密码不能为空！');
            exit(0);
        }
        $User = M('User');
        $User->password	= md5($password);
        $User->id =	$id;
        $result	= $User->save();
        false !== $result ? $this->success("密码修改为  $password") : $this->error('重置密码失败！');
        exit(0);
    }
}