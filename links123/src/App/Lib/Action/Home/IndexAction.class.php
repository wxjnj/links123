<?php
/**
 * @name IndexAction.class.php
 * @package Home
 * @desc 首页
 * @author frank UPDATE 2013-08-16
 * @version 0.0.1
 */
import("@.Common.CommonAction");
class IndexAction extends CommonAction {
	/**
	 * @desc 首页
	 * @author Frank UPDATE 2013-08-16
	 * @see CommonAction::index()
	 */
	public function index() {
		$cat = M("Category");
		import("@.ORG.String");
		
		$cid = intval($this->_param('cid')) ? : $cat->where('status=1 and level=1')->min('id');
		$lan = intval($this->_param('lan')) ? : session('lanNow') ? : 1;
		$grade = intval($this->_param('grade'));
		$sort = $this->_param('sort');
		
		if ($lan != session('lanNow')) {
			session('lanNow', $lan);
		}
		
		$rid = $this->getRoot($cid);
		$catName = $cat->where('id = %d', $cid)->getField('cat_name'); 
		$ridTip = $cat->where('id = %d', $rid)->getField('intro');
		
		$separate = "&nbsp;<span>|</span>&nbsp;";
		switch ($rid) {
			case 1:
				$aryGrade = array('1' => '初级', '1,2' => '初级' . $separate . '中级',
				 '1,2,3' => '初级' . $separate . '中级' . $separate . '高级',
				 '2' => '中级', '2,3' => '中级' . $separate . '高级', '3' => '高级');
				$grades = array('初级', '中级', '高级');
				break;
			case 4:
				$aryGrade = array('1' => '苹果', '2' => '安卓+', 
				'1,2' => '苹果' . $separate . '安卓+');
				$grades = array('苹果', '安卓+');
				break;
			default:
				$aryGrade = array();
				$grades = array();
		}
		
		$this->getLeftMenu($rid);
		
		$condition['status'] = 1;
		$condition['category'] = $cid == $rid ? array('in', $this->_getSubCats($cid)) : $cid;
		$condition['language'] = $lan;
		if ($grade) {
			$condition['grade'] = array('like', '%' . $grade . '%');
			$this->assign("grade", $grade);
		}
		
		if (empty($sort)) {
			$sort = "csort asc,sort asc";
		}
		
		$paiLie = $this->_session('pailie');
		if (empty($paiLie)) {
			$memberAuthKey = $this->_session(C('MEMBER_AUTH_KEY'));
			if (!empty($memberAuthKey)) {
				$paiLie = M("Member")->where('id = %s', $memberAuthKey)->getField('pailie');
			} else {
				$paiLie = M("Variable")->where("vname='pailie'")->getField("value_int");
			}
			session('pailie', $paiLie);
		}
		$listRows = $paiLie == 1 ? 20 : 11;
		$pg = intval($this->_param(C('VAR_PAGE'))) ? : 1;
		$rst = ($pg - 1) * $listRows;
		
		$links = new LinksFntViewModel();
		$list = $links->getLists($condition, $sort, $rst, $listRows, $rid, $aryGrade);
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
		$announces = $announce->where('status=1')->order('sort ASC, create_time DESC')->select();
		
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
		$this->assign('grades',$grades);
		
		$this->getHeader();
		$this->display();
		$this->getFooter();
	}

