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
	 * @desc index V4
	 * 
	 * @author slate date:2013-10-07
	 */
	public function indexV4() {
        
        //气象数据
        $weather = A('Home/Weather');
        $data = json_decode($weather->getJsonData());
        $weatherData = array(
            'city' => $data->n,
            'temp' => $data->t,
            'sun'  => $data->s,
            'air'  => $data->i->aq->label
        );        
        $this->assign('weatherData', $weatherData);
        
		//自留地
		$myareaModel = M("Myarea");
		$scheduleModel = M("Schedule");

		$user_id = intval($this->_session(C('MEMBER_AUTH_KEY')));
		if ($user_id) {
			$memberModel = M("Member");
			$mbrNow = $memberModel->where(array('id' => $user_id))->find();
			$_SESSION['myarea_sort'] = $mbrNow['myarea_sort'] ? explode(',', $mbrNow['myarea_sort']) : '';
			$_SESSION['app_sort'] = $mbrNow['app_sort'];

			//取出皮肤ID和模板ID
			$skinId = session('skinId');
			if (!$skinId) {
				$skinId = cookie('skinId');
			}

			$themeId = session('themeId');
			if (!$themeId) {
				$themeId = cookie('themeId');
			}
		} else {

			//取出皮肤ID和模板ID
			$skinId = session('skinId');
			if (!$skinId) {
				$skinId = cookie('skinId');
			}
			$themeId = session('themeId');
			if (!$themeId) {
				$themeId = cookie('themeId');
			}

			if (!$_SESSION['app_sort']) {

				$_SESSION['app_sort'] = cookie('app_sort');
			}
		}
        
		//快捷皮肤
		$skins = $this->getSkins();
		if ($skinId) {
			$skin = $skins['skin'][$skinId]['skinId'];
			if (!$skin) $skinId = '';
			$this->assign("skinId", $skinId);
			$this->assign("skin", $skin);
		}
		$this->assign("skinList", $skins['list']);
		$this->assign("skinCategory", $skins['category']);

		$this->assign('themeId', $themeId);

		$app_list = $this->getApps($_SESSION['app_sort']);
		$this->assign('app_list', $app_list);
		$this->getHeaderInfo();
		$this->display('index_v4');
	}
	
	/**
	 * @desc 新首页
	 *
	 * @author slate date:2013-09-06
	 */
	public function index() {
		
		//自留地
		$myareaModel = M("Myarea");
		$scheduleModel = M("Schedule");

		$user_id = intval($this->_session(C('MEMBER_AUTH_KEY')));
		if ($user_id) {
			$memberModel = M("Member");
			$mbrNow = $memberModel->where(array('id' => $user_id))->find();
			$_SESSION['myarea_sort'] = $mbrNow['myarea_sort'] ? explode(',', $mbrNow['myarea_sort']) : '';
			$_SESSION['app_sort'] = $mbrNow['app_sort'];
			
			//取出皮肤ID和模板ID
			$skinId = session('skinId');
			if (!$skinId) {
				$skinId = cookie('skinId');
			}
			
			$themeId = session('themeId');
			if (!$themeId) {
				$themeId = cookie('themeId');
			}
		} else {
			
			//取出皮肤ID和模板ID
			$skinId = session('skinId');
			if (!$skinId) {
				$skinId = cookie('skinId');
			}
			$themeId = session('themeId');
			if (!$themeId) {
				$themeId = cookie('themeId');
			}
			
			if (!$_SESSION['app_sort']) {
				
				$_SESSION['app_sort'] = cookie('app_sort');
			}
		}
		
		//快捷皮肤
		$skins = $this->getSkins();
		if ($skinId) {
			$skin = $skins['skin'][$skinId]['skinId'];
			if (!$skin) $skinId = '';
			$themeId = $skins['skin'][$skinId]['themeId'];
			$this->assign("skinId", $skinId);
			$this->assign("skin", $skin);
		}
		$this->assign("skinList", $skins['list']);
		$this->assign("skinCategory", $skins['category']);
		
		$theme = $this->getTheme($themeId);
		$this->assign('theme', $theme);
		
		//自留地数据
		if ($user_id || !$_SESSION['arealist']) {	
			$areaList = $myareaModel->where(array('mid' => $user_id))->select();
			
			if ($areaList) {
				$_SESSION['arealist'] = array();
			}
			
			foreach ($areaList as $value) {
				$_SESSION['arealist'][$value['id']] = $value;
			}
		}
		if (!$_SESSION['myarea_sort']) {
			
			$_SESSION['myarea_sort'] = array_keys($_SESSION['arealist']);
		}
	
		//日程表
		if ($user_id) {
			
			$schedule_list = $scheduleModel->where(array('mid' => $user_id, 'status' => 0))->select();
		} else {
			
			$schedule_list = cookie(md5('schedule_list'));
			if (!$schedule_list[0]) {
				$schedule_list = $scheduleModel->where(array('mid' => 0, 'status' => 0))->select();
			}
			
			cookie(md5('schedule_list'), $schedule_list);
		}
		
		if (!$schedule_list[0]['datetime']) {
			$schedule_list[0]['datetime'] = time();
			$schedule_list[0]['content'] = '快来创建第一个日程';
		}
		
		$this->assign('schedule_list', $schedule_list);
		
		//热门音乐
		$songList = $this->getDayhotMusic();
		shuffle($songList['top']);
		shuffle($songList['fair']);
		$songTopList = array_chunk($songList['top'], 2, true);
		$songTopList = $songTopList[0];
		$songFairList = array_chunk($songList['fair'], 20, true);
		$songFairList = $songFairList[0];
		
		$this->assign('songTopList', $songTopList);
		$this->assign('songFairList', $songFairList);
		
		//豆瓣电影信息 0正在上映 1即将上映
// 		$movieList = $this->getDoubanMovieInfo();
// 		$nowplayingmovie = $movieList[0];
//      $latermovie = $movieList[1];
        
		//TED 发现
		$ted_list = S('ted_list');
		if (!$ted_list) {
			
			$variableModel = M('Variable');
			$linksModel = M("Links");
			
			$ted_list = array();
			$home_ted_hot_list = S('home_ted_hot');
			if (!$home_ted_hot_list) {
				
				$home_ted_hot_list = $variableModel->where(array('vname' => 'home_ted_hot'))->find();
				$home_ted_hot_list =  unserialize($home_ted_hot_list['value_varchar']);
				S('home_ted_hot', $home_ted_hot_list);
			}
			
			$ted_ids = implode(',', array_keys($home_ted_hot_list));
			$result = $linksModel->where('id in ('.$ted_ids.')')->select();
			
			$ted_list = array();
			foreach ($result as $value) {
				
				$ted_list[$value['id']] = array('id' => $value['id'], 'title' => $value['title'], 'link_cn_img' => $value['link_cn_img'], 'status' => $home_ted_hot_list[$value['id']]);
			}
					
			S('ted_list', $ted_list);
		}
		$this->assign('ted_list', $ted_list);
		
		//图片精选
		
		//APP应用
		$app_list = $this->getApps($_SESSION['app_sort']);
		$this->assign('app_list', $app_list);
		
		
		$this->getHeaderInfo();
		$this->display('index_v3');
	}
	
	/**
	 * @desc 另客导航3.0
	 * 
	 * @author slate date:2013-10-1
	 */
	public function hao() {
		
		$this->getHeaderInfo();
		$this->display('hao');
	}
	
	/**
	 * @desc 首页2.0
	 * @author slate date: 2013-08-20
	 */
	public function old_index() {
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

		}

		if ($skinId) {
			session('skinId', $skinId);
			cookie('skinId', $skinId, array('expire' => 0));
		} else {
			unset($_SESSION['skinId']);
			cookie('skinId',null);
		}

		$this->ajaxReturn(cookie('skinId'));
	}

	/**
	 * 更新首页主题
	 *
	 * @param themeId: ThemeID
	 *
	 * @return void
	 *
	 * @author slate date:2013-09-21
	 */
	public function updateSkinTheme() {

		$themeId = intval($this->_param('themeId'));

		$user_id = intval($_SESSION[C('MEMBER_AUTH_KEY')]);

		$result = true;

		if ($user_id) {

			$memberModel = M("member");

			if (!$memberModel->where(array('id' => $user_id))->setField('theme' , $themeId)) {

				$result = false;
			}

		}

		session('themeId', $themeId);
		cookie('themeId', $themeId, array('expire' => 0));

		$this->updateSkin(0);
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
			$condition['tip_content'] = array('eq', '"' . $stext . '"');
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
		//使用get，长文本翻译会出错，改为post
		$srcLang = $this->_param('sl');
		$tatLang =$this->_param('tl');
		//$q = rawurlencode(trim($_POST['q']));
		$q = trim($_POST['q']);
		$data = array ('q' => $q);
		$data = http_build_query($data);
		$opts = array (
			'http' => array (
			'method' => 'POST',
			'header'=> "Content-type: application/x-www-form-urlencoded\r\n" .
			"Content-Length: " . strlen($data) . "\r\n",
			'content' => $data
			)
		);
		$context = stream_context_create($opts);
		//$html = file_get_contents('http://localhost/e/admin/test.html', false, $context);
		$url = 'http://translate.google.cn/translate_a/t?client=t&hl=zh-CN&sl=' . $srcLang . '&tl=' . $tatLang . '&ie=UTF-8&oe=UTF-8&multires=1&oc=1&prev=conf&psl=en&ptl=vi&otf=1&it=sel.166768%2Ctgtd.2118&ssel=4&tsel=4&sc=1';//&q=' . $q;
		$result = file_get_contents($url, false, $context);
		$this->ajaxReturn($result, '', true);
	}
	
	/**
	 * @name addSchedule
	 * @desc 添加日程
	 * @param string content
	 * @param string datetime
	 * @return 成功:1; 失败:0; 未登录或登录已失效: -1
	 * @author slate date:2013-09-14
	 */
	public function addSchedule() {
	
		$content = $this->_param('content');
		$datetime = $this->_param('datetime');
	
		$user_id = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
	
		$result = 0;
	
		if ($user_id) {
			$scheduleModel = M("Schedule");
	
			$now = time();
				
			if ($datetime) {
	
				$datetime = str_replace(array('月','日'), '-', $datetime);
	
				$datetime = strtotime('2013-' . $datetime);
	
				$datetime = $datetime ? $datetime : $now;
			}
	
			$saveData = array(
					'mid' => $user_id,
					'content' => $content,
					'datetime' => $datetime,
					'status' => 0,
					'create_time' => $now,
					'update_time' => $now
			);
	
			$id = $scheduleModel->add($saveData);
				
			if ($id) {
	
				$saveData['id'] = $result = $id;
				$schedule_list = cookie(md5('schedule_list'));
				$schedule_list[$id] = $saveData;
				cookie(md5('schedule_list'), $schedule_list);
			}
		} else {
				
			$result = -1;
		}
	
		echo $result;
	}
	
	/**
	 * @name updateSchedule
	 * @desc 更新日程表
	 * @param int id
	 * @param String content
	 * @param String datetime
	 * @return 成功:1; 失败:0;
	 * @author slate date:2013-09-14
	 */
	public function updateSchedule() {
	
		$id = $this->_param('id');
		$content = $this->_param('content');
		$datetime = $this->_param('datetime');
	
		$user_id = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
	
		$result = 0;
	
		if ($id) {
				
			$now = time();
				
			if ($datetime) {
	
				$datetime = str_replace(array('月','日'), '-', $datetime);
	
				$datetime = strtotime('2013-' . $datetime);
	
				$datetime = $datetime ? $datetime : $now;
			}
	
			$saveData = array(
					'content' => $content,
					'datetime' => $datetime,
					'update_time' => $now
			);
				
			$result = 1;
				
			if ($user_id) {
				$scheduleModel = M("Schedule");
					
				if (false === $scheduleModel->where(array('id' => $id, 'mid' => $user_id))->save($saveData)) {
						
					$result = 0;
				}
			}
				
			$schedule_list = cookie(md5('schedule_list'));
			$schedule_list[$id] = array_merge($schedule_list[$id], $saveData);
			cookie(md5('schedule_list'), $schedule_list);
		}
			
		echo $result;
	}
	
	/**
	 * @name delSchedule
	 * @desc 删除日程表
	 * @param int id
	 * @return 成功:1; 失败:0;
	 * @author slate date:2013-09-14
	 */
	public function delSchedule() {
	
		$id = $this->_param('id');
	
		$user_id = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
	
		$result = 0;
	
		if ($id) {
				
			$result = 1;
				
			if ($user_id) {
	
				$scheduleModel = M("Schedule");
	
				if (false === $scheduleModel->where(array('mid' => $user_id, 'id' => $id))->save(array('status' => 1))) {
	
					$result = 0;
				}
	
			}
				
			$schedule_list = cookie(md5('schedule_list'));
			unset($schedule_list[array_search($id, $schedule_list)]);
			cookie(md5('schedule_list'), $schedule_list);
		}
	
		echo $result;
	
	}
	
	/**
	 * @name delArea
	 * @desc 删除自留地
	 * @param string web_id
	 * @return 成功:1; 失败:0; 未登录或登录已失效: -1
	 * @author slate date:2013-09-14
	 */
	public function delArea() {
	
		$id = $this->_param('web_id');
	
		$user_id = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
	
		$result = 0;
	
		if ($id) {
			if ($user_id) {
	
				$memberModel = M("Member");
	
				$myarea = M("Myarea");
					
				if (false !== $myarea->where(array('id' => $id, 'mid' => $user_id))->delete()) {
	
					$result = 1;
						
					unset($_SESSION['myarea_sort'][array_search($id, $_SESSION['myarea_sort'])]);
					$memberModel->where(array('id' => $user_id))->save(array('myarea_sort' => implode(',', $_SESSION['myarea_sort'])));
				}
	
			} else {
					
				$result = -1;
			}
		}
	
		echo $result;
	
	}
	
	/**
	 * @name updateArea
	 * @desc 更新我的地盘
	 * @param string web_url
	 * @param string web_name
	 * @return 成功:1; 失败:0; 未登录或登录已失效: -1
	 * @author slate date:2013-09-14
	 */
	public function updateArea() {
	
		$url = $this->_param('web_url');
		$webname = $this->_param('web_name');
		$id = $this->_param('web_id');
	
		$user_id = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
	
		$result = 0;
	
		if ($user_id) {
			$myarea = M("Myarea");
			$memberModel =  M("Member");
	
			$now = time();
	
			$saveData = array(
					'url' => $url,
					'web_name' => $webname,
					'create_time' => $now
			);
	
			if (!$id) {
				$saveData['mid'] = $user_id;
				$id = $myarea->add($saveData);
				if ($id) {
	
					$saveData['id'] = $result = $id;
						
					$_SESSION['arealist'][$id] = $saveData;
					array_push($_SESSION['myarea_sort'], $id);
					$memberModel->where(array('id' => $user_id))->save(array('myarea_sort' => implode(',', $_SESSION['myarea_sort'])));
				}
			} else {
				if (false !== $myarea->where(array('id' => $id, 'mid' => $user_id))->save($saveData)) {
						
					$result = 1;
				}
			}
	
			if ($result) {
	
				if ($id) {
					$_SESSION['arealist'][$id]['url'] = $url;
					$_SESSION['arealist'][$id]['web_name'] = $webname;
				}
			}
		} else {
				
			$result = -1;
		}
	
		echo $result;
	}
	/**
	 * @name sortArealist
	 * @desc 拖动我的地盘进行排序
	 * @param Array area
	 * @return 成功:1; 失败:0;
	 * @author slate date:2013-09-14
	 */
	public function sortArealist() {
	
		$result = 1;
	
		$area_list = $this->_post('area');
	
		if ($this->isAjax() && $area_list) {
				
			
			$_SESSION['myarea_sort'] = $area_list;
				
			$user_id = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
				
			$memberModel = M("Member");
				
			if ($user_id) {
	
				$memberModel->where(array('id' => $user_id))->save(array('myarea_sort' => implode(',', $area_list)));
					
			}
				
		} else {
				
			$result = 0;
		}
	
		echo $result;
	}
	
	/**
	 * APP排序
	 * 
	 * @param appIds
	 * 
	 * @return void
	 * 
	 * @author slate date:2013-10-02
	 */
	public function sortApp() {
	
		$result = 1;
	
		$appIds = $this->_post('appIds');
	
		if ($this->isAjax() && $appIds) {
	
				
			$_SESSION['app_sort'] = $app_sort = implode(',', $appIds);
	
			$user_id = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
	
			$memberModel = M("Member");
	
			if ($user_id) {
	
				$memberModel->where(array('id' => $user_id))->save(array('app_sort' => $app_sort));
					
			} else {
				
				cookie('app_sort', $app_sort, array('expire' => 0));
			}
	
		} else {
	
			$result = 0;
		}
	
		echo $result;
	}
	
	public function tag() {
		$key = $this->_param('q');
		$dl = M('directLinks');
		$condition['tag'] = array('like', '%' . $key . '%');
		$data = $dl->where($condition)->select();
		$val = '';
		foreach ($data as $row) {
			$val .= $row['tag'].'|'.$row['id']."\n";
		}
		echo $val;
		exit;
	}

	//MacQQBrowser载入定制的豆瓣音乐
	public function dbfm(){
		$this->display();
	}
}
