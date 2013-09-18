<?php
/**
 * @desc 网站基本信息设置
 * @name BasicSetAction.class.php
 * @package Admin
 * @author Frank UPDATE 2013-09-12
 * @version 1.0
 */
class BasicSetAction extends CommonAction {

    public function index() {
        $variable = D("Variable");
        
        $vars = $variable->select();
        foreach ($vars as $var) {
        	switch($var['vname']) {
        		case "auto_login_time":
        			$data = $var['value_int'] / (60 * 60 * 24);
        			break;
        		case "admin_session_expire":
        			$data = $var['value_int'] / 60;
        			break;
        		case "home_session_expire":
        			$data = $var['value_int'] / 60;
        			break;
        		case "english_tourist_record_save_time":
        			$data = $var['value_int'] / 24;
        			break;
        		default:
        			$data = $var;
        	}
        	
        	$this->assign($var['vname'], $data);
        }
        
        $this->display();
        return;
    }
    /**
     * @desc 设定标题
     * @author Frank UPDATE 2013-09-12
     */
    public function setTitle() {
        $variable = M("Variable");
        $list = $variable->where("vname = 'title'")->setField('value_varchar', $this->_post('title'));
        //lTrace('Log/lastSql', $this->getActionName(), $variable->getLastSql());
        false !== $list ? $this->success('成功设定标题!') : $this->error('设定标题失败!');
    }

    /**
     * @desc 设定关键词
     * @author Frank UPDATE 2013-09-12
     */
    public function setKeywords() {
        $variable = M("Variable");
        $list = $variable->where("vname = 'Keywords'")->setField('value_varchar', $this->_post('Keywords'));
        //lTrace('Log/lastSql', $this->getActionName(), $variable->getLastSql());
        false !== $list ? $this->success('成功设定关键词!') : $this->error('设定关键词失败!');
    }

    /**
     * @desc 设定描述
     * @author Frank UPDATE 2013-09-12
     */
    public function setDescription() {
        $variable = M("Variable");
        $list = $variable->where("vname = 'Description'")->setField('value_varchar', $this->_post('Description'));
        //lTrace('Log/lastSql', $this->getActionName(), $variable->getLastSql());
        false !== $list ? $this->success('成功设定描述!') : $this->error('设定描述失败!');
    }

    /**
     * @desc 设定公告标题
     * @author Frank UPDATE 2013-09-12
     */
    public function setAnnName() {
        $variable = M("Variable");
        $list = $variable->where("vname = 'ann_name'")->setField('value_varchar', $this->_post('ann_name'));
        //lTrace('Log/lastSql', $this->getActionName(), $variable->getLastSql());
        false !== $list ? $this->success('成功设定公告标题!') : $this->error('设定公告标题失败!');
    }

    
    /**
     * @desc 设定发件箱
     * @author Frank UPDATE 2013-09-12
     */
    public function setSendFrom() {
        $variable = M("Variable");
        $list = $variable->where("vname = 'send_from'")->setField('value_varchar', $this->_post('send_from'));
        //lTrace('Log/lastSql', $this->getActionName(), $variable->getLastSql());
        false !== $list ? $this->success('成功设定发件箱!') : $this->error('设定发件箱失败!');
    }
    
    /**
     * @desc 设定中文岛提示
     * @author Frank UPDATE 2013-09-12
     */
    public function setCnTip() {
        $variable = M("Variable");
        $list = $variable->where("vname = 'cn_tip'")->setField('value_varchar', $this->_post('cn_tip'));
        //lTrace('Log/lastSql', $this->getActionName(), $variable->getLastSql());
        false !== $list ? $this->success('成功设定中文岛提示!') : $this->error('设定中文岛提示失败!');
    }
    
    /**
     * @desc 设定英文岛提示
     * @author Frank UPDATE 2013-09-12
     */
    public function setEnTip() {
        $variable = M("Variable");
        $list = $variable->where("vname ='en_tip'")->setField('value_varchar', $this->_post('en_tip'));
        //lTrace('Log/lastSql', $this->getActionName(), $variable->getLastSql());
        false !== $list ? $this->success('成功设定英文岛提示!') : $this->error('设定英文岛提示失败!');
    }
    