	/**
	 * @desc 直达网址
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
					<script type="text/javascript">
						 window.location.href="http://' . $linkNow['url'] . '";
					  </script>';
//    		header("location: http://".$linkNow['url']);
		} else {
			$data['tag'] = $condition['tag'];
			$data['update_time'] = time();
			$model->add($data);
			$this->display('../Public/directUrl');
		}
	}

	// 更新我的地盘
	public function updateArealist() {
		
		$updated = false;
		
		foreach ($_SESSION['arealist'] as $key => $value) {
			
			if ($value['url'] == $_POST['url']) {
				if ($value['id'] != $_POST['id']) {
					echo "该链接已存在！";
					return;
				}
			}
			
			if ($value['id'] == $_POST['id']) {
				$_SESSION['arealist'][$key]['web_name'] = $_POST['web_name'];
				$_SESSION['arealist'][$key]['url'] = $_POST['url'];
				$updated = true;
				break;
			}
		}
		//
//     	// 让session修改生效
//     	$i = 0;
//     	foreach ($_SESSION['arealist'] as &$value) { $i++; }
		// 保存到账号
		if (isset($_SESSION[C('MEMBER_AUTH_KEY')]) && !empty($_SESSION[C('MEMBER_AUTH_KEY')]) && intval($_SESSION[C('MEMBER_AUTH_KEY')]) > 0) {
			//
			$user_id = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
			$myarea = M("Myarea");
			$list = $myarea->where('mid=' . $user_id)->order('sort')->select();
			//
			$myarea->startTrans();
			$result = true;
			$reason = "未知";
			//
			$now = time();
			$data['mid'] = $user_id;
			$data['create_time'] = $now;
			if (empty($list)) {
				foreach ($_SESSION['arealist'] as &$value) {
					$data['web_name'] = $value['web_name'];
					$data['url'] = $value['url'];
					if (false === $myarea->add($data)) {
						$result = false;
						Log::write('新增我的地盘失败：' . $myarea->getLastSql(), Log::SQL);
						$reason = '新增我的地盘失败！';
					}
				}
			} else {
				foreach ($list as $key => $value) {
					Log::write('session：' . $_SESSION['arealist'][$key]['web_name'], Log::SQL);
					$value['web_name'] = $_SESSION['arealist'][$key]['web_name'];
					$value['url'] = $_SESSION['arealist'][$key]['url'];
					
					//web_name || url为空则不进行更改（session中arealist曾经会丢失）
					if (!$value['web_name'] || !$value['url']) continue;
					
					$value['create_time'] = $now;
					if (false === $myarea->save($value)) {
						$result = false;
						Log::write('更新我的地盘失败：' . $myarea->getLastSql(), Log::SQL);
						$reason = '保存我的地盘失败！';
					}
				}
			}
			//
			if ($result) {
				$myarea->commit();
			} else {
				$myarea->rollback();
				echo $reason;
			}
		}
		//
		if ($updated) {
			
			echo "updateOK";
		} else {
			if (!$_SESSION['arealist']) {
				
				echo "updateOK";
			} else {
				
				echo "无更新内容！";
			}
		}
	}

	//拖动我的地盘进行排序
	public function sortArealist() {
		if ($this->isAjax()) {
			//获取排序列表
			$area_list = $_POST['area'];
			$new_area_list = array();
			$sort = 1;
			//对session数据排序
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
			// 保存到账号
			if (isset($_SESSION[C('MEMBER_AUTH_KEY')]) && !empty($_SESSION[C('MEMBER_AUTH_KEY')]) && intval($_SESSION[C('MEMBER_AUTH_KEY')]) > 0) {
				//
				$user_id = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
				$myarea = M("Myarea");
				$list = $myarea->where('mid=' . $user_id)->order('id')->select();
				//
				$myarea->startTrans();
				$result = true;
				$reason = "未知";
				//
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
				//
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

	// 获取默认我的地盘
	public function getArealistDefault() {
		$_SESSION['arealist'] = $_SESSION['arealist_default'];
		$arealist_default1 = '';
		$arealist_default2 = '';
		foreach ($_SESSION['arealist_default'] as $value) {
			$arealist_default1 .= "<li title='拖动排序' url='" . $value['url'] . "' id='area_" . $value['id'] . "' mid='" . $value['id'] . "'>" . $value['web_name'] . "</li>";
			$arealist_default2 .= "<li><a href='http://" . $value['url'] . "' target='_blank' myid='" . $value['id'] . "'>" . $value['web_name'] . "</a></li>";
		}
		echo "getOK|" . $arealist_default1 . "|" . $arealist_default2;
	}

	// 保存到账号
	/*
	  public function saveArealist() {
	  if ( !isset($_SESSION[C('MEMBER_AUTH_KEY')]) || empty($_SESSION[C('MEMBER_AUTH_KEY')]) ) {
	  echo "请先登录！";
	  return false;
	  }
	  //
	  $myarea = M("Myarea");
	  $list = $myarea->where('mid='.$_SESSION[C('MEMBER_AUTH_KEY')])->order('id')->select();
	  //
	  $myarea->startTrans();
	  $result = true;
	  $reason = "未知";
	  //
	  $now = time();
	  $data['mid'] = $_SESSION[C('MEMBER_AUTH_KEY')];
	  $data['create_time'] = $now;
	  if ( empty($list) ) {
	  foreach ($_SESSION['arealist'] as &$value) {
	  $data['web_name'] = $value['web_name'];
	  $data['url'] = $value['url'];
	  if ( false === $myarea->add($data) ) {
	  $result = false;
	  $reason = '新增我的地盘失败！';
	  }
	  }
	  }
	  else {
	  foreach ($list as $key => $value) {
	  $value['web_name'] = $_SESSION['arealist'][$key]['web_name'];
	  $value['url'] = $_SESSION['arealist'][$key]['url'];
	  $value['create_time'] = $now;
	  if ( false === $myarea->save($value) ) {
	  $result = false;
	  $reason = '更新我的地盘失败！';
	  }
	  }
	  }
	  //
	  if ( $result ) {
	  $myarea->commit();
	  echo "saveOK";
	  }
	  else {
	  $myarea->rollback();
	  echo $reason;
	  }
	  }
	 */

