<?php
/**
 * @name CommonAction.class.php
 * @package Homepage
 * @desc 首页相关基础类，单点登录
 * @author wangzhenguo 2013-07-06
 * @version 0.0.1
 */
class CommonAction extends Action {
	protected $linkUserID = 0;							//用户登录ID
	protected $linkUserName = '';						//用户登录昵称
	protected $linkTime = null;							//系统时间
	
	/**
	 * @desc 初始化
	 * @author wangzhenguo 2013-07-06
	 */
	protected function _initialize() {
		session_start();
		/**
		 * 全局变量
		 */
		$this->linkTime = time();
		$this->linkUserID = 0;
		$this->linkUserName = 'links';
		$this->assign('linkUserID', $this->linkUserID);
		$this->assign('linkUserName', $this->linkUserName);
		$this->assign('searchCategoryOn','搜');
		$this->assign('searchTitleOn','baidu');
		$this->assign('URL_ROOT', C('URL_ROOT'));
		$this->assign('URL_HOMEPAGE', C('URL_HOMEPAGE'));
		
		/**
		 * 搜索列表信息
		 */
		$search = D('Search');
		$searchList = $search->getAll();
		$this->assign('searchList', $searchList);
	}
}
?>