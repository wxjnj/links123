<?php

/*
 * 英语角基础控制类
 * @author adam 2013.5.28
 */

class EnglishAction extends BaseAction {

	public   $englishUserLogic = null;      /** 英语角用户信息模块*/
	
	public   $englishTopicLogic = null;     /** 英语角题目视频媒体信息模块*/
	
	public   $englishCategoryLogic = null;  /** 英语角选择列表信息模块*/
	
    //重写父类的初始化方法
    public function _initialize() {
    	
    	$this->englishUserLogic = new EnglishUserLogic();
    	$this->englishTopicLogic = new EnglishTopicLogic();
    	$this->englishCategoryLogic = new EnglishCategoryLogic();
    	
        parent::_initialize(); //调用父类初始化方法
    }

}

?>
