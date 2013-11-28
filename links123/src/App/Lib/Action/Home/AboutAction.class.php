<?php
/**
 * @name AboutAction
 * @package Home
 * @desc 关于我们
 * @author frank UPDATE 2013-08-17
 * @version 0.0.1
 */
import("@.Common.CommonAction");
class AboutAction extends CommonAction {
	
	/**
	 * @desc 关于我们页面
	 * @see AboutAction::index()
	 */
	public function index()
	{
		$variable = M("Variable");
		$Description = $variable->getByVname('Description');
		
		$this->assign('aboutCtnt', nl2br($Description['value_varchar']));
		
		$about_us = $variable->getByVname('about_us');
		$this->assign('about_us', htmlspecialchars_decode($about_us['value_varchar']));
		
		$this->getHeaderInfo(array('title' => '关于我们'));
		
		$this->assign('banner', $this->getAdvs(1, "banner"));
		$this->display();
	}
}
