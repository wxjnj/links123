<?php
class BasicSetAction extends CommonAction {
	//
	public function index() {
		$variable = D("Variable");
		//
		$title = $variable->getByVname('title');
		$this->assign ( 'title', $title );
		//
		$Keywords = $variable->getByVname('Keywords');
		$this->assign ( 'Keywords', $Keywords );
		//
		$Description = $variable->getByVname('Description');
		$this->assign ( 'Description', $Description );
		//
		$ann_name = $variable->getByVname('ann_name');
		$this->assign ( 'ann_name', $ann_name );
		//
		$send_from = $variable->getByVname('send_from');
		$this->assign ( 'send_from', $send_from );
		//
		$cn_tip = $variable->getByVname('cn_tip');
		$this->assign ( 'cn_tip', $cn_tip );
		//
		$en_tip = $variable->getByVname('en_tip');
		$this->assign ( 'en_tip', $en_tip );
		//
		$thl = $variable->getByVname('thl');
		$this->assign ( 'thl', $thl );
		//
		$pauseTime = $variable->getByVname('pauseTime');
		$this->assign ( 'pauseTime', $pauseTime );
		//
		$pailie = $variable->getByVname('pailie');
		$this->assign ( 'pailie', $pailie );
		//
		$directTip = $variable->getByVname('directTip');
		$this->assign ( 'directTip', $directTip );
		//
        $auto_login_time = $variable->getVariable('auto_login_time');
        $auto_login_time = $auto_login_time / (60*60*24);
        $this->assign ( 'auto_login_time', $auto_login_time );
        //
        $admin_session_expire = $variable->getVariable('admin_session_expire');
        $admin_session_expire = $admin_session_expire / 60;
        $this->assign ( 'admin_session_expire', $admin_session_expire );
        //
        $home_session_expire = $variable->getVariable('home_session_expire');
        $home_session_expire = $home_session_expire / 60;
        $this->assign ( 'home_session_expire', $home_session_expire );
        //
		$this->display();
		return;
	}

	// 设定标题
	public function setTitle() {
		$variable = M("Variable");
		$list = $variable->where('vname=\'title\'')->setField('value_varchar', $_POST['title']);
		//lTrace('Log/lastSql', $this->getActionName(), $variable->getLastSql());
		if (false !== $list) {
			$this->success ('成功设定标题!');
		} else {
			$this->error ('设定标题失败!');
		}
	}
	
	// 设定关键词
	public function setKeywords() {
		$variable = M("Variable");
		$list = $variable->where('vname=\'Keywords\'')->setField('value_varchar', $_POST['Keywords']);
		//lTrace('Log/lastSql', $this->getActionName(), $variable->getLastSql());
		if (false !== $list) {
			$this->success('成功设定关键词!');
		} else {
			$this->error('设定关键词失败!');
		}
	}

	// 设定描述
	public function setDescription() {
		$variable = M("Variable");
		$list = $variable->where('vname=\'Description\'')->setField('value_varchar', $_POST['Description']);
		//lTrace('Log/lastSql', $this->getActionName(), $variable->getLastSql());
		if (false !== $list) {
			$this->success('成功设定描述!');
		} else {
			$this->error('设定描述失败!');
		}
	}
	
	// 设定公告标题
	public function setAnnName() {
		$variable = M("Variable");
		$list = $variable->where('vname=\'ann_name\'')->setField('value_varchar', $_POST['ann_name']);
		//lTrace('Log/lastSql', $this->getActionName(), $variable->getLastSql());
		if (false !== $list) {
			$this->success('成功设定公告标题!');
		} else {
			$this->error('设定公告标题失败!');
		}
	}
	
	// 设定发件箱
	public function setSendFrom() {
		$variable = M("Variable");
		$list = $variable->where('vname=\'send_from\'')->setField('value_varchar', $_POST['send_from']);
		//lTrace('Log/lastSql', $this->getActionName(), $variable->getLastSql());
		if (false !== $list) {
			$this->success('成功设定发件箱!');
		} else {
			$this->error('设定发件箱失败!');
		}
	}
	
