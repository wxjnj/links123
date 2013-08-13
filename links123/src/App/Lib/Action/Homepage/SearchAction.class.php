<?php
/**
 * @name SearchAction.class.php
 * @package Search
 * @desc 搜索相关
 * @author wangzhenguo 2013-07-05
 * @version 0.0.1
 */
class SearchAction extends CommonAction {
	/**
	 * @desc 搜索结果页
	 * @author wangzhenguo 2013-07-08
	 */
	public function index() {
		$searchType = $this->_get('search_type');
		$keyword = $this->_get('keyword');
		if (empty($searchType)) {
			$this->error("参数错误！");
		}
		$condition['url_tag_name'] = $searchType;
		$searchInfo = M("Thl")->where($condition)->find();
		if (!$searchInfo) {
			$this->error("参数未取到！");
		} else {
			$condition['id'] = $searchInfo['id'];
			M("Thl")->where($condition)->setInc("click_num");			//统计数累加
			$url = str_replace("keyword", $keyword, $searchInfo['url']);
			$this->assign('ACTION','Search');
			$this->assign('OPERATE','index');
			$this->assign('keyword', $keyword);
			$this->assign('searchInfo', $searchInfo);
        	$this->assign('subUrl', $url);
		}
        $this->display();
	}
	
	public function top() {
		$searchType = $this->_get('search_type');
		$keyword = $this->_get('keyword');
		if (empty($searchType)) {
			$this->error("参数错误！");
		}
		$condition['url_tag_name'] = $searchType;
		$searchInfo = M("Thl")->where($condition)->find();
		if (!$searchInfo) {
			$this->error("参数未取到！");
		} else {
			$this->assign('searchCategoryOn',$searchInfo['thl']);
			$this->assign('searchTitleOn',$searchInfo['url_tag_name']);
			$this->assign('searchValue', $keyword);
			$this->assign('ACTION','Search');
			$this->assign('OPERATE','top');
		}
        $this->display();
	}
}
?>