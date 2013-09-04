<?php
// 链接模型
class LinksModel extends CommonModel {
	public $_validate =	array(
		array('title', 'require', '标题必须'),
		array('link', 'require', '链接必须'),
		array('language', 'require', '语言必须'),
		array('intro', 'require', '简介必须'),
	);
	
	public $_auto =	array(
		array('create_time', 'time', self::MODEL_INSERT, 'function'),
	);
	/**
	 * @desc 另客搜索链接
	 */
	public function getLinks($condition, $mid, $keyword, $listRows, $rst, $category)
	{
		$list = $this->where($condition)->select();
		$data['list'] = $list;
		if(count($list) == 0) {
			return $data;
		}
		
		$model = new Model();
		$flag = $mid ? " or mid='$mid'" : '';
		$sql = "select web_name as title, url as link, 'myarea.jpg' as logo, '我的地盘' as intro, '1' as notlink from lnk_myarea 
				where (mid=0".$flag.") and (web_name like '%" . $keyword . "%' or url like '%" . $keyword . "%')";
		$dpList = $model->query($sql);
		if (!empty($dpList)) {
			$list = array_merge($list, $dpList);
		}
		
		$lybList = $model->query("select '留言板' as title, 'www.links123.cn/Index/suggestion' as link, 'lyb.jpg' as logo, suggest as intro, 
				'1' as notlink from lnk_suggestion where status>=0 and suggest like '%" . $keyword . "%'");
		if (!empty($lybList)) {
			$list = array_merge($list, $lybList);
		}
		
		$sayList = $model->query("select '说说' as title, CONCAT('www.links123.cn/Detail/index/id/',lnk_id) as link, 'say.jpg' as logo, comment as intro, 
				'1' as notlink from lnk_comment a inner join lnk_links b on a.lnk_id=b.id where comment like '%" . $keyword . "%'");
		if (!empty($sayList)) {
			$list = array_merge($list, $sayList);
		}
		
		$aimList = array();
		for ($i = 0; $i != $listRows; ++$i) {
			if (!empty($list[$i + $rst])) {
				array_push($aimList, $list[$i + $rst]);
			}
		}
		
		foreach ($aimList as &$value) {
			if (empty($value['notlink'])) {
				$value["more"] = 0;
				if (empty($value["logo"])) {
					if ($_SESSION['pailie'] == 1) {
						$value["sintro"] = String::msubstr($value["intro"], 0, 19);
					} else {
						$value["sintro"] = String::msubstr($value["intro"], 0, 208);
						if ($value["sintro"] != $value["intro"]) {
							$value["sintro"] = String::msubstr($value["intro"], 0, 150);
							$value["more"] = 1;
						}
					}
				} else {
					if ($_SESSION['pailie'] == 1) {
						$value["sintro"] = String::msubstr($value["intro"], 0, 13);
					} else {
						$value["sintro"] = String::msubstr($value["intro"], 0, 184);
						if ($value["sintro"] != $value["intro"]) {
							$value["sintro"] = String::msubstr($value["intro"], 0, 132);
							$value["more"] = 1;
						}
					}
				}
				if ($_SESSION['pailie'] == 2) {
					$value["sintro"] = nl2br($value["sintro"]);
					$value["sintro"] = str_replace("<br /><br />", "", $value["sintro"]); // 特意写成这样的
					$value["sintro"] = str_replace("<br /><br />", "", $value["sintro"]); // 特意写成这样的
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
		
				if (!empty($value['mid'])) {
					$value['nickname'] = M("Member")->where('id=' . $value['mid'])->getField('nickname');
				}
					
				$cat = $category->getById($value['category']);
				$root = $category->getById($cat['prt_id']);
				$value['title'] = $value['title'] . "【" . $root['cat_name'] . "-" . $cat['cat_name'] . "】";
		
				//取出prt_id，当prt_id=5即当前结果为TED，连接修改为本地播放
				$value['prt_id'] = $cat['prt_id'];
			} else {
				$value["sintro"] = String::msubstr($value["intro"], 0, 240);
			}
		}
		$data['aimList'] = $aimList;
		
		return $data;
	} 
}
?>
