<?php
/**
 * @name IndexAction.class.php
 * @package Homepage
 * @desc 首页相关
 * @author wangzhenguo 2013-07-06
 * @version 0.0.1
 */
class IndexAction extends CommonAction {
	/**
	 * @desc 首页
	 * @author wangzhenguo 2013-07-06
	 */
	public function index() {
		$this->assign('ACTION','Index');
		$this->assign('OPERATE','index');
		$this->display();
		//var_dump($this->linkUserID);
	}
}
?>