	// 详细介绍
	public function detail() {
		//
		import("@.ORG.String");
		import("@.ORG.VideoHooks");
		//
		$id = $_REQUEST['id'];
		if (empty($id)) {
			$this->redirect(__URL__);
		}
		//
		$links = M("Links");
		$linkNow = $links->getById($id);
		$this->assign("linkTitle", $linkNow['title']);
/////// SEO Title
		$GLOBALS['_title'] = $linkNow['title'];
/////// SEOTitle
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
		//
		if (!empty($linkNow['mid'])) {
			$linkNow['nickname'] = M("Member")->where('id=' . $linkNow['mid'])->getField('nickname');
		}

		//
		$cid = $linkNow['category'];
		$this->assign("cid", $cid);
///////////////// SEO Title and Descriiption /////////////
		$GLOBALS['_description'] = strlen($linkNow['intro']) > 100 ? String::msubstr($linkNow['intro'], 0, 100) : $linkNow['intro'];
		$this->SEOTitle($cid);
///////////////// SEO Title and Descriiption /////////////
		//
		$rid = $this->getRoot($cid);
		$this->assign("rid", $rid);
		//
		$this->assign("lan", $linkNow['language']);
		$this->getLeftMenu($rid);
		//
		$listRows = 12;
		$pg = !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1;
		$rst = ($pg - 1) * $listRows;
		//
		$condition['lnk_id'] = $id;
		$comment = new CommentViewModel();
		$cmtList = $comment->where($condition)->order('create_time desc')->limit($rst . ',' . $listRows)->select();
		foreach ($cmtList as &$value) {
			$value['create_time'] = date('Y-m-d H:i', $value['create_time']);
			if (empty($value['nickname'])) {
				$value['nickname'] = "游客";
			}
			if (empty($value['face'])) {
				$value['face'] = "face.jpg";
			}
		}
		$this->assign('cmtList', $cmtList);
		// 分页
		$count = $comment->where($condition)->count('id');
		if ($count > 0) {
			import("@.ORG.Page");
			$p = new Page($count, $listRows);
			$page = $p->show_js();
			$this->assign("page", $page);
		}
		// 目录图片
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

		//session防垃圾和时间设置
		//如果提交了评论
		if (preg_match("#saveComment#i", $_SERVER['HTTP_REFERER'])){
			//下一次提交评论的最小时间戳(时间戳 +两次评论的最小间隔时间)
			$this->timestamp 	= $_SESSION['timestamp']= time()+D("WebSettings")->getwebSettings("COMMENT_BETWEEN_TIME") ;

		}
		//随机的评论框名称
		$this->comment      = $_SESSION['comment'] 		= 'comment'.rand(1000, 9999);
		$this->display();
	}

	// 公告明细
	public function ann_detail() {
		$announce = M("Announcement");
		$annNow = $announce->getById($_REQUEST['id']);
		$announce->where("id=" . intval($_REQUEST['id']))->setInc("click_num");
		$annNow['create_time'] = date('Y-m-d H:i', $annNow['create_time']);
		$annNow['content'] = nl2br($annNow['content']);
		$annNow["content"] = checkLinkUrl($annNow["content"]);
		$this->assign("annNow", $annNow);
		//
		$this->display();
	}

	/**
	 * @desc 设置排列
	 * @return boolean
	 */
	public function setPailie() {
		$val = intval($_REQUEST['val']);
		if (empty($val)) {
			echo "排列值丢失！";
			return false;
		}
		
		$_SESSION['pailie'] = $val;
		if (isset($_SESSION[C('MEMBER_AUTH_KEY')])) {
			$member = M("Member");
			if (false === $member->where("id='%s'", $_SESSION[C('MEMBER_AUTH_KEY')])->setField('pailie', $val)) {
				Log::write('设置排列失败：' . $member->getLastSql(), Log::SQL);
				echo "设置排列失败";
				return false;
			}
		}
		echo "setOK";
	}

	// 顶
	public function ding() {
		if (empty($_REQUEST['id'])) {
			echo "链接编号丢失！";
			return false;
		}
		//
		$links = M("Links");
		$linkNow = $links->getById($_REQUEST['id']);
		if (false === $links->where("link='" . $linkNow['link'] . "'")->setInc('ding')) {
			Log::write('顶失败：' . $links->getLastSql(), Log::SQL);
		} else {
			echo "dingOK";
		}
	}

	// 踩
	public function cai() {
		if (empty($_REQUEST['id'])) {
			echo "链接编号丢失！";
			return false;
		}
		//
		$links = M("Links");
		$linkNow = $links->getById($_REQUEST['id']);
		if (false === $links->where("link='" . $linkNow['link'] . "'")->setInc('cai')) {
			Log::write('踩失败：' . $links->getLastSql(), Log::SQL);
		} else {
			echo "caiOK";
		}
	}

	/**
	 * @desc 关于我们
	 */
	public function about() {
		$variable = M("Variable");
		$Description = $variable->getByVname('Description');
		$this->assign('aboutCtnt', nl2br($Description['value_varchar']));

		$this->assign('title', '另客网，国内领先的网上教育资源大全，众多最有影响力的搜索引擎汇集地');
		$this->assign('Description', '另客网是国内领先的网上教育资源大全，众多最有影响力的搜索引擎汇集，让您输入一次，搜遍网络。我们的语音教育资源更是独树一帜。网友的参与和贡献将让另客网内容更加丰富。我们的最终目标是为您打造一个教育信息资源丰富、形式多样、网友积极参与、互动的网上教育社区');
		//
		$this->assign('banner', $this->getAdvs(1, "banner"));
		//
		$this->display();
	}

	// 联系我们
	public function contact() {
		//
		$this->assign("pageNow",$this->getPage(2));
		//
		$this->display();
	}

	//验证码
	public function verify() {
		$type = isset($_GET['type']) ? $_GET['type'] : 'gif';
		import("@.ORG.Image");
		Image::buildImageVerify(3, 1, $type, 48, 28);
	}

	// 注册
	public function reg() {
		//
		$this->assign('banner', $this->getAdvs(5, "banner"));
		$this->assign('title', '还不是岛民？赶快注册另客吧，成为另客会员，你能获得会员专有的服务和资源！');
		$this->assign('Description', '注册成为另客会员，你能享受更多另客独有的资源和权利，你会不断有惊喜的发现！');
		//
		$this->display();
	}

