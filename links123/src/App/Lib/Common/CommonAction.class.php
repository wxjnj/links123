<?php

class CommonAction extends Action {

	protected function _initialize() {
		$this->_init();
	}

	/**
	 * 整站公共部件初始化
	 * @author heyanlong 2013-07-30
	 */
	private function _init() {
		session_start();

		//网站升级
		$this->updating();
		//自动登录
		$this->autoLogin();

		$variable = $this->_getVariable();
		$this->assign('cn_tip', $variable['cnTip']);
		$this->assign('en_tip', $variable['enTip']);
		$this->assign('directTip', $variable['directTip']);
		//留言
		if (D("SuggestionView")->isTodayHasNewSuggestion()) {
			$this->assign("newSuggestion", 1);
		}
		$this->getRootCats();

		//顶部日期
		$weekdays = array("周日", "周一", "周二", "周三", "周四", "周五", "周六");
		$this->assign('today', str_replace("*", $weekdays[date('w')], date('n月j日 * H:i:s')));

		//糖葫芦
		$this->assign("thl_list", D("Thl")->getThlListWithThlz());
		$this->assign('thlNow', '搜');
		$this->assign('tidNow', 1);
	}

	/**
	 * 获取头部设置信息
	 * @return array
	 * @author heyanlong 2013-07-30
	 */
	private function _getVariable() {
		$variable = M("Variable");
		$title = $variable->getByVname('title');
		$keywords = $variable->getByVname('Keywords');
		$description = $variable->getByVname('Description');
		$cnTip = $variable->getByVname('cn_tip');
		$enTip = $variable->getByVname('en_tip');
		$directTip = $variable->getByVname('directTip');
		$thl = $variable->getByVname('thl');
		$pauseTime = $variable->getByVname('pauseTime');

		return array(
			'title' => $title['value_varchar'],
			'keywords' => $keywords['value_varchar'],
			'description' => $description['value_varchar'],
			'cnTip' => $cnTip['value_varchar'],
			'enTip' => $enTip['value_varchar'],
			'directTip' => $directTip['value_varchar'],
			'thl' => $thl['value_varchar'],
			'pauseTime' => $pauseTime['value_int'],
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
	protected function getHeader($data=array()) {

		$variable = $this->_getVariable();
		$title = empty($data['title'])?'另客网 | 领先的全面导航 | 高效的组合搜索 | 独特的英语角（在建） - 学习':empty($data['title']);
		$keywords = empty($data['keywords'])?$variable['keywords']:empty($data['keywords']);
		$description = empty($data['description'])?$variable['description']:empty($data['description']);

		//注入
		$this->assign('title', $title);
		$this->assign('keywords', $keywords);
		$this->assign('description', $description);

		//显示模板文件
		$this->display("Public:newHeader");
	}

	/**
	 * 获取页底
	 */
	protected function getFooter() {
		$this->display("Public:newFooter");
	}

    // 获取所有根目录
    protected function getRootCats() {
        $cats = M("Category")->field('id, cat_name, intro, level')->where('status=1 and level=1')->order('sort asc')->select();
        foreach ($cats as &$value) {
            for ($i = 0; $i != $value['level']; ++$i) {
                $value['cat_name'] = '　' . $value['cat_name'];
            }
        }
        $this->assign("rootCats", $cats);
    }



    // 获取所有下级目录
    protected function _getSubCats($pid) {
        $pids = array();
        array_push($pids, $pid);
        //
        $cat = M("Category");
        $list = $cat->field('id')->where('status=1 and prt_id=' . $pid)->select();
        if (count($list) > 0) {
            foreach ($list as &$value) {
                $pids = array_merge($pids, $this->_getSubCats($value['id']));
            }
        }
        //
        return $pids;
    }

    // 获取左栏目录
    protected function getLeftMenu($rid) {
        $cat = M("Category");
        $leftMenuCn = $cat->where('status=1 and flag=1 and prt_id=' . $rid)->order('sort')->select();
        $leftMenuEn = $cat->where('status=1 and flag=2 and prt_id=' . $rid)->order('sort')->select();
        $this->assign("leftMenuCn", $leftMenuCn);
        $this->assign("leftMenuEn", $leftMenuEn);
    }

    // 当前日期（ajax）
    public function getDate() {
        $weekdays = array("周日", "周一", "周二", "周三", "周四", "周五", "周六");
        $today = date('n月j日 *');
        $today = str_replace("*", $weekdays[date('w')], $today);
        echo $today;
    }

    //404页面
    public function _empty() {
        //使HTTP返回404状态码
        header("HTTP/1.0 404 Not Found");
        $this->title = "真的很抱歉，我们搞丢了页面……";
        $this->display(C('404_PAGE'));
    }

	/**
	 * 网站升级，后台设置
	 * @author heyanlong 2013-07-30
	 */
	private function updating() {
        //如果设置了网站升级中，则只展示网站升级页面，用户无法访问其它页面
        if (D("WebSettings")->getwebSettings("WEB_UPDATE_STATUS")) {
            $this->display(C('UPDATE_PAGE'));
            exit();
        }
    }

    /**
     * 自动登录
     */
    public function autoLogin() {
        $user_str = cookie("USER_ID");
        if (!isset($_SESSION[C('MEMBER_AUTH_KEY')]) || empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
            if (!empty($user_str)) {
                $ret = explode("|", $user_str);
                $user_id = intval($ret[0]);
                $user_info = D("Member")->find($user_id);
                if (!empty($user_info) && md5($user_info['password'] . $user_info['nickname']) == $ret[1]) {
                    $_SESSION[C('MEMBER_AUTH_KEY')] = $user_info['id'];
                    $_SESSION['nickname'] = $user_info['nickname'];
                    $_SESSION['face'] = $user_info['face'];
                    if (empty($_SESSION['face'])) {
                        $_SESSION['face'] = "face.jpg";
                    }
                    //使用cookie过期时间来控制前台登陆的过期时间
                    $home_session_expire = D("Variable")->getVariable("home_session_expire");
                    cookie(md5("home_session_expire"), time(), $home_session_expire);
                }
            }
        } else {
            if (empty($user_str)) {
                //根据cookie检查session是否过期
                $time = cookie(md5("home_session_expire"));
                if (empty($time)) {
                    unset($_SESSION[C('MEMBER_AUTH_KEY')]);
                    unset($_SESSION['nickname']);
                    unset($_SESSION['face']);
                }
            }
        }
    }

}

?>