	// 设定中文岛提示
	public function setCnTip() {
		$variable = M("Variable");
		$list = $variable->where('vname=\'cn_tip\'')->setField('value_varchar', $_POST['cn_tip']);
		//lTrace('Log/lastSql', $this->getActionName(), $variable->getLastSql());
		if (false !== $list) {
			$this->success('成功设定中文岛提示!');
		} else {
			$this->error('设定中文岛提示失败!');
		}
	}
	
	// 设定英文岛提示
	public function setEnTip() {
		$variable = M("Variable");
		$list = $variable->where('vname=\'en_tip\'')->setField('value_varchar', $_POST['en_tip']);
		//lTrace('Log/lastSql', $this->getActionName(), $variable->getLastSql());
		if (false !== $list) {
			$this->success('成功设定英文岛提示!');
		} else {
			$this->error('设定英文岛提示失败!');
		}
	}
	
	// 设定糖葫芦
	public function setThl() {
		$variable = M("Variable");
		$list = $variable->where('vname=\'thl\'')->setField('value_varchar', $_POST['thl']);
		//lTrace('Log/lastSql', $this->getActionName(), $variable->getLastSql());
		if (false !== $list) {
			$this->success('成功设定糖葫芦!');
		} else {
			$this->error('设定糖葫芦失败!');
		}
	}
	
	// 设定目录图切换时间间隔
	public function setPauseTime() {
		$variable = M("Variable");
		$list = $variable->where('vname=\'pauseTime\'')->setField('value_int', $_POST['pauseTime']);
		//lTrace('Log/lastSql', $this->getActionName(), $variable->getLastSql());
		if (false !== $list) {
			$this->success('成功设定目录图切换时间间隔!');
		} else {
			$this->error('设定目录图切换时间间隔失败!');
		}
	}
	
	// 设定默认排列形式
	public function setPailie() {
		$variable = M("Variable");
		$list = $variable->where('vname=\'pailie\'')->setField('value_int', $_POST['pailie']);
		//lTrace('Log/lastSql', $this->getActionName(), $variable->getLastSql());
		if (false !== $list) {
			$this->success('成功设定默认排列形式!');
		} else {
			$this->error('设定默认排列形式失败!');
		}
	}
	
	// 设定直达网址提示
	public function setDirectTip() {
		$variable = M("Variable");
		$list = $variable->where('vname=\'directTip\'')->setField('value_varchar', $_POST['directTip']);
		//lTrace('Log/lastSql', $this->getActionName(), $variable->getLastSql());
		if (false !== $list) {
			$this->success('成功设定直达网址提示!');
		} else {
			$this->error('设定直达网址提示失败!');
		}
	}
    /**
     * 设置自动登录的保持时间
     * @author Adam $date2013-07-23$
     */
    public function setAutoLoginTime(){
        if(intval($_POST['auto_login_time'])==0){
            $this->error("必须为大于零的数字");
        }
        $variableModel = D("Variable");
        $variableModel->setVariable("auto_login_time",  intval($_POST['auto_login_time'])*24*60*60,"下次自动登录保持时间，单位周");
        $this->success("设置自动登录时间成功");
    }
    /**
     * 设置前台用户登录的保持时间
     * @author Adam $date2013-07-23$
     */
    public function setHomeSessionTime(){
        if(intval($_POST['home_session_expire'])==0){
            $this->error("必须为大于零的数字");
        }
        $variableModel = D("Variable");
        $variableModel->setVariable("home_session_expire",  intval($_POST['home_session_expire'])*60,"前台登陆过期时间，单位秒");
        $this->success("设置前台用户登录的保持时间成功");
    }
    /**
     * 设置后台登录的保持时间
     * @author Adam $date2013-07-23$
     */
    public function setAdminSessionTime(){
        if(intval($_POST['admin_session_expire'])==0){
            $this->error("必须为大于零的数字");
        }
        $variableModel = D("Variable");
        $variableModel->setVariable("admin_session_expire",  intval($_POST['admin_session_expire'])*60,"后台登陆过期时间，单位秒");
        $this->success("设置后台登录的保持时间成功");
    }

}
?>