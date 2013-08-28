<?php
/**
 * @name DirectAction
 * @desc 链接导向
 * @package Home
 * @version 1.0
 * @author frank UPDATE 2013-08-27
 */
import("@.Common.CommonAction");
class LinkAction extends CommonAction {
	/**
	 * @desc 连接导向
	 * @name index
	 * @param string mod
	 * @param string url
	 * @author Frank UPDATE 2013-08-17
	 */
	public function index() {
		$url = $this->_param('url');
		$mod = $this->_param('mod');
		
		if (empty($url)) {
			$this->error("对不起，链接不存在！");
		}
		$flag = 0;
		if ($mod == "myarea") {
			$mid = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
			$myarea = D("Myarea");
			$flag = $myarea->where("mid = '%d' and url = '%s'", $mid, $url)->setInc("click_num");
		} else {
			$linkModel = D("Links");
			$flag = $linkModel->where("link = '%s'", $url)->setInc("click_num");
		}
		
		echo '<style type="text/css">a{display:none}</style>
				<script src="http://s96.cnzz.com/stat.php?id=4907803&web_id=4907803" language="JavaScript"></script>
				<script type="text/javascript">window.location.href="' . (strpos ($url, 'http://')===FALSE && strpos ($url, 'https://')===FALSE ? 'http://' . $url : $url) . '";</script>';
		
	}
	
	/**
	 * @name direct
	 * @desc 直达网址
	 * @param string tag
	 * @author Frank UPDATE 2013-08-17
	 */
	public function direct() {
		$tag = cleanParam($this->_param('tag'));
		$tag = str_replace('。', '.', $tag);
		$len = strlen($tag);
		if ((strpos($tag, '网') == ($len - 3)) && $len > 6) {
			$tag = substr($tag, 0, $len - 3);
		}
		$condition['tag'] = $tag;
		$condition['status'] = 1;
	
		$model = M("DirectLinks");
		$linkNow = $model->where($condition)->find();
		if ($linkNow) {
			$model->where("id={$linkNow['id']}")->setInc("click_num");
			echo '<style type="text/css">a{display:none}</style>
				  <script src="http://s96.cnzz.com/stat.php?id=4907803&web_id=4907803" language="JavaScript"></script>
				  <script type="text/javascript">window.location.href="http://' . $linkNow['url'] . '";
				  </script>';
		} else {
			$data['tag'] = $condition['tag'];
			$data['update_time'] = time();
			$model->add($data);
			$this->display('../Public/directUrl');
		}
	}
}