	// 登录
	public function login() {
		//
		$this->assign('banner', $this->getAdvs(4, "banner"));
		//
		$this->assign('title', '另客岛民请登录，享受您另客岛民专有的服务');
		$this->assign('Description', '另客会员专区有众多只有会员才能享有的资源和服务');
		$this->display();
	}

	// 保存注册
	public function saveReg() {
		//
		if ($_SESSION['verify'] != md5($_POST['verify'])) {
			echo "验证码错误！";
			return false;
		}
		//
		$member = M("Member");
		//
		$data['nickname'] = trim($_POST['nickname']);
		if ($member->where('nickname = \'' . $data['nickname'] . '\'')->find()) {
			echo "该昵称已注册过了，请换一个！";
			return false;
		}
		//
		import("@.ORG.String");
		$data['salt'] = String::randString();
		$data['password'] = md5(md5($_POST['password']) . $data['salt']);
		$data['status'] = 1;
		$data['create_time'] = time();
		if (false !== $member->add($data)) {
			$_SESSION[C('MEMBER_AUTH_KEY')] = $member->getLastInsID();
			//给新增用户添加默认自留地
			$myareaModel = D("Myarea");
			$default_myarea = $myareaModel->field("web_name,url,sort")->where("mid = 0")->Group("url")->order("`sort` asc")->limit(20)->select();
			foreach ($default_myarea as $value) {
				$value['create_time'] = $data['create_time'];
				$value['mid'] = $_SESSION[C('MEMBER_AUTH_KEY')];
				$myareaModel->add($value);
			}
			$_SESSION['nickname'] = $data['nickname'];
			echo "regOK";
		} else {
			Log::write('会员注册失败：' . $member->getLastSql(), Log::SQL);
			echo "会员注册失败！";
		}
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

	// 获取目录
	private function getMyCats($flag = 1) {
		$cat = M("Category");
		$cats = $cat->field('id, cat_name, level')->where('status=1 and level=1')->order('sort asc')->select();
		foreach ($cats as &$value) {
			switch ($value['id']) {
				case 1:
					$value['grades'] = array(
						array('name' => '初级', 'value' => '1'),
						array('name' => '初级中级', 'value' => '1,2'),
						array('name' => '初级中级高级', 'value' => '1,2,3'),
						array('name' => '中级', 'value' => '2'),
						array('name' => '中级高级', 'value' => '2,3'),
						array('name' => '高级', 'value' => '3')
					);
					break;
				case 4:
					$value['grades'] = array(
						array('name' => '苹果', 'value' => '1'),
						array('name' => '安卓+', 'value' => '2'),
						array('name' => '苹果安卓+', 'value' => '1,2')
					);
					break;
			}
			//
			$value['subCats'] = $cat->field('id, cat_name, level')->where('status=1 and flag=' . $flag . ' and prt_id=' . $value['id'])->order('sort asc')->select();
		}
		$this->assign("cats", $cats);
	}

	// 推荐链接
	public function recommend() {
		//
		$links = M("Links");
		$id = $_REQUEST['id'];
		$lan = $_REQUEST['lan'];
		if (!empty($id)) {
			if (!isset($_SESSION[C('MEMBER_AUTH_KEY')]) || empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
				header("Location: " . __APP__ . "/");
			} else {
				$linkNow = $links->getById($id);
				if ($linkNow['mid'] == $_SESSION[C('MEMBER_AUTH_KEY')]) {
					$catNow = M("Category")->getById($linkNow['category']);
					$linkNow['rid'] = $catNow['prt_id'];
					$this->assign('linkNow', $linkNow);
					$lan = $linkNow['language'];
				}
			}
		} else {
			if (isset($_SESSION[C('MEMBER_AUTH_KEY')]) && !empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
				$last = $links->where('mid=' . $_SESSION[C('MEMBER_AUTH_KEY')])->order('id desc')->limit(1)->select();
				$linkNow['category'] = $last[0]['category'];
				$linkNow['rid'] = M("Category")->where('id=' . $last[0]['category'])->getField('prt_id');
				$linkNow['grade'] = $last[0]['grade'];
			}
			$linkNow['title'] = "请输入标题";
			$linkNow['link'] = "请输入链接";
			$linkNow['intro'] = "请输入简介";
			if (empty($lan)) {
				$lan = 1;
			}
			$linkNow['language'] = $lan;
			$this->assign('linkNow', $linkNow);
		}
		//
		$this->getMyCats($lan);
		//
		$this->assign('alt', $_REQUEST['alt']);
		//
		$this->assign('title', '好东西就应该和大家分享。您推荐的好东西会让另客的内容更加丰富！');
		$this->assign('Description', '分享您发现的好东西，别人也会和您分享他们的好东西，互动共享让另客教育社区更加生气蓬勃！');
		$this->display();
	}

	// 保存推荐链接
	public function saveRecommend() {
		//
		$_POST['title'] = cleanParam($_POST['title']);
		$_POST['link'] = str_replace('http://', '', cleanParam($_POST['link']));
		$_POST['intro'] = cleanParam($_POST['intro']);
		//
		$id = $_REQUEST['id'];
		$links = M("Links");
		if (!empty($id)) {
			if (!isset($_SESSION[C('MEMBER_AUTH_KEY')]) || empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
				header("Location: " . __APP__ . "/");
			} else {
				$linkNow = $links->getById($id);
				if ($linkNow['mid'] == $_SESSION[C('MEMBER_AUTH_KEY')]) {
					//
					if (false === $links->save($_POST)) {
						Log::write('链接编辑失败：' . $links->getLastSql(), Log::SQL);
						echo '链接编辑失败';
					} else {
						echo 'editOK';
					}
				} else {
					echo '这不是你上传的链接';
				}
			}
		} else {
			//
			if ($links->where('category=' . $_POST['category'] . ' and link=\'' . $_POST['link'] . '\'')->find()) {
				echo '该链接已存在';
				return false;
			}
			//
			$_POST['status'] = 0;
			$_POST['create_time'] = time();
			$_POST['mid'] = $_SESSION[C('MEMBER_AUTH_KEY')];
			if (empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
				$_POST['mid'] = -1; //游客推荐
				$_POST['recommended'] = "游客";
			} else {
				$_POST['recommended'] = getUserNickName($_SESSION[C('MEMBER_AUTH_KEY')]);
			}
			//
			if (false === $links->add($_POST)) {
				Log::write('链接提交失败：' . $links->getLastSql(), Log::SQL);
				echo '链接提交失败';
			} else {
				echo 'addOK';
			}
		}
	}

    // 保存说说
	public function saveComment() {
		if ($this->isAjax()) {
                $lnk_id = $this->_param('lnk_id');
                if (empty($lnk_id)) {
                    $this->ajaxReturn("unllid", "", false);
                    exit;
                }       

                $commentData = stripslashes($this->_param('comment'));
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
                if($commentnum >= 1)
                {
                    $this->ajaxReturn("isset", "", false);
                    exit;
                }

                //1天同一ip只能发3次
                $daystart = strtotime(date("Y-m-d", time()).' 00:00:00');
                $dayend = strtotime(date("Y-m-d", time()).' 23:59:59');

                $commentmax = $Comment->where("lnk_id=$lnk_id AND ip='$condition[ip]' AND create_time>'$daystart' AND create_time<'$dayend'")->count();
                if($commentmax >= 3)
                {
                    $this->ajaxReturn("maxflag", "", false);
                    exit;
                }

                $data = array();
                $data['lnk_id'] = $lnk_id;
                $data['mid'] = $_SESSION[C('MEMBER_AUTH_KEY')];
                $data['comment'] = $commentData;
                $data['ip'] = getIP();
                $data['create_time'] = time();
                                
                if (false === $Comment->add($data)) {
                    Log::write('说说提交失败：' . $comment->getLastSql(), Log::SQL);
                } else {
                    $links = M("Links");
                    if (false === $links->where('id=' . $data['lnk_id'])->setInc('say_num')) {
                        Log::write('增加链接说说数量失败：' . $links->getLastSql(), Log::SQL);
                    }
                }
		
                //formRequest($_SERVER['HTTP_REFERER'],array('timestamp'=>time()));
                
                        
			$this->ajaxReturn("success", "", true);
		}
	}

	// 建议投诉
	public function suggestion() {
		//
		import("@.ORG.String");
		//
		$listRows = 100;
		$pg = !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1;
		$rst = ($pg - 1) * $listRows;
		//
//        $condition['_string'] = 'pid is null';
		$condition['pid'] = 0;
		$condition['status'] = array('egt', 0);
		//
		$sugView = new SuggestionViewModel();
		$list = $sugView->where($condition)->order('create_time desc')->limit($rst . ',' . $listRows)->select();
		$total = count($list);
		foreach ($list as $key => &$value) {
			/*
			  if ( mb_strlen($value['suggest'], 'utf-8') > 240 ) {
			  //$value['ssuggest'] = String::msubstr($value["suggest"], 0, 112);
			  //$value["ssuggest"] = nl2br($value["ssuggest"]);
			  $value["ssuggest"] = $value["suggest"];
			  $value["ssuggest"] = checkLinkUrl($value["ssuggest"]);
			  }
			 */
			//$value["suggest"] = nl2br($value["suggest"]);
			//$value["suggest"] = checkLinkUrl($value["suggest"]);
			$list[$key]['number'] = $total - $key;
			$list[$key]['reply'] = $sugView->getSuggestionReplyList($value['id']);
//                    where("is_reply=1 and pid={$list[$key]['id']}")->order('create_time desc')->select();
			if (false === $list[$key]['reply']) {
				unset($list[$key]['reply']);
			}
			$value['create_time'] = date('Y-m-d H:i', $value['create_time']);
			if ($value['mid'] == -1) {
				$list[$key]['nickname'] = "另客";
			} else if ($value['mid'] == 0 || empty($value['nickname'])) {
				$list[$key]['nickname'] = "游客";
			} else {
				if ($value['mid'] == $_SESSION[C('MEMBER_AUTH_KEY')]) {
					$value['editable'] = "1";
				}
			}
			if (empty($value['face'])) {
				$value['face'] = "face.jpg";
			}
			/*
			  $value['goon'] = 0;
			  if ( isset($_SESSION[C('MEMBER_AUTH_KEY')]) && $_SESSION[C('MEMBER_AUTH_KEY')] == $value['mid'] ) {
			  $value['goon'] = 1;
			  }
			  //
			  $value['subsug'] = $sugView->where('suggestion.status>=0 and pid='.$value['id'])->order('create_time asc')->select();
			  if ( !empty($value['subsug']) ) {
			  foreach ($value['subsug'] as &$val) {
			  if ( mb_strlen($val['suggest'], 'utf-8') > 240 ) {
			  $val['ssuggest'] = String::msubstr($val["suggest"], 0, 112);
			  //$val["ssuggest"] = nl2br($val["ssuggest"]);
			  }
			  //$val["suggest"] = nl2br($val["suggest"]);
			  $val["suggest"] = checkLinkUrl($val["suggest"]);
			  $val['create_time'] = date('Y-m-d H:i', $val['create_time']);
			  if ( empty($val['nickname']) ) {
			  $val['nickname'] = "游客";
			  }
			  if ( empty($val['face']) ) {
			  $val['face'] = "face.jpg";
			  }
			  if ( $_SESSION[C('MEMBER_AUTH_KEY')] == $val['mid'] ) {
			  $val['goon'] = 1;
			  }
			  }
			  }
			 */
		}
		$this->assign('suglist', $list);
		// 分页
		$count = $sugView->where($condition)->count('suggestion.id');
		if ($count > 0) {
			import("@.ORG.Page");
			$p = new Page($count, $listRows);
			$page = $p->show_front();
			$this->assign("page", $page);
			$this->assign("count", $count);
		}
		//
		$this->assign('banner', $this->getAdvs(2, "banner"));
		//
		$this->assign('title', '您的意见对另客非常重要！让我们一起努力，让另客变得更好！');
		$this->assign('Description', '另客的成功离不开您的意见和建议。我们欢迎您给我们提意见，我们将据此不断改进，为您提供更好的服务。');
		//
		$this->display();
	}

	// 保存建议投诉
	public function saveSuggestion() {
		//
		$suggestion = M("Suggestion");
		$data = array();
		/*
		  $data['type'] = $_POST['type'];
		  if ( isset($_POST['pid']) && !empty($_POST['pid']) ) {
		  $data['pid'] = $_POST['pid'];
		  }
		 */
		$operate = "留言";
		if (isset($_POST['reply_id']) && intval($_POST['reply_id']) > 0) {
			$data['pid'] = intval($_POST['reply_id']);
			$data['is_reply'] = 1;
			$operate = "点评";
		}
		$data['mid'] = $_SESSION[C('MEMBER_AUTH_KEY')];
		$data['suggest'] = stripslashes($_POST['suggest']);
		$data['create_time'] = time();
		if (false === $suggestion->add($data)) {
			Log::write($operate . '提交失败：' . $suggestion->getLastSql(), Log::SQL);
			$this->ajaxReturn("", $operate . "提交失败", false);
//            echo "留言提交失败！";
		} else {
			if ($data['pid'] > 0) {
				$suggestion->where("id={$data['pid']}")->setField("create_time", time());
			}
			$this->ajaxReturn("", $operate . "成功", true);
		}
	}

	public function updateSuggestion() {
		if ($this->isPost()) {
			$data = array();
			$data['id'] = intval($_POST['id']);
			$data['create_time'] = time();
			$data['suggest'] = stripslashes($_POST['content']);
			$mod = M("Suggestion");
			$suggestion_info = $mod->find($data['id']);
			if (false === $suggestion_info || empty($suggestion_info)) {
				$this->ajaxReturn("", "留言不存在", false);
			}
			if ($_SESSION[C('MEMBER_AUTH_KEY')] != $suggestion_info['mid']) {
				$this->ajaxReturn("", "对不起，你不能编辑他人的留言", false);
			}
			if (empty($data['suggest'])) {
				$this->ajaxReturn("", "留言不能为空", false);
			}
			if (false === $mod->save($data)) {
				$this->ajaxReturn("", "编辑失败", false);
			}
			$this->ajaxReturn("", "编辑成功", true);
		}
	}

	// 友情链接
	public function blogroll() {
		//
		$outlink = M("OutsideLinks");
		$list = $outlink->order('sort')->select();
		$this->assign('outlinklist', $list);
		//
		$this->assign('banner', $this->getAdvs(3, "banner"));
		//
		$this->display();
	}

	// 搜索
	public function search() {
		//
		import("@.ORG.String");
		$category = M("Category");
		//
		$condition = array();
		$condition['status'] = 1;
		//
		$lan = $_REQUEST['lan'];
		if (!empty($lan)) {
			if (!is_numeric($lan)) {
				$this->error("非法参数lan！");
			}
			//
			$condition['language'] = $lan;
			$this->assign('lan', $lan);
		}
		//
		$cid = $_REQUEST['cid'];
		if (!empty($cid)) {
			if (!is_numeric($cid)) {
				$this->error("非法参数cid！");
			}
			//
			$condition['category'] = $cid;
			$this->assign('cid', $cid);
		}
		//
		$keyword = cleanParam($_REQUEST['q']);
		if (!empty($keyword)) {
			$condition['_string'] = "title like '%" . $keyword . "%' or tags like '%" . $keyword . "%' or link like '%" . $keyword . "%' or intro like '%" . $keyword . "%'";
		}
		$this->assign("keyword", $keyword);
		//
		if ($_SESSION['pailie'] == 1) {
			$listRows = 20;
		} else {
			$listRows = 11;
		}
		$pg = $_REQUEST[C('VAR_PAGE')];
		if (empty($pg)) {
			$pg = 1;
		} else {
			if (!is_numeric($pg)) {
				$this->error("非法参数p！");
			}
		}
		$rst = ($pg - 1) * $listRows;
		//
		$links = M("Links");
		$list = $links->where($condition)->select();
		//echo $links->getLastSql();
		//
		$model = new Model();
		if (isset($_SESSION[C('MEMBER_AUTH_KEY')]) && !empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
			$dpList = $model->query("select web_name as title, url as link, 'myarea.jpg' as logo, '我的地盘' as intro, '1' as notlink from lnk_myarea where (mid=0 or mid=" . $_SESSION[C('MEMBER_AUTH_KEY')] . ") and (web_name like '%" . $keyword . "%' or url like '%" . $keyword . "%')");
		} else {
			$dpList = $model->query("select web_name as title, url as link, 'myarea.jpg' as logo, '我的地盘' as intro, '1' as notlink from lnk_myarea where mid=0 and (web_name like '%" . $keyword . "%' or url like '%" . $keyword . "%')");
		}
		if (!empty($dpList)) {
			$list = array_merge($list, $dpList);
		}
		//
		$lybList = $model->query("select '留言板' as title, 'www.links123.cn/Index/suggestion' as link, 'lyb.jpg' as logo, suggest as intro, '1' as notlink from lnk_suggestion where status>=0 and suggest like '%" . $keyword . "%'");
		if (!empty($lybList)) {
			$list = array_merge($list, $lybList);
		}
		//
		$sayList = $model->query("select '说说' as title, CONCAT('www.links123.cn/Index/detail/id/',lnk_id) as link, 'say.jpg' as logo, comment as intro, '1' as notlink from lnk_comment a inner join lnk_links b on a.lnk_id=b.id where comment like '%" . $keyword . "%'");
		if (!empty($sayList)) {
			$list = array_merge($list, $sayList);
		}
		//
		$aimList = array();
		for ($i = 0; $i != $listRows; ++$i) {
			if (!empty($list[$i + $rst])) {
				array_push($aimList, $list[$i + $rst]);
			}
		}
		//
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
					$value["sintro"] = str_replace("<br />
<br />", "", $value["sintro"]); // 特意写成这样的
					$value["sintro"] = str_replace("<br />
 <br />", "", $value["sintro"]); // 特意写成这样的
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
				//
				if (!empty($value['mid'])) {
					$value['nickname'] = M("Member")->where('id=' . $value['mid'])->getField('nickname');
				}
				//
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
			$page = $p->show_js();
			$this->assign("page", $page);
		}
		//
		$this->assign('banner', $this->getAdvs(6, "banner"));
		//
		$this->assign('thlNow', $_REQUEST['thl']);
		$this->assign('tidNow', $_REQUEST['tid']);
		//
		$this->assign('title', '另客汇集最有影响力的搜索引擎，让你输入一次，搜遍网络！');
		$this->assign('Description', '另客独有的搜索引擎汇集给您带来特有的搜索体验。你不用离开另客就能很方便地使用众多最有影响力的搜索引擎。另客本身丰富的数据也是你寻找教育资源最好的搜索引擎。网友的分享和交流更可能让你获得意想不到的信息');
		//
		$this->display();
	}

	//
	public function category() {
		//
		$language = $_REQUEST['lan'];
		$this->assign('language', $language);
		//
		$this->getMyCats($language);
		//
		$this->assign('rid', $_REQUEST['rid']);
		//
		$this->assign('tidNow', 10);
		//
		$this->assign('banner', $this->getAdvs(6, "banner"));
		//
		$this->display();
	}

	///////////////////////////////// Andrew's Code for SEO //////////////////////
	public function SEOTitle($catid) {
		///////////////////////////////// Andrew's Coe for SEO Page Title /////////////////////////////////////////
		//default title	
		$variable = M("Variable");
		$title = $variable->getByVname('title');
		//current category
		$cat = M("Category");
		if ($_REQUEST['cid']) {
			if ($_SESSION['catNow'])
				$_SESSION['catNow'] = null;
			$cid = $_REQUEST['cid'];
			$_SESSION['catNow'] = $cat->getById($cid);
		} else if (!$_SESSION['catNow'] || $_SERVER['REQUEST_URI'] == '/') {
			$cid = $cat->where('status=1 and level=1')->min('id');
			$_SESSION['catNow'] = $cat->getById($cid);
		}
		//		echo "<label style='display:none'>".$catNow['cat_name']."</label>";
		if ($catid) {
			////////////////detail page ///////////////
			$_SESSION['catNow'] = $cat->getById($catid);
			$_title = $GLOBALS['_title'] . ' | ' . $_SESSION['catNow']['cat_name'] . ' | 另客网';
		} else {
			/////////////////// List page/////////////////////
			$_firstTiles3 = $GLOBALS['firstTiles3'][0] . ($GLOBALS['firstTiles3'][1] ? '、' . $GLOBALS['firstTiles3'][1] . ($GLOBALS['firstTiles3'][2] ? '、' . $GLOBALS['firstTiles3'][2] : '') : '');
			//echo "<label style='display:none'>".$_firstTiles3."</label>";
			import("@.ORG.Page");
			$p = new Page();
			if ($p->getCurrentPage() > 1) {
				$_title = $title['value_varchar'] . ' - ' . $_SESSION['catNow']['cat_name'] . ' 第' . $p->getCurrentPage() . '页';
			} else {
				$_title = $title['value_varchar'] . ' - ' . $_SESSION['catNow']['cat_name'];
			}
			//description of list page
			$Description = $variable->getByVname('Description');
			$GLOBALS['_description'] = $Description['value_varchar'] . '。本页热门网站有：' . $_firstTiles3;
		}
		$this->assign('title', $_title);
		$this->assign('Description', strip_tags(str_replace("\n","",$GLOBALS['_description'])));

		///////////////////////////////// Andrew's Coe for SEO Page Title /////////////////////////////////////////
	}

	///////////////////////////////////// Andrew's Code for Front Search Tips ////////////////////////////////////////////	
	public function searchTips() {
		$model = M('SearchTip');
		$condition = array();
		$condition['tip_content'] = array('like', trim($_REQUEST['search_text']) . '%');

		//echo $model->getLastSql();
		$tips = $model->where($condition)->select();
		if (count($tips) > 0) {
			$tiplist = '<li style="height:1px; width:1px; z-index:-1; position:relative"><input type="text" id="li0" style="height:1px" size="1" /></li>';
			for ($i = 0; $i < count($tips); $i++) {
				$tiplist .= '<li style="background:#fff"><input type="text" readonly id="li' . ($i + 1) . '" style="border:none; padding:0 0 0 4px; width:351px;cursor:pointer;z-index:' . ($i + 1) . '" value=' . $tips[$i]['tip_content'] . ' class="valid" /></li>';
				//$tiplist.='<li id="li'.($i+1).'" style="background:#fff; padding:0 0 0 4px; width:350px;cursor:pointer">'.$tips[$i][0].'</li>';
			}
			//$tiplist.='<input type="text" readonly id="li.'.($i+1).' style="display:none;border:none;width:1px;float:left" size="1" />';
			echo $tiplist;
		} else {
			echo - 1;
		}

		/////////////////////add new search entry or update existing records
		if ($_REQUEST['sbmt']) {
			//
			$condition['tip_content'] = array('=', '"' . trim($_REQUEST['search_text']) . '"');
			$tips = $model->where($condition)->select();
			if (count($tips) > 0) {
				/// update weight of existing record
				$model->__set('tip_weight', 1 + $tips[0]['tip_weight']);
				$model->save();
			} else {
				//addd new record
				$GLOBALS['_sql'] = 'insert into lnk_search_tip (tip_content) values ("' . trim($_REQUEST['search_text']) . '")';
				$model->add();
			}
			/////////////////add new search entry or update existing records
		}
	}

	/**
	 * @desc 连接导向
	 * @author Frank UPDATE 2013-08-17
	 */
	public function link_out() {
		$url = $_REQUEST['url'];
		if (empty($url)) {
			$this->error("对不起，链接不存在！");
		}
		if ($_REQUEST['mod'] == "myarea") {
			$mid = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
			$mod = D("Myarea");
			$mod->where("mid='%d' and url='%s'", $mid, $url)->setInc("click_num");
		} else {
			$linkModel = D("Links");
			$linkModel->where("link='%s'", $url)->setInc("click_num");
		}
		
		echo '<style type="text/css">a{display:none}</style>
			  <script src="http://s96.cnzz.com/stat.php?id=4907803&web_id=4907803" language="JavaScript"></script>
			  <script type="text/javascript">
			  window.location.href="http://' . $url . '";
			  </script>';
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
			$p = (int)$this->_param('p');
			$cid = intval($this->_param('cid'));
			$grade = $this->_param('grade');
			$sort = $this->_param('sort');
			$page = $p > 0 ? $p : 1;
			
			if ($lan <= 0) {
				$lan = $this->_session('lanNow');
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
		$srcLang = $_POST['sl'];
		$tatLang = $_POST['tl'];
		$q = urlencode(trim($_POST['q']));
		
		$url = 'http://translate.google.cn/translate_a/t?client=t&hl=zh-CN&sl=' . $srcLang . '&tl=' . $tatLang . '&ie=UTF-8&oe=UTF-8&multires=1&oc=1&prev=conf&psl=en&ptl=vi&otf=1&it=sel.166768%2Ctgtd.2118&ssel=4&tsel=4&sc=1&q=' . $q;
		$result = file_get_contents($url);
		$this->ajaxReturn($result, '', true);
	}
	
	public function test0619() {
		set_time_limit(1000);
		import("@.ORG.VideoDownload");
		$videoDownload = new VideoDownload();
		//$question['media_text_url'] = trim(str_replace(' ', '', $question['media_text_url']));
		$videoInfo = $videoDownload->download("http://www.peepandthebigwideworld.com/activities/anywhere-activities/whathappens/");
		if (!$videoInfo) {
			var_dump($videoDownload->getError());
		}
		var_dump($videoInfo);
	}
	
}