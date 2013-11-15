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
		//$this->updating();
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
			
			echo $ajax ? "请先登录！" : header("Location: " . __APP__ . "/");
			exit(0);
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
		$arrs = cache('variable');
		if (empty($arrs)) {
			$variable    = M("Variable");
			
			$vars = $variable->select();
			foreach ($vars as $row) {
				$arrs[$row['vname']] = empty($row['value_int']) ? $row['value_varchar'] : $row['value_int'];
			}
			cache('variable',$arrs);
		}
		return $arrs;
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
			'catPics' => M("CatPic")->where("rid = '%d'", $rid)->order('sort')->select(),
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
		$title = empty($data['title'])?'另客网 | 领先的全面导航 | 高效的组合搜索 | 独特的英语角':empty($data['title']);
		$keywords = empty($data['keywords'])?$variable['Keywords']:empty($data['keywords']);
		$description = empty($data['description'])?$variable['Description']:empty($data['description']);
		
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
        	foreach ($list as $value) {
            //foreach ($list as &$value) {
                //$pids = array_merge($pids, $this->_getSubCats($value['id']));
                //删除无效的二级级联查询 @slate
                $pids[] = $value['id'];
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
    	isset($_SESSION[C('MEMBER_AUTH_KEY')]) && 
    	$mid = @ intval($_SESSION[C('MEMBER_AUTH_KEY')]);
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
        			$_SESSION['skinId'] = $user_info['skin'];
        			$_SESSION['themeId'] = $user_info['themeId'];
        		
        			//使用cookie过期时间来控制前台登陆的过期时间
        			$home_session_expire = intval(D("Variable")->getVariable("home_session_expire"));
        			cookie(md5("home_session_expire"), time(), $home_session_expire);
        		}
        	}
        }
    }
    
    /**
     * @name getMyCats
     * @desc 获取目录
     * @author Frank 2013-08-28
     */
    public function getMyCats($flag = 1) {
    	$cat = M("Category");
    	$cats = $cat->field('id, cat_name, level')->where('status = 1 and level = 1')->order('sort ASC')->select();
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
    		$value['subCats'] = $cat->field('id, cat_name, level')->where("status = 1 and flag = '%s' and prt_id = '%s'", $flag, $value['id'])->order('sort ASC')->select();
    	}
    	$this->assign("cats", $cats);
    }
    
    /**
     * 获取皮肤列表
     * 
     * @return skins: 皮肤列表数据
     * 
     * @author slate date:2013-08-29
     */
    public function getSkins() {
    	
    	$skins = S('skinsList');
    	 
    	if (!$skins) {
    		
	    	$skins = array();
	    	
	    	$model = new Model();
			
	    	$sql = 'SELECT A.`categoryId`, A.`categoryName`, A.`categoryImg`, B.`skinId`, B.`skinName`, B.`smallSkin`, B.`middleSkin`, B.`skin`, B.`themeId`, B.`skinStyle`, B.`categoryId` AS cid '
	    	.'FROM `lnk_skin_category` A LEFT JOIN `lnk_skin` B ON A.`categoryId` = B.`categoryId` WHERE A.`status` = 0 AND B.`status` = 0';
			
			$result = $model->query($sql);
			
			foreach ($result as $skin) {
				$skin['theme'] = $this->getTheme($skin['themeId']);
				$skins['list'][$skin['categoryId']][] = $skin;
				$skins['category'][$skin['categoryId']] = array('categoryId' => $skin['categoryId'], 'categoryName' => $skin['categoryName']);
				$skins['skin'][$skin['skinId']] = array('skinId' => $skin['skin'], 'themeId' => $skin['themeId']);
			}
		
			S('skinsList', $skins);
    	}
    	
		return $skins;
    }
    
    /**
     * 获取热门音乐，数据来源百度音乐，1小时更新一次
     *
     * @return $songItemList: 音乐列表数据
     *
     * @author slate date:2013-09-15
     */
    public function getDayhotMusic() {
    	
    	$songItemList = S('songItemList');
    	
    	if (!$songItemList) {
    		
	    	$playUrl = 'http://play.baidu.com/?__methodName=mboxCtrl.playSong&__argsValue=';
	    	$url = 'http://music.baidu.com/top/dayhot';
	    	$imgUploadsPath = realpath('./Public/Uploads/Others/');
	    	$imgUrlHost = '/Public/Uploads/Others/';
	    	
	    	$str = file_get_contents($url);
	    	
	    	preg_match_all('/<li  data-songitem(.*?)<\/li>/is', $str, $match);
	    	
	    	$songItemList = array();
	    	
	    	foreach ($match[0] as $key => $value) {
	    	
	    		$data_songitem = str_replace('&quot;', '', $this->tp_match('/data-songitem = \'(.*?)\'/is', $value, 1));
	    		$data_songitem = $this->tp_match('/{songItem:{sid:(.*?),author:(.*?),sname:(.*?)}}/is', $data_songitem, -1);
	    	
	    		$songItem['sid'] = $data_songitem[1];
	    		$songItem['img'] = '';
	    		
	    		$img = $this->tp_match('/<img(.*?)src="(.*?)"(.*?)\/>/is', $value, 2);
	    		if ($img) {
		    		$imgFile =$songItem['sid'] . '.' . end(explode('.', $img));
		    		
		    		if (!file_exists($imgUploadsPath . '/' . $imgFile)) {
		    			file_put_contents($imgUploadsPath . '/' . $imgFile, file_get_contents($img));
		    		}
		    		
		    		$songItem['img'] = $imgUrlHost . $imgFile;
	    		}
	    		$songItem['url'] = $playUrl . $songItem['sid'];
	    		
	    		$songInfo = json_decode('{"author":"'.$data_songitem[2].'","sname":"'.$data_songitem[3].'"}', true);
				$songItem = array_merge($songItem, $songInfo);
	    	
	    		if ($songItem['sid']) {
	    			if ($songItem['img']) {
	    				$songItemList['top'][$songItem['sid']] = $songItem;
	    			} else {
	    				$songItemList['fair'][$songItem['sid']] = $songItem;
	    			}
	    		}
	    	}
	    	S('songItemList', $songItemList, 172800);
    	}
    	
    	return $songItemList;
    }

    /**
     * 获取豆瓣电影信息，数据来源豆瓣电影
     * 
     * @return $movieinfo: 电影信息数据
     * 
     * @author Lee date:2013-09-24
     */
    public function getDoubanMovieInfo() {
        $movieinfo = S('movieinfo');

        if (!$movieinfo) {
            $url = 'http://movie.douban.com/nowplaying/shanghai/';

            $str = file_get_contents($url);

            preg_match_all('/<ul class="lists">(.*?)>/is', $str, $match);
            $movieinfo = array();
            foreach ($match[0] as $key => $value) {
                //豆瓣id
                preg_match('/data-subject="(.*?)"/is', $value, $matchid);
                $movieinfo[$key]['id'] = $matchid[1];

                //电影详情
                $detailurl = 'http://movie.douban.com/subject/'.$movieinfo[$key]['id'];
                $detailstr = file_get_contents($detailurl);

                //电影海报(存入本地)
                preg_match('/<div id="mainpic" class="">(.*?)<\/div>/is', $detailstr, $matchimg);
                preg_match('/<img src="(.*?)"(.*?)\/>/is', $matchimg[1], $matchimgs);
                $movieinfo[$key]['img'] = $matchimgs[1];

                //编剧
                preg_match('/编剧<\/span>: (.*?)<\/span>/is', $detailstr, $matchwriter);
                $movieinfo[$key]['writer'] = strip_tags($matchwriter[1]);

                //又名
                preg_match('/又名:<\/span> (.*?)<br\/>/is', $detailstr, $matchaname);
                $movieinfo[$key]['aname'] = $matchaname[1];

                //语言
                preg_match('/语言:<\/span> (.*?)<br\/>/is', $detailstr, $matchlanguage);
                $movieinfo[$key]['language'] = $matchlanguage[1];

                //电影标题
                preg_match('/v:itemreviewed">(.*?)</is', $detailstr, $matchtitle);
                $movieinfo[$key]['title'] = $matchtitle[1];

                //电影年代
                preg_match('/year">(.*?)</is', $detailstr, $matchyear);
                $movieinfo[$key]['year'] = $matchyear[1];

                //电影标题(加上年代)
                $movieinfo[$key]['title'] = $movieinfo[$key]['title'].$movieinfo[$key]['year'];

                //评分
                preg_match('/v:average">(.*?)</is', $detailstr, $matchscore);
                $movieinfo[$key]['score'] = $matchscore[1];

                //导演
                preg_match('/v:directedBy"(.*?)>(.*?)</is', $detailstr, $matchdirector);
                $movieinfo[$key]['director'] = $matchdirector[2];

                //主演
                preg_match_all('/v:starring"(.*?)>(.*?)</is', $detailstr, $matchstars);
                $movieinfo[$key]['stars'] = $matchstars[2];

                //上映时间
                preg_match_all('/v:initialReleaseDate"(.*?)>(.*?)</is', $detailstr, $matchdate);
                $movieinfo[$key]['date'] = $matchdate[2];

                //片长
                preg_match('/片长:<\/span> (.*?)<br\/>/is', $detailstr, $matchruntime);
                $movieinfo[$key]['runtime'] = strip_tags($matchruntime[1]);

                //影评地址
                $movieinfo[$key]['filmreviewurl'] = 'http://movie.douban.com/subject/'.$movieinfo[$key]['id'].'/new_review';
            }
            
            S('movieinfo', $movieinfo, 172800);
        }

        return $movieinfo;
    }
        
    /**
     * 获取主题
     *
     *
     * @author slate date:2013-9-21
     */
    public function getTheme($themeId) {
    	 
    	$themeList = S('themeList');
    	
    	if (!$themeList) {
    		
    		$themeList = array();
    		
	    	$themeModel = M('SkinTheme');
	    
	    	$result = $themeModel->where(array('status' => 0))->select();
	    	 
	    	foreach ($result as $value) {
	    			
	    		$themeList[$value['themeId']] = $value['themeName'];
	    	}
	    	
	    	S('themeList', $themeList);
    	}
    
    	$theme = $themeList[$themeId];
    	
    	return $theme ? $theme : 'dark';
    }
    
    /**
     * 获取APP应用数据
     * 
     * @param  $appIds: app排序结果
     * 
     * @return $apps: app排序后结果集
     * 
     * @author slate date:2013-10-02
     */
    public function getApps($appIds) {
    	
    	$apps = array();
    	$appList = S('appList');
    	 
    	if (!$appList) {
    	
    		$appList = array();
    	
    		$appModel = M('App');
    		 
    		$result = $appModel->where(array('status' => 0))->select();
    		 
    		foreach ($result as $value) {
    	
    			$appList[$value['appId']] = $value;
    		}
    	
    		S('appList', $appList);
    	}
    	
    	if ($appIds) {
    		
    		$ids = explode(',', $appIds);
    		foreach ($ids as $appId) {
    			
    			$apps[$appId] = $appList[$appId];
    		}
    		
    	} else {
    		
    		$apps = $appList;
    	}
    	
    	return $apps;
    }
    
    public function tp_match($pattern, $subject, $num = 1) {
    	$boolean = preg_match($pattern, $subject, $matches);
    	$str = '';
    	if ($boolean) {
    		if ($num >= 0) {
    			$str = $matches[$num];
    		} else {
    			$str = $matches;;
    		}
    	}
    	return $str;
    }
}

?>