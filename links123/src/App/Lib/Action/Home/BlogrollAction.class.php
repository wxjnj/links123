<?php
/**
 * @name BlogrollAction
 * @package Home
 * @desc 友情链接
 * @author frank UPDATE 2013-08-19
 * @version 0.0.1
 */

import("@.Common.CommonAction");
class BlogrollAction extends CommonAction {
	/**
	 * @desc 友情链接页面
	 * @see AboutUsAction::index()
	 */
	public function index() {
		$outlink = M("OutsideLinks");
		$list = $outlink->order('sort ASC')->select();
		$this->assign('outlinklist', $list);
		$this->assign('banner', $this->getAdvs(3, "banner"));
		
		$this->getHeaderInfo(array('title' => '友情链接'));
		
		$this->display();
	}
}