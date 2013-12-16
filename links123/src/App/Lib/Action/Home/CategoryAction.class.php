<?php
/**
 * @name AboutAction
 * @package Home
 * @desc 搜索导航，在IndexAction Search方法中搜索不到任何链接时跳转到此Action
 * @version 1.0
 * @author frank 2013-08-28
 */
import("@.Common.CommonAction");
class CategoryAction extends CommonAction {

	/**
	 * @name category
	 * @desc 根据类别搜索链接
	 * @param int lan
	 * @param int rid
	 * @author Frank 2013-08-28
	 */
	public function index() {
		
		$language = $this->_param('lan');
		$rid = $this->_param('rid');
		
		$this->getHeaderInfo(array('title' => '搜索链接'));
		
		$this->assign('language', $language);
		$this->getMyCats($language);
		$this->assign('rid', $rid);
		$this->assign('tidNow', 10);
		$this->assign('banner', $this->getAdvs(6, "banner"));
		
		$this->display();
	}
}