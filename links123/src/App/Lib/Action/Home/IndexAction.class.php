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
		$announce = M("Announcement");
		$announces = $announce->where('status = 1')->order('sort ASC, create_time DESC')->select();
		
		$skins = $this->getSkins();
		
		// 我的地盘
		$myarea = M("Myarea");
		session('arealist_default', $myarea->where('mid=0')->order('sort ASC')->select());
		
		//存在用户登录，获取用户的我的地盘
		$memberAuthKey = $this->_session(C('MEMBER_AUTH_KEY'));
	
		if ($memberAuthKey) {
			
			$areaList = $myarea->where(array('mid' => $memberAuthKey))->order('sort ASC')->select();
			session('arealist', $areaList ? $areaList : session('arealist_default'));
			
			$skinId = session('skinId');
			if (!$skinId) {
				$skinId = cookie('skinId');
			}
		} else {
			
			$areaList = $this->_session('arealist');
			!empty($areaList) || session('arealist', session('arealist_default'));
			
			$skinId = cookie('skinId');
		}
		
		$this->assign("announces", $announces);
		$this->assign("skinId", $skinId);
		$this->assign("skin", $skins['skin'][$skinId]);
		$this->assign("skinList", $skins['list']);
		$this->assign("skinCategory", $skins['category']);
	
		$this->getHeaderInfo();
		$this->display('new_index');
	}
	
	/**
	 * 更新首页背景皮肤
	 * 
	 * @param skinId: 皮肤ID
	 * 
	 * @return void
	 * 
	 * @author slate date:2013-08-29
	 */
	public function updateSkin() {
		
		$skinId = intval($this->_param('skinId'));
		
		$user_id = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
		
		$result = true;
		
		if ($user_id) {
			
			$memberModel = M("member");
					
			if (!$memberModel->where(array('id' => $user_id))->setField('skin' , $skinId)) {
				
				$result = false;
			}
			
			session('skinId', $skinId);
		}
			
		cookie('skinId', $skinId, array('expire' => 0));
		
		$this->ajaxReturn($result);
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
		
		$mid = intval($this->_session(C('MEMBER_AUTH_KEY')));
		$paiLie = $this->_session('pailie');
		
		if (empty($paiLie)) {
			$paiLie = empty($mid) ? M("Variable")->where("vname='pailie'")->getField("value_int") : M("Member")->where("id = '%s'", $mid)->getField('pailie');
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
		
		if ($mid) {
			$areaList = $myarea->where("mid = '%d'", $mid)->order('sort ASC')->select();
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
	
	/**
	 * @name missPwd
	 * @desc 忘记密码
	 * @param string email
	 * @return boolean
	 */
	public function missPwd() {
		$email = $this->_param('email');
		if (empty($email)) {
			echo "邮箱丢失！";
			return false;
		}
		$mbr = M("Member");
		$mbrNow = $mbr->getByEmail($email);
	
		if ($mbrNow) {
			import("@.ORG.String");
			$password = String::randString();
			if (false !== $mbr->where("id = '%d'", $mbrNow['id'])->setField('password', md5(md5($password) . $mbrNow['salt']))) {
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
		
		$mid = intval($this->_session(C('MEMBER_AUTH_KEY')));
		$paiLie = $this->_session('pailie');
		$lan = intval($this->_param('lan'));
		$cid = intval($this->_param('cid'));
		$keyword = cleanParam($this->_param('q'));
		$pg = intval($this->_param(C('VAR_PAGE')));
		
		$condition['status'] = 1;
		if (empty($paiLie)) {
			$paiLie = empty($mid) ? M("Variable")->where("vname = 'pailie'")->getField("value_int") : M("Member")->where("id = '%s'", $mid)->getField('pailie');
			session('pailie', $paiLie);
		}
		
		if (!empty($lan)) {
			$condition['language'] = $lan;
			$this->assign('lan', $lan);
		}
		
		if (!empty($cid)) {
			$condition['category'] = $cid;
			$this->assign('cid', $cid);
		}
		
		if (!empty($keyword)) {
			$condition['_string'] = "title like '%" . $keyword . "%' or tags like '%" . $keyword . "%' or link like '%" . $keyword . "%' or intro like '%" . $keyword . "%'";
		}
		
		$category = M("Category");
		$listRows = $_SESSION['pailie'] == 1 ? 20 : 11;
		$pg = max(1, $pg);
		$rst = ($pg - 1) * $listRows;
		
		$links = D("Links");
		$data = $links->getLinks($condition, $mid, $keyword, $listRows, $rst, $category);
		
		//分页
		$count = count($data['list']);
		if ($count == 0) {
			$this->redirect("Category/index");
			exit(0);
		}
		
		if ($count > 0) {
			import("@.ORG.Page");
			$p = new Page($count, $listRows);
			$page = $p->show();
			$this->assign("page", $page);
		}
		
		$this->assign("keyword", $keyword);
		$this->assign('links', $data['aimList']);
		$this->assign('banner', $this->getAdvs(6, "banner"));
		$this->assign('thlNow', $this->_param('thl'));
		$this->assign('tidNow', $this->_param('tid'));
		$this->assign('title', '另客汇集最有影响力的搜索引擎，让你输入一次，搜遍网络！');
		$this->assign('Description', '另客独有的搜索引擎汇集给您带来特有的搜索体验。你不用离开另客就能很方便地使用众多最有影响力的搜索引擎。另客本身丰富的数据也是你寻找教育资源最好的搜索引擎。网友的分享和交流更可能让你获得意想不到的信息');
		
		$this->display();
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
			$page = intval($this->_param('p'));
			$cid = intval($this->_param('cid'));
			$grade = $this->_param('grade');
			$sort = $this->_param('sort');
			$page = $page >1 ? $page : 1;
			
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
}