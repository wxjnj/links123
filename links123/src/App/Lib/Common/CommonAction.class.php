<?php
/**
 * 整站公共部件初始化
 * @author heyanlong 2013-07-30
 */
class CommonAction extends Action {

	protected function _initialize() {
		$this->_init();
	}
	
	/**
	 * @desc 初始化方法
	 * @author frank UPDATE 2013-08-15
	 */
	private function _init() {
		session_start();
		//网站升级
		$this->updating();
		//自动登录
		$this->autoLogin();
		
		$variable = $this->_getVariable();
		
		//获取用户留言
		if (D("SuggestionView")->isTodayHasNewSuggestion()) {
			$this->assign("newSuggestion", 1);
		}
		$this->getRootCats();
		//获取日期
		$today = $this->getDate();
		//糖葫芦
		$thlList =  D("Thl")->getThlListWithThlz();
		
		$this->assign('cn_tip', $variable['cnTip']);
		$this->assign('en_tip', $variable['enTip']);
		$this->assign('directTip', $variable['directTip']);
		$this->assign('today', $today);
		$this->assign("thl_list", $thlList);
		$this->assign('thlNow', '搜');
		$this->assign('tidNow', 1);
	}

	/**
	 * @desc 检查是否会员登录
	 * @author Frank UPDATE 2013-08-18
	 * @param int $ajax
	 * @return boolean
	 */
	protected function checkLog($ajax = 0) {
		$mid = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
		
		if (empty($mid)) {
			if ($ajax) {
				echo "请先登录！";
				return false;
			} else {
				header("Location: " . __APP__ . "/");
				exit(0);
			}
		} else {
			return true;
		}
	}
	
	/**
	 * 获取头部设置信息
	 * @return array
	 * @author heyanlong 2013-07-30
	 */
	private function _getVariable() {
		$variable    = M("Variable");
		$title       = $variable->getByVname('title');
		$keywords    = $variable->getByVname('Keywords');
		$description = $variable->getByVname('Description');
		$cnTip       = $variable->getByVname('cn_tip');
		$enTip       = $variable->getByVname('en_tip');
		$directTip   = $variable->getByVname('directTip');
		$thl         = $variable->getByVname('thl');
		$pauseTime   = $variable->getByVname('pauseTime');

		return array(
			'title'       => $title['value_varchar'],
			'keywords'    => $keywords['value_varchar'],
			'description' => $description['value_varchar'],
			'cnTip'       => $cnTip['value_varchar'],
			'enTip'       => $enTip['value_varchar'],
			'directTip'   => $directTip['value_varchar'],
			'thl'         => $thl['value_varchar'],
			'pauseTime'   => $pauseTime['value_int'],
		);
	}

	/**
	 * 获取糖葫芦
	 * @author heyanlong 2013-07-30
	 */
	protected function getThl() {
		$variable = $this->_getVariable();
		return explode(",", $variable['thl']);
	}

	/**
	 * 获取目录图片
	 * @param int $rid
	 * @return array
	 * @author heyanlong 2013-07-30
	 */
	protected function getCatPics($rid = 1) {
		$variable = $this->_getVariable();
		return array(
			'catPics' => M("CatPic")->where('rid=' . $rid)->order('sort')->select(),
			'pauseTime' => (int)$variable['pauseTime'] * 1000
		);
	}

	/**
	 * 获取单页
	 * @param $id
	 * @return mixed
	 * @author heyanlong 2013-07-30
	 */
	protected function getPage($id) {
		return M("Pages")->getById($id);
	}

	/**
	 * 获取广告
	 * @param $ids
	 * @param $aim
	 * @return mixed
	 * @author heyanlong 2013-07-30
	 */
	protected function getAdvs($ids, $aim) {
		return M("Advert")->where('id in(' . $ids . ')')->order('sort')->select();
	}

	/**
	 * 获取根目录
	 * @param $cid
	 * @return mixed
	 * @author heyanlong 2013-07-30
	 */
	protected function getRoot($cid) {
		$cat = M("Category");
		$catNow = $cat->getById($cid);
		if ($catNow['prt_id'] == 0) {
			return $catNow['id'];
		} else {
			return $this->getRoot($catNow['prt_id']);
		}
	}


