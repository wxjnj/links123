<?php
/**
 * @name SearchModel.class.php
 * @db lnk_thl
 * @desc 搜索thl表相关操作
 * @author wangzhenguo 2013-07-07
 * @version 0.0.1
 */
class SearchModel extends Model {
	protected $tableName = 'thl';						//指定数据表
	
	/**
	 * @desc 获取所有的搜索名称的信息，支持分类，三维数组
	 * @return array('category'=>array(
	 * 					array('id','thl','name','url_tag_name','url','tip','sort','create_time','needkey','click_num'),
	 * 					array('id','thl','name','url_tag_name','url','tip','sort','create_time','needkey','click_num'),
	 * 				));
	 */
	public function getAll() {
		$category = $this->field('thl')->group('thl')->order('id')->select();
		$list = array();
		foreach ($category as $key=>$item) {
			$condition['thl'] = $item['thl'];
			$list[$item['thl']] = $this->where($condition)->order('sort')->select();
		}
		return $list;
	}
}