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
		
		$this->assign('title', '另客网，国内领先的网上教育资源大全，众多最有影响力的搜索引擎汇集地');
		$this->assign('Description', '另客网是国内领先的网上教育资源大全，众多最有影响力的搜索引擎汇集，让您输入一次，搜遍网络。我们的语音教育资源更是独树一帜。网友的参与和贡献将让另客网内容更加丰富。我们的最终目标是为您打造一个教育信息资源丰富、形式多样、网友积极参与、互动的网上教育社区');
		$this->assign('banner', $this->getAdvs(1, "banner"));
		$this->display();
	}
}
