<?php
/**
* 英语角类目逻辑类
* author reasono
*/
class EnglishLevelnameLogic {
	public function getCategoryLevelListBy($type, $title) {
		$category = D('EnglishLevelname');
		$ret = $category->getCategoryLevelListBy($type, $title);

		return $ret;
	}

	public function forbidCategoryById($cate_id) {
		$category = D('EnglishLevelname');
		$ret = $category->forbidCategoryById($cate_id, 0);

		return $ret;
	}

	public function resumeCategoryById($cate_id) {
		$category = D('EnglishLevelname');
		$ret = $category->forbidCategoryById($cate_id, 1);

		return $ret;
	}

	public function getCategoryInfoById($cate_id) {
		$category = D('EnglishLevelname');
		$ret = $category->getCategoryInfoById($cate_id);

		return $ret;
	}

	public function updateCatetory($catetory) {
		$category = D('EnglishLevelname');
		$ret = $category->updateCatetory($catetory);

		return $ret;
	}

	public function deleteCategory($cate_id) {
		$category = D('EnglishLevelname');
		$ret = $category->deleteCategory($cate_id);

		return $ret;
	}
}