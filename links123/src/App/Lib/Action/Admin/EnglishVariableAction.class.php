<?php

/**
 * 英语角信息管理控制类
 *
 * @author adam 2013.11.27
 */
class EnglishVariableAction extends CommonAction{
    
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
    
    public function setVariable(){
        $variableName  = $this->_post("name");
        $variableValue  = $this->_post("value");
        if(empty($variableName) || empty($variableValue)){
            $this->error("操作名称和内容都不能为空!");
        }
        $variableModel = D("Variable");
        $variableExplain = $this->_post("explain");
        $ret = $variableModel->setVariable($variableName,$variableValue,$variableExplain);
        if(false === $ret){
            $this->error("编辑失败");
        }  else {
            $this->success("编辑成功");
        }
    }
}

?>