	/**
	 * 获取页头
	 * @param array $data
	 */
	protected function getHeaderInfo($data=array()) {

		$variable = $this->_getVariable();
		$title = empty($data['title'])?'另客网 | 领先的全面导航 | 高效的组合搜索 | 独特的英语角（在建） - 学习':empty($data['title']);
		$keywords = empty($data['keywords'])?$variable['keywords']:empty($data['keywords']);
		$description = empty($data['description'])?$variable['description']:empty($data['description']);
		
		$this->assign('title', $title);
		$this->assign('keywords', $keywords);
		$this->assign('description', $description);
	}

	/**
	 * 获取页底
	 */
	protected function getFooter() {
		$this->display("Public:newFooter");
	}

    /**
     * @desc 获取所有根目录
     * @author frank UPDATE 2013-08-15
     */
    protected function getRootCats() {
        $cats = M("Category")->field('id, cat_name, intro, level')->where('status=1 and level=1')->order('sort ASC')->select();
        $this->assign("rootCats", $cats);
    }
    
    /**
     * @desc 获取所有子目录
     * @author frank UPDATE 2013-08-16
     * @param int $pid
     * @return array:
     */
    protected function _getSubCats($pid) {
        $pids = array();
        array_push($pids, $pid);
        $cat = M("Category");
        $list = $cat->field('id')->where('status=1 and prt_id = %d', $pid)->select();
        if (count($list)) {
            foreach ($list as &$value) {
                $pids = array_merge($pids, $this->_getSubCats($value['id']));
            }
        }
        return $pids;
    }

    /**
     * @desc 获取左栏目录
     * @author frank UPDATE 2013-08-16
     * @param int $rid
     * @return void
     */
    protected function getLeftMenu($rid) {
        $cat = M("Category");
        $Menu = $cat->where('status=1 and flag<3 and prt_id= %d', $rid)
        ->order('sort ASC')->select();
        foreach($Menu as $m) {
        	if ($m['flag'] == 1) {
        		$leftMenuCn[] = $m;
        	} else {
        		$leftMenuEn[] = $m;
        	}
        }
        $this->assign("leftMenuCn", $leftMenuCn);
        $this->assign("leftMenuEn", $leftMenuEn);
    }

    /**
     * @desc 获取日期
     * @author frank UPDATE 2013-08-16
     * @return string 
     */
    public function getDate() {
        $weekdays = array("周日", "周一", "周二", "周三", "周四", "周五", "周六");
        $today = date('m月d日 *');
        $today = str_replace("*", $weekdays[date('w')], $today);
        return $today;
    }

    //404页面
    public function _empty() {
        //使HTTP返回404状态码
        header("HTTP/1.0 404 Not Found");
        $this->title = "真的很抱歉，我们搞丢了页面……";
        $this->display(C('404_PAGE'));
    }

	/**
	 * @desc 网站升级,后台设置.如果设置了网站升级中，则只展示网站升级页面，用户无法访问其它页面.
	 * @author heyanlong 2013-07-30
	 */
	private function updating() {
        if (D("WebSettings")->getwebSettings("WEB_UPDATE_STATUS")) {
            $this->display(C('UPDATE_PAGE'));
            exit();
        }
    }

    /**
     * 自动登录
     * @author frank qian 2013-08-15
     */
    public function autoLogin() {
    	$user_str = $_COOKIE["USER_ID"];
    	$mid = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
        if (empty($mid)) {
        	//没有用户session且自动登录标示Cookie USER_ID 存在执行自动登录过程
        	if (!empty($user_str)) {
        		$ret = explode("|", $user_str);
        		$user_id = intval($ret[0]);
        		$user_info = D("Member")->find($user_id);
        		
        		if (!empty($user_info) && md5($user_info['password'] . $user_info['nickname']) == $ret[1]) {
        			$_SESSION[C('MEMBER_AUTH_KEY')] = $user_info['id'];
        			$_SESSION['nickname'] = $user_info['nickname'];
        			$_SESSION['face'] = empty($user_info['face'])?'face.jpg':$user_info['face'];
        		
        			//使用cookie过期时间来控制前台登陆的过期时间
        			$home_session_expire = intval(D("Variable")->getVariable("home_session_expire"));
        			cookie(md5("home_session_expire"), time(), $home_session_expire);
        		}
        	}
        }
        
        if(empty($_COOKIE[md5("home_session_expire")])) {
        	//自动登录标示Cookie USER_ID 时间过期
        	unset($_SESSION[C('MEMBER_AUTH_KEY')]);
        	unset($_SESSION['nickname']);
        	unset($_SESSION['face']);
        }
    }
}

?>