    /**
     * @desc 设定糖葫芦
     * @author Frank UPDATE 2013-09-12
     */
    public function setThl() {
        $variable = M("Variable");
        $list = $variable->where("vname = 'thl'")->setField('value_varchar', $this->_post('thl'));
        //lTrace('Log/lastSql', $this->getActionName(), $variable->getLastSql());
        false !== $list ? $this->success('成功设定糖葫芦!') : $this->error('设定糖葫芦失败!');
    }

    /**
     * @desc 设定目录图切换时间间隔
     * @author Frank UPDATE 2013-09-12
     */
    public function setPauseTime() {
        $variable = M("Variable");
        $list = $variable->where("vname = 'pauseTime'")->setField('value_int', $this->_post('pauseTime'));
        //lTrace('Log/lastSql', $this->getActionName(), $variable->getLastSql());
        false !== $list ? $this->success('成功设定目录图切换时间间隔!') : $this->error('设定目录图切换时间间隔失败!');
    }
    
    /**
     * @desc 设定默认排列形式
     * @author Frank UPDATE 2013-09-12
     */
    public function setPailie() {
        $variable = M("Variable");
        $list = $variable->where("vname = 'pailie'")->setField('value_int', $this->_post('pailie'));
        //lTrace('Log/lastSql', $this->getActionName(), $variable->getLastSql());
        false !== $list ? $this->success('成功设定默认排列形式!') : $this->error('设定默认排列形式失败!');
    }
    
    /**
     * @desc 设定直达网址提示
     * @author Frank UPDATE 2013-09-12
     */
    public function setDirectTip() {
        $variable = M("Variable");
        $list = $variable->where("vname = 'directTip'")->setField('value_varchar', $this->_post('directTip'));
        //lTrace('Log/lastSql', $this->getActionName(), $variable->getLastSql());
        false !== $list ? $this->success('成功设定直达网址提示!') : $this->error('设定直达网址提示失败!');
    }

    /**
     * 设置自动登录的保持时间
     * @author Adam $date2013-07-23$
     */
    public function setAutoLoginTime() {
    	$alt = intval($this->_post('auto_login_time'));
        if ($alt== 0) {
            $this->error("必须为大于零的数字");
            exit(0);
        }
        $variableModel = D("Variable");
        $variableModel->setVariable("auto_login_time", $alt * 24 * 60 * 60, "下次自动登录保持时间，单位周");
        $this->success("设置自动登录时间成功");
    }

    /**
     * 设置前台用户登录的保持时间
     * @author Adam $date2013-07-23$
     */
    public function setHomeSessionTime() {
    	$hse = intval($this->_post('home_session_expire'));
        if ($hse == 0) {
            $this->error("必须为大于零的数字");
            exit(0);
        }
        $variableModel = D("Variable");
        $variableModel->setVariable("home_session_expire", $hse * 60, "前台登陆过期时间，单位秒");
        $this->success("设置前台用户登录的保持时间成功");
    }

    /**
     * 设置后台登录的保持时间
     * @author Adam $date2013-07-23$
     */
    public function setAdminSessionTime() {
    	$ase = intval($this->_post('admin_session_expire'));
        if ($ase == 0) {
            $this->error("必须为大于零的数字");
            exit(0);
        }
        $variableModel = D("Variable");
        $variableModel->setVariable("admin_session_expire", $ase * 60, "后台登陆过期时间，单位秒");
        $this->success("设置后台登录的保持时间成功");
    }

    /**
     * 设置英语角游客记录保存时间
     * @author Adam $date2013-08-19$
     */
    public function setEnglishTouristRecordSaveTime() {
    	$stime = intval($this->_post('english_tourist_record_save_time'));
        if ($stime == 0) {
            $this->error("必须为大于零的数字");
            exit(0);
        }
        $variableModel = D("Variable");
        $variableModel->setVariable("english_tourist_record_save_time", $stime * 24, "英语角游客记录保留时间，单位小时");
        $this->success("设置英语角游客记录保存时间成功");
    }

}

?>