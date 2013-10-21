<?php
class EnglishLevelnameModel extends CommonModel {
	public function getCategoryLevelListBy($type = '', $title = '') {
		$condition = array();
		if (!empty($type)) {
			$condition = array("level" => $type);
		} else {
			$condition = array("level" => array("neq", "-1"));
		}
		if (!empty($title)) {
			$condition["name"] = array('like','%' . $title . '%');
		}
		$ret = $this->alias('levelname')
					->where($condition)
					->order('sort')
					->select();
		return $ret;
	}

	public function forbidCategoryById($cate_id, $status) {
		$ret = $this->where('id=' . $cate_id)->setField('status',$status);
		return $ret;
	}

	public function getCategoryInfoById($cate_id) {
		$ret = $this->where('id=' . $cate_id)->select();
		return $ret;
	}

	public function updateCatetory($catetory) {
		$data["name"]    = $catetory["name"];
		$data["level"]   = $catetory["level"];
		$data["sort"]    = $catetory["sort"];
		$data["status"]  = $catetory["status"];
		$data["updated"] = time();
		$ret = $this->where('id=' . $catetory["id"])->save($data);
		return $ret;
	}

	public function deleteCategory($cate_id) {
		$ret = $this->where('id=' . $cate_id)->delete();
		return $ret;
	}
}