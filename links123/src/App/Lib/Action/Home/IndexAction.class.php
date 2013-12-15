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
	public function index() {
		import("@.ORG.String");
		//自留地
		$myareaModel = M("Myarea");
		$scheduleModel = M("Schedule");

		$user_id = $this->userService->getUserId();
		if ($user_id) {
			$memberModel = M("Member");
			$mbrNow = $memberModel->where(array('id' => $user_id))->find();
			$_SESSION['myarea_sort'] = $mbrNow['myarea_sort'] ? explode(',', $mbrNow['myarea_sort']) : '';
			$_SESSION['app_sort'] = $mbrNow['app_sort'];
			$_SESSION['news_history'] = explode(',',$mbrNow['news_history']);
		} else {
			$this->userService->getGuestId();
		}
		
		//取出皮肤ID和模板ID
		$skinId = session('skinId');
		if (!$skinId) {
			$skinId = cookie('skinId');
		}
		$themeId = session('themeId');
		if (!$themeId) {
			$themeId = cookie('themeId');
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
			$areaList = $myareaModel->where(array('mid' => $user_id))->order('sort asc')->select();

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

		//links
		$friend_links = $this->getFriendLinks();
		
		//APP应用
		$app_list = $this->getApps($_SESSION['app_sort']);
		$this->assign('app_list', $app_list);
       // print_r($this->getAstro());
        $this->assign("astro",$this->getAstro());
        //气象数据
        $this->assign('weatherData', $this->getWeatherData());
        
        $this->assign('friend_links', $friend_links);
        
        $this->assign('newsData', $this->getNews());
        $this->assign('userNewsType', intval($_COOKIE['news_type']));
        
		$this->getHeaderInfo();
		$this->display('index_v4');
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


		$result = true;

		if ($this->userService->isLogin()) {
			$user_id = $this->userService->getUserId();

			$memberModel = M("member");

			if (!$memberModel->where(array('id' => $user_id))->setField('skin' , $skinId)) {

				$result = false;
			}

		}

		if ($skinId) {
			session('skinId', $skinId);
			cookie('skinId', $skinId, array('expire' => 31536000));
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


		$result = true;

		if ($this->userService->isLogin()) {
			$user_id = $this->userService->getUserId();

			$memberModel = M("member");

			if (!$memberModel->where(array('id' => $user_id))->setField('theme' , $themeId)) {

				$result = false;
			}

		}

		session('themeId', $themeId);
		cookie('themeId', $themeId, array('expire' => 31536000));

		$this->updateSkin(0);
		$this->ajaxReturn($result);
	}

	/**
	 * 更新首页主题 V4
	 *
	 *  与v3版区别: themeId是字符串
	 *
	 */
	public function updateThemeV4() {

		$themeId = $this->_param('themeId');


		$result = true;

		if ($this->userService->isLogin()) {
			$user_id = $this->userService->getUserId();

			$memberModel = M("member");

			if (!$memberModel->where(array('id' => $user_id))->setField('theme' , $themeId)) {

				$result = false;
			}

		}

		session('themeId', $themeId);
		cookie('themeId', $themeId, array('expire' => 31536000));

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

		$paiLie = $this->_session('pailie');
		
		if (empty($paiLie)) {
			if($this->userService->isLogin()){
				$paiLie = M("Member")->where("id = '%s'", $this->userService->getUserId())->getField('pailie');
			}else{
				$paiLie = M("Variable")->where("vname='pailie'")->getField("value_int");
			}
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
		if ($this->userService->isLogin()) {
			$user_id = $this->userService->getUserId();
			$member = M("Member");
			if (false === $member->where("id = '%d'", $user_id)->setField('pailie', $val)) {
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

		$status = $this->userService->resetPassword($email);
		switch($status){
			case 203:
				echo "未发现您输入的邮箱！";
				return false;
			case 204:
				echo "已禁用！";
				return false;
			case 209:
				echo "邮箱格式错误";
				return false;
			case 219:
				echo "发送新密码失败！";
				return false;
			case 200:
				echo "sendOK";
				return true;
		}
		return false;
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
			$data['mid'] = $this->userService->getUserId();
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

		$mid = $this->userService->getUserId();
		$paiLie = $this->_session('pailie');
		$lan = intval($this->_param('lan'));
		$cid = intval($this->_param('cid'));
		$keyword = cleanParam($this->_param('q'));
		$pg = intval($this->_param(C('VAR_PAGE')));
		
		$condition['status'] = 1;
		if (empty($paiLie)) {
			if($this->userService->isLogin()){
				$paiLie = M("Member")->where("id = '%s'",$mid)->getField('pailie');
			}else{
				$paiLie = M("Variable")->where("vname = 'pailie'")->getField("value_int");

			}
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
			$mid = $this->userService->getUserId();
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
	 * @desc 获取日程
	 * 
	 * @param String year
	 * @param String month
	 * @return
	 * @author slate date:2013-10-27
	 */
	public function getSchedule() {
		$year = intval($this->_param('year'));
		$month = intval($this->_param('month'));

		$stauts = 1;
		$data  = array();
		$nowTime = time();
		$today = intval(date('d', $nowTime));

		$user_id = $this->userService->getId();
		
		if ($user_id) {
			$scheduleModel = M("Schedule");
		
			$result = $scheduleModel->where(array('mid' => $user_id,'status' =>0 , 'year' => $year, 'month' => $month))->select();
		
			foreach ($result as $k => $v) {
				$data[$v['day']][] = $v;
			}
			
		}
		
		if (!$data[$today]) {
			$data[$today][] = array('id' => 0, 'content' => '来创建今天新的日程吧！', 'datetime' => $nowTime, 'stauts' => 0);
		}
		
		$this->ajaxReturn($data, '', $stauts);
	}
	/**
	 * @name addSchedule
	 * @desc 添加日程
	 * @param string desc
	 * @param string time
	 * @return 成功:1; 失败:0; 未登录或登录已失效: -1
	 * @author slate date:2013-09-14
	 */
	public function addSchedule() {
	
		$content = $this->_param('desc');
		$datetime = intval($this->_param('time'));
	
		$result = 0;
	
		$user_id = $this->userService->getId();
		
		if ($user_id && $content) {
			
			$scheduleModel = M("Schedule");
	
			$now = time();
				
			$datetime = $datetime ? $datetime : $now;
	
			$saveData = array(
					'mid' => $user_id,
					'content' => $content,
					'datetime' => $datetime,
					'status' => 0,
					'create_time' => $now,
					'update_time' => $now,
					'year' => date('Y', $datetime),
					'month' => date('m', $datetime),
					'day' => date('d', $datetime)
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
	 * @param String desc
	 * @param String time
	 * @return 成功:1; 失败:0;
	 * @author slate date:2013-09-14
	 */
	public function updateSchedule() {
	
		$id = $this->_param('id');
		$content = $this->_param('desc');
		$datetime = $this->_param('time');
	
		$result = 0;
	
		if ($id) {
				
			$now = time();
				
			$datetime = $datetime ? $datetime : $now;
	
			$saveData = array(
					'content' => $content,
					'datetime' => $datetime,
					'update_time' => $now,
					'year' => date('Y', $datetime),
					'month' => date('m', $datetime),
					'day' => date('d', $datetime)
			);
				
			$result = 1;
			
			$user_id = $this->userService->getId();
			
			if ($user_id) {
				$scheduleModel = M("Schedule");
					
				if (false === $scheduleModel->where(array('id' => $id, 'mid' => $user_id))->save($saveData)) {
						
					$result = 0;
				}
			} else {
				
				$result = -1;
			}				
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
	
		$result = 0;
	
		if ($id) {
				
			$result = 1;
			
			$user_id = $this->userService->getId();
			
			if ($user_id) {
	
				$scheduleModel = M("Schedule");
	
				if (false === $scheduleModel->where(array('mid' => $user_id, 'id' => $id))->save(array('status' => 1))) {
	
					$result = 0;
				}
	
			} else {
				
				$result = -1;
			}
		}
	
		echo $result;
	
	}
	
	public function getNote() {
	
		$stauts = 1;
		$nowTime = time();
		$today = intval(date('d', $nowTime));
	
		$user_id = $this->userService->getId();
		
		if ($user_id) {
			$noteModel = new NoteModel();
	
			$data = $noteModel->getNotesByUser($user_id);
	
		}
	
		$this->ajaxReturn($data, '', $stauts);
	}
	
	/**
	 * 添加/更新便签
	 *
	 * @param id：便签ID，有ID为更新，无ID为添加
	 * @param pageX：坐标X
	 * @param pageY: 坐标Y
	 * @param background：背景色
	 * @param content：便签内容
	 * 
	 * @return json：status=0,删除失败;status=1,删除成功;
	 *
	 * @author slate date:2013-11-15
	 */
	public function updateNote() {
	
		$id = $this->_param('id');
		$pageX = intval($this->_param('pageX'));
		$pageY = intval($this->_param('pageY'));
		$background = $this->_param('background');
		$content = $this->_param('content');
	
		$status = 0;
	
	
		$now = time();

		$saveData = array();
		
		if ($content) {
			$saveData['content'] = $content;
		}
		
		if ($pageX) {
			$saveData['pageX'] = $pageX;
			$saveData['pageY'] = $pageY;
		}
		
		if ($background) {
			$saveData['background'] = $background;
		}

		$user_id = $this->userService->getId();
		
		if ($user_id) {
			
			$noteModel = new NoteModel();
			if ($id) {
				if ($noteModel->updateNote($id, $user_id, $saveData)) {
	
					$status = 1;
				}
			} else {
				
				$saveData['created'] = $now;
				$saveData['mid'] = $user_id;
				$id = $noteModel->addNote($saveData);
				if ($id) {
					$status = 1;
				}
			}
		} else {

			$status = -1;
		}
		
		$this->ajaxReturn(array('id' => $id), '', $status);
	}
	
	/**
	 * 删除便签
	 * 
	 * @param id：便签ID
	 * 
	 * @return json：status=0,删除失败;status=1,删除成功;
	 * 
	 * @author slate date:2013-11-15
	 */
	public function delNote() {
	
		$id = $this->_param('id');
	
		$status = 0;
	
		if ($id) {
	
			$user_id = $this->userService->getId();
			
			if ($user_id) {
	
				$noteModel = new NoteModel();
	
				if ($noteModel->delNote($id, $user_id)) {
	
					$status = 1;
				}
	
			} else {
	
				$status = -1;
			}
		}
	
		$this->ajaxReturn('', '', $status);
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
	
		$user_id = $this->userService->getUserId();
		$result = 0;
	
		if ($id) {
            //判断session是否为空
            if(!$_SESSION['arealist']){
                $_SESSION['arealist'] = D("Myarea")->where(array("mid" => $user_id))->select();
            }
			if ($user_id) {
	
				$memberModel = M("Member");
	
				$myarea = M("Myarea");
				//判断session是否为空
                if(!$_SESSION['myarea_sort']){
                    $myarea_sort = $memberModel->where(array('id' => $user_id))->getField("myarea_sort");
                    if(false !== $myarea_sort && !empty($myarea_sort)){
                        $_SESSION['myarea_sort'] = explode(",", $myarea_sort);
                    }else{
                        $_SESSION['myarea_sort'] = array_keys($_SESSION['arealist']);
                    }
                }
				if (false !== $myarea->where(array('id' => $id, 'mid' => $user_id))->delete()) {
	
					$result = 1;
						
                    unset($_SESSION['arealist'][$id]);
					unset($_SESSION['myarea_sort'][array_search($id, $_SESSION['myarea_sort'])]);
					$memberModel->where(array('id' => $user_id))->save(array('myarea_sort' => implode(',', $_SESSION['myarea_sort'])));
				}
	
			} else {
                //判断session是否为空
                if(!$_SESSION['myarea_sort']){
                    $_SESSION['myarea_sort'] = array_keys($_SESSION['arealist']);
                }
                unset($_SESSION['arealist'][$id]);
				unset($_SESSION['myarea_sort'][array_search($id, $_SESSION['myarea_sort'])]);
				$result = 1;
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
	
		$user_id = $this->userService->getUserId();
	
		$result = 0;
        //判断session是否为空
        if(!$_SESSION['arealist']){
            $_SESSION['arealist'] = D("Myarea")->where(array("mid" => $user_id))->select();
        }
		if ($user_id) {
			$myarea = M("Myarea");
			$memberModel =  M("Member");
            //判断session是否为空
            if(!$_SESSION['myarea_sort']){
                $myarea_sort = $memberModel->where(array('id' => $user_id))->getField("myarea_sort");
                if(false !== $myarea_sort && !empty($myarea_sort)){
                    $_SESSION['myarea_sort'] = explode(",", $myarea_sort);
                }else{
                    $_SESSION['myarea_sort'] = array_keys($_SESSION['arealist']);
                }
            }
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
            //判断session是否为空
            if(!$_SESSION['myarea_sort']){
                $_SESSION['myarea_sort'] = array_keys($_SESSION['arealist']);
            }
            $result = 1;
            if (!$id) {
                $id = end($_SESSION['myarea_sort']) + 1;
                array_push($_SESSION['myarea_sort'], $id);
                $_SESSION['arealist'][$id]['id'] = $id;
                $result = $id;
            }
            $_SESSION['arealist'][$id]['url'] = $url;
            $_SESSION['arealist'][$id]['web_name'] = $webname;
				
			
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
				
			$user_id = $this->userService->getUserId();
				
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
	
			$user_id = $this->userService->getUserId();
	
			$memberModel = M("Member");
	
			if ($user_id) {
	
				$memberModel->where(array('id' => $user_id))->save(array('app_sort' => $app_sort));
					
			} else {
				
				cookie('app_sort', $app_sort, array('expire' => 31536000));
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
    
    /**
     * 获取当前访问用户所在地的天气数据
     * 
     * @author Hiker date:2013-10-16
     * 
     * @return array
     */
    protected function getWeatherData() {
        
        $weather = A('Home/Weather');
        $city_id = $_COOKIE['weather_region'];
        $data = json_decode($weather->getJsonData($city_id));
        $weatherData = array(
            'city' => $data->n,
            'temp' => $data->t == 'NA' ? $data->d1->l . '-' . $data->d1->h : $data->t,
            'sun'  => $data->s,
            'air'  => $data->i->aq->label
        );
        
        return $weatherData;
    }
    
    /**
     * 获取指定条数的数据推荐电影数据
     * 
     * @author Hiker date:2013-10-16
     * 
     * @param int $length
     * @return array
     */
    protected function getHomeMovies($length = 10) {
        
        $result = S('HomeMovies'.$length);
        if(!$result) {
            $model = D('HomeMovie');
            $result = $model->getList(array('status'=>1), 'sort', $length);
            
            S('HomeMovies'.$length, $result);
        }
        
        return $result;
    }
    
    /**
     * 获取指定数目的推荐音乐数据
     * 
     * @author Hiker date:2013-10-16
     * 
     * @param int $length
     * @return array
     */
    protected function getHomeMusics($length = 30) {
        
        $result = S('HomeMusics'.$length);
        if(!$result) {
            $model = D('HomeMusic');
            $result = $model->getList(array('status'=>1), 'sort', $length);
            
            S('HomeMusics'.$length, $result);
        }
        
        return $result;
    }
    
    /**
     * 抓取360热门新闻头条
     */
    protected function getHotNews() {
    
    	$hotNews = S('hotNewsList');
    	//调用分类页面头条，用以判断用户习惯
    	//$url = 'http://sh.qihoo.com/index.html';
		//目前每个分类是6条数据
		$urlArray = array(
			'http://sh.qihoo.com/china/',
			'http://sh.qihoo.com/world/',
			'http://sh.qihoo.com/mil/',
			'http://sh.qihoo.com/ent/',
			'http://sh.qihoo.com/sports/',
			'http://sh.qihoo.com/internet/',
			'http://sh.qihoo.com/tech/',
			'http://sh.qihoo.com/finance/',
			'http://sh.qihoo.com/house/',
			'http://sh.qihoo.com/edu/',
			'http://sh.qihoo.com/game/',
			'http://sh.qihoo.com/health/',
			'http://sh.qihoo.com/society/'
		);
		$time = time();
		//每次更新1个分类
		$i=1;
		$updateCache = false;
		foreach($urlArray as $type=>$url){
			if($i==0) break;
			if(!empty($hotNews[$type]) && $hotNews[$type]['cacheTime'] > $time) continue;
			if(empty($hotNews[$type])){
				$hotNews[$type] = array('list'=>array(),'cacheTime'=>0);
			}else{
				$i--;
			}
			$baseURL = U('clickHotNews').'?redirectURL=%s&type=%s';

			$str = file_get_contents($url);
			$str = $this->tp_match('/<ul class="contents">(.*?)<\/ul>/is', $str);
			preg_match_all('/<li(.*?)<\/li>/is', $str, $match);
			foreach ($match[0] as $k => $v) {
				$hotNews[$type]['list'][] = array('url' => sprintf($baseURL,urlencode(stripslashes($this->tp_match('/href="(.*?)"/is', $v))),$type), 'title' => str_replace('"', '“',trim(strip_tags($this->tp_match('/<span class="title">(.*?)<\/span>/is', $v)))), 'img' => $this->tp_match('/src="(.*?)"/is', $v));
			}
			$hotNews[$type]['cacheTime'] = $time + 14400;
			$updateCache = true;
		}
		//如果更新过新闻，则重新缓存
    	if($updateCache) S('hotNewsList', $hotNews);
		if(empty($_SESSION['news_history'])){
			//获取游客数据
			$_SESSION['news_history'] = cookie('news_history');
		}
		//浏览历史记录长度与分类总数相等
		//保证偏好数据有效性及更新频率
		$historyCount = count($urlArray);
		if(count($_SESSION['news_history']) > $historyCount){
			$_SESSION['news_history'] = array_slice($_SESSION['news_history'],-1 * $historyCount);
			//重新更新用户浏览历史记录
			$user_id = $this->userService->getUserId();
			if($user_id){
				$memberModel =  M("Member");
				$memberModel->where(array('id' => $user_id))->save(array('news_history' => implode(',', $_SESSION['news_history'])));
			}else{
				cookie('news_history',implode(',', $_SESSION['news_history']));
			}
		}

		//计算用户偏好
		$userInfo = array();
		if(!empty($_SESSION['news_history'])){
			foreach($_SESSION['news_history'] as $type){
				if(empty($userInfo[$type])){
					$userInfo[$type] = 1;
				}else{
					$userInfo[$type]++;
				}
			}
		}

		//根据用户偏好，获取13条新闻，
		$news_num = 13;
		$img_num = 4;
		$num = $news_num + $img_num;
		$list = $other_list = array();
		foreach($hotNews as $type=>$typelist){
			if($num == 0) break;
			if(!empty($userInfo[$type])){
				//获取当前分类的偏好权重
				$count = $userInfo[$type];
				//修正权重：避免用户只能看到某几个分类的新闻，导致产生不了新的偏好
				//如果不需要考虑，直接注释即可
				$count = $count > 1 ? $count>>1 : $count;
				foreach($typelist['list'] as $key=>$news){
					if($num == 0) break 2;
					if($count == 0) break;
					$list[] = $news;
					$count--;
					$num--;
					unset($hotNews[$type]['list'][$key]);
				}
			}
			$other_list = array_merge($other_list,$typelist['list']);
		}
		//随机获取剩余文章数
		shuffle($other_list);
		$list = array_merge($list,array_slice($other_list , 0,$num));
		/*
		for($i=0;$num>0;$i = (int)$i/2){
			if($i >= $historyCount || $i == 0) $i=mt_rand(0,$historyCount-1);
			$count = count($hotNews[$i]['list']);
			$y = mt_rand(0,$count-1);
			$list[] = $hotNews[$i]['list'][$y];
			unset($hotNews[$i]['list'][$y]);
			$num--;
		}*/
		//图片新闻为前几篇，正好符合偏好显示为主
    	return array('news'=>array_slice($list,$img_num,$news_num),'imgNews'=>array_slice($list,0,$img_num));
    } 
    
    /**
     * 抓取英闻
     */
    protected function getEnglishNews() {
    	$hotNews = S('EnglishNewsList');
    	
    	if (!$hotNews) {
    	
    		$url = 'http://www.en84.com/';
    		$str = file_get_contents($url);
    		if ($str) {
    			
    			$str = iconv('gbk', 'utf8', $str);
    			
	    		$news = $imgNews = array();
	    		
	    		preg_match_all('/<div class="module cl xl xl1">(.*?)<\/div>/is', $str, $match);
	    		
	    		foreach ($match[0] as $matchStr) {
		    		preg_match_all('/<li>(.*?)<\/li>/is', $matchStr, $match);
		    		foreach ($match[1] as $k => $v) {
		    			$v = preg_replace(array('/<label>(.*?)<\/label>/is', '/<em>(.*?)<\/em>/is'), '', $v);
		    			
		    			$news[] = array('url' => $url . $this->tp_match('/href="(.*?)"/is', $v), 'title' => mb_substr(trim(str_replace(array("\n", '>'), '', strip_tags($v))), 0, 24, 'utf8'), 'img' => '');
		    			
		    		}
	    		}
	    		$imgNewsStr = $this->tp_match('/<ul class="slideshow">(.*?)<\/ul>/is', $str);
	    		preg_match_all('/<li(.*?)<\/li>/is', $imgNewsStr, $match);
	    		foreach ($match[0] as $k => $v) {
	    			$imgNews[] = array('url' => $url . stripslashes($this->tp_match('/href="(.*?)"/is', $v)), 'title' => str_replace('"', '“',trim(strip_tags($this->tp_match('/<span class="title">(.*?)<\/span>/is', $v)))), 'img' => $url . $this->tp_match('/src="(.*?)"/is', $v));
	    		}
	    		$hotNews = array('news' => $news, 'imgNews' => $imgNews);
	    		S('EnglishNewsList', $hotNews, 28800);
	    		if ($news && $imgNew) {
	    			S('EnglishNewsList_back', $hotNews);
	    		}
    		}
    		
    		if (!$hotNews) {
    			$hotNews = S('EnglishNewsList_back');
    		}
    	}
    	shuffle($hotNews['news']);
    	
    	$news = array_chunk($hotNews['news'], 13, true);
    	if ($news[0]) {
    		$hotNews['news'] = $news[0];
    	}
    	
    	return $hotNews;
    }
    
    /**
     * 获取blog文章
     */
    protected function getBlogNews() {
    	$hotNews = S('BlogNewsList');
    	 
    	if (!$hotNews) {
    		 
    		$url = 'http://blog.links123.cn/newsAPI.php';
    		$str = file_get_contents($url);
    		if ($str) {
    			$str = preg_replace('/\r|\n/is', '', $str);
    			$news = json_decode($str, true);
    			
    			foreach ($news as $k => $v) {
    				$v['desc'] = addslashes($v['desc']);
    				if ($v['img']) {
    					$imgNews[] = $v;
    				}
    			}
    	   
    			$hotNews = array('news' => $news, 'imgNews' => $imgNews);
    			
    			S('BlogNewsList', $hotNews, 28800);
    			
    			if ($hotNews) {
    				S('BlogNewsList_back', $hotNews);
    			}
    		}
    
    		if (!$hotNews) {
    			$hotNews = S('BlogNewsList_back');
    		}
    	}
    	 
    	return $hotNews;
    }
    
    protected function getFriendLinks() {
    	$friendLinks = S('FriendLinks');
    
    	if (!$friendLinks) {
    		
    		$friendLinkModel = M('FriendLink');
    		$friendLinks = $friendLinkModel->where(array('status' => 0))->order('sort asc')->select();
  
    		S('FriendLinks', $friendLinks);
   
    	}

    	return $friendLinks;
    }
    
	/**
     * 搜索框自动填充
     */
	public function searchSupplement() {
	
		$q = $_GET["q"];
		$abcs = mb_convert_encoding(trim($q),"utf-8","gb2312");           //接收传送过来的关键值
		$skey = file_get_contents("http://suggestion.baidu.com/su?wd=".urlencode($q)."");        //访问百度页面
		preg_match('/\[(.*?)\]/',$skey,$m);    //通过正则去掉
		$s = explode(',',$m[1]);    
		foreach($s as $k=>$v){
			$s[$k] = iconv("gb2312","UTF-8",substr($v,1,-1));
		}
		echo json_encode($s);
	}

	/**
	 * @desc 热门文章点击记录
	 * @author GO 2013-11-30
	 */
    public function clickHotNews(){
		$redirectURL = $this->_param('redirectURL');
		$type = $this->_param('type');
		//记录用户操作
		//重新更新用户浏览历史记录
		$user_id = $this->userService->getUserId();
		if($user_id){
			$memberModel = M("Member");
			$mbrNow = $memberModel->where(array('id' => $user_id))->find();
			//记录游客数据
			if(empty($mbrNow['news_history'])) $mbrNow['news_history'] = cookie('news_history');
			$memberModel->where(array('id' => $user_id))->save(array('news_history' => empty($mbrNow['news_history']) ? $type : $mbrNow['news_history'].','.$type));
		}else{
			$news_history = cookie('news_history');
			cookie('news_history',empty($news_history) ? $type : $news_history.','.$type);
		}
		//继续跳转操作
		header('Location:'.$redirectURL);
		exit;
	}
	
	/**
	 * @desc 新闻ajax调用接口
	 * 
	 * @param type : 栏目类型
	 * 
	 * @return json 
	 * 
	 * @author slate date:2013-12-02
	 */
	public function getNews() {
		
		//$newsType = intval($this->_param('type'));
		$data = array();

		$data['column'] = array(
			array('type' => 0, 'name' => '英闻', 'url' => 'http://www.en84.com/', 'status' => 0),
			array('type' => 1, 'name' => '头条', 'url' => 'http://sh.qihoo.com/index.html', 'status' => 0),
			array('type' => 2, 'name' => '博客', 'url' => 'http://blog.links123.cn/', 'status' => 0),
			array('type' => 3, 'name' => '文摘', 'url' => '', 'status' => 1),
			array('type' => 4, 'name' => '社交', 'url' => '', 'status' => 1)
		);
		
		foreach ($data['column'] as $k => $v) {
			$newsData = array();
			if ($v['status'] == 0) {
				switch ($k) {
					case 0 :
						
						$newsData = $this->getEnglishNews();
						
						break;
					case 1 : 
						
						$newsData = $this->getHotNews();
						break;
						
					case 2 :
						$newsData = $this->getBlogNews();
						break;
						
					default:
						break;
				}
				
				//$data['news'][$k] = $v;
				$data['news'][$k]['pics'] = $newsData['imgNews'];
				$data['news'][$k]['texts'] = $newsData['news'];
				$data['news'][$k]['type'] = $k;
				$data['news'][$k]['more_url'] = $v['url'];
				//TODO 添加排序
			}
		}
		//$this->ajaxReturn($data, '', $stauts);
		
		return $data;
	}

    /**
     * 获取星座的运势信息
     * @param bool $birthday
     * @return mixed
     */
    private function getAstro($birthday = false){
		$star=$this->getStar($birthday);
		$starArr = explode('=',$star);
		$starid=$starArr[1];
		if($birthday){
			$url="http://api.uihoo.com/astro/astro.http.php?fun=day&id=$starid&format=json";
		}else{
			$url="http://api.uihoo.com/astro/astro.http.php?fun=day&id=$starid&format=json";
		}
		$content=file_get_contents($url);
		$astro= json_decode($content, true);
		if($birthday){
			return $astro;
		}else{
			return $astro;
		}
	}

    /**
     * 获取星座
     * @param bool $astrodate
     * @return mixed
     */
    private function getStar($astrodate=false){
        if(!$astrodate){
            $curtime= getdate(time());
        }else{
            $curtime= getdate(strtotime($astrodate));
        }
        $m=$curtime["mon"];
        $d=$curtime["mday"];
        $xzdict = array ('摩羯=9', '水瓶=10', '双鱼=11', '白羊=0', '金牛=1', '双子=2', '巨蟹=3', '狮子=4', '处女=5', '天秤=6', '天蝎=7', '射手=8' );
        $zone = array (1222, 122, 222, 321, 421, 522, 622, 722, 822, 922, 1022, 1122, 1222 );
        if ((100 * $m + $d) >= $zone [0] || (100 * $m + $d) < $zone [1])
        {
            $i = 0;
        }
        else
        {
            for($i = 1; $i < 12; $i ++)
            {
                if ((100 * $m + $d) >= $zone [$i] && (100 * $m + $d) < $zone [$i + 1])
                    break;
            }
        }
        return $xzdict[$i];
    }
}
