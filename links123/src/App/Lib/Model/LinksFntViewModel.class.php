<?php
/**
 * @package Model
 * @name LinksFntViewModel.class.php
 * @desc 链接视图模型(前台)
 * @author Frank UPDATE 2013-08-16
 * @return array
 */
class LinksFntViewModel extends ViewModel {

    public $viewFields = array(
        'links' => array('id', 'title', 'logo', 'category', 'language', 'link', 'intro', 'grade', 'create_time', 'status', 'say_num', 'collect_num', 'sort', 'uid', 'ding', 'cai', 'mid', 'recommended', '_type' => 'LEFT'),
        'category' => array('cat_name', 'prt_id', 'sort' => 'csort', '_on' => 'links.category=category.id')
    );
    /**
     * @desc 获取链接列表
     * @param unknown_type $condition
     * @param unknown_type $sort
     * @param unknown_type $rst
     * @param unknown_type $listRows
     * @param unknown_type $rid
     * @param unknown_type $aryGrade
     * @return unknown
     */
    public function getLists($condition, $sort, $rst, $listRows, $rid, $aryGrade)
    {
    	$list = $this->where($condition)->order($sort)->limit($rst . ',' . $listRows)->select();
    	$paiLie = session('pailie');
    	foreach ($list as &$value) {
    		$value["more"] = 0;
    		if (empty($value["logo"])) {
    			if ($paiLie == 1) {
    				$value["sintro"] = String::msubstr($value["intro"], 0, 19);
    			} else {
    				$value["sintro"] = String::msubstr($value["intro"], 0, 208);
    				if ($value["sintro"] != $value["intro"]) {
    					$value["sintro"] = String::msubstr($value["intro"], 0, 150);
    					$value["more"] = 1;
    				}
    			}
    		} else {
    			if ($paiLie == 1) {
    				if ($rid == 5) {
    					$value["sintro"] = String::msubstr($value["intro"], 0, 20);
    				} else {
    					$value["sintro"] = String::msubstr($value["intro"], 0, 13);
    				}
    			} else {
    				$value["sintro"] = String::msubstr($value["intro"], 0, 184);
    				if ($value["sintro"] != $value["intro"]) {
    					$value["sintro"] = String::msubstr($value["intro"], 0, 132);
    					$value["more"] = 1;
    				}
    			}
    		}
    		if ($paiLie == 2) {
    			$value["sintro"] = nl2br($value["sintro"]);
    			$value["sintro"] = preg_replace('/(<br\s*\/>)/i', '', $value["sintro"]);
    			$tempary = explode("<br />", $value["sintro"]);
    			if (count($tempary) > 4) {
    				$lastline = String::msubstr($tempary[2], 0, 40);
    				if ($lastline == $tempary[2]) {
    					$lastline .= "…";
    				}
    				$value["sintro"] = $tempary[0] . "<br />" . $tempary[1] . "<br />" . $lastline;
    				$value["more"] = 1;
    			}
    			if (count($tempary) == 3 || (count($tempary) == 4 && $value["more"] == 1)) {
    				$lastline = String::msubstr($tempary[2], 0, 40);
    				$value["sintro"] = $tempary[0] . "<br />" . $tempary[1] . "<br />" . $lastline;
    				if (count($tempary) == 4) {
    					$value["sintro"] .= "…";
    				}
    			}
    			if (count($tempary) == 2) {
    				if ($value["more"] == 1) {
    					if (strlen($tempary[1]) < strlen($tempary[0])) {
    						$lastline = String::msubstr($tempary[1], 0, 40);
    						$value["sintro"] = $tempary[0] . "<br />" . $lastline;
    					}
    				} else {
    					$value["sintro"] = $tempary[0] . "<br />" . $tempary[1];
    				}
    			}
    			$value["sintro"] = checkLinkUrl($value["sintro"]);
    		}
    		// 防采集
    		$rand = randString();
    		$tempstr = $rand['randstr'];
    		$value["linkTitle"] = $value["title"];
    		$value["title"] = $value["title"] . $tempstr;
    		$value["sintro"] = $value["sintro"] . $tempstr;
    		$value['grade_name'] = $aryGrade[$value['grade']];
    		empty($value['mid']) || $value['nickname'] = M("Member")->where("id='%d'", $value['mid'])->getField('nickname');
    	}
    	return $list;
    }
}
?>

