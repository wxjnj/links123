<?php

/**
 * 
 * @desc 新闻接口
 * 
 * @author slate date:2013-12-16
 *
 */
class NewsService{
	
	protected $userService = null;
	
	/**
	 * 栏目配置
	 * TODO 后期放到后台设置中|添加排序
	 */
	private $_column_config = array(
			array('type' => 0, 'name' => '英闻', 'url' => 'http://www.en84.com/', 'status' => 0, 'method' => '__en84'),
			array('type' => 1, 'name' => '头条', 'url' => 'http://sh.qihoo.com/index.html', 'status' => 0, 'method' => '__sh_qihoo'),
			array('type' => 2, 'name' => '博客', 'url' => 'http://blog.links123.cn/', 'status' => 0, 'method' => '__blog_links123'),
			array('type' => 3, 'name' => '文摘', 'url' => '', 'status' => 1),
			array('type' => 4, 'name' => '社交', 'url' => '', 'status' => 1)
	);
	
	public function __construct(){
		
		$this->userService = D('User','Service');
	}

	
	/**
	 * 获取所有新闻
	 * 
	 * @return array 
	 */
	public function getAllNews() {
	
		$data = array();
	
		$data['column'] = $this->_column_config;
	
		foreach ($this->_column_config as $k => $v) {
			$newsData = array();
			if ($v['status'] == 0) {
	
				$newsData = $this->$v['method']();
	
				$data['news'][$k]['pics'] = $newsData['imgNews'];
				$data['news'][$k]['texts'] = $newsData['news'];
				$data['news'][$k]['type'] = $k;
				$data['news'][$k]['more_url'] = $v['url'];
			}
		}
	
		return $data;
	}

	/**
	 * 抓取360热门新闻头条
	 */
	protected function __sh_qihoo() {

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
				
				$newsUrl = sprintf($baseURL,urlencode(stripslashes($this->tp_match('/href="(.*?)"/is', $v))),$type);
				
				$hotNews[$type]['list'][] = array(
						'url' => $newsUrl, 
						'title' => str_replace('"', '“',trim(strip_tags($this->tp_match('/<span class="title">(.*?)<\/span>/is', $v)))), 
						'img' => $this->tp_match('/src="(.*?)"/is', $v),
						'desc' => trim(str_replace(array("\n", '>'), '', strip_tags($this->tp_match('/<p>(.*?)<\/p>/is', $v))))
				);
			}
			$hotNews[$type]['cacheTime'] = $time + 8000;
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
		//图片新闻为前几篇，正好符合偏好显示为主
		return array('news'=>array_slice($list,$img_num,$news_num),'imgNews'=>array_slice($list,0,$img_num));
	}
	
	/**
	 * 抓取英闻
	 */
	protected function __en84() {
		$hotNews = S('EnglishNewsList');
		 
		if (!$hotNews) {
			
			$host = 'http://www.en84.com/';
			$str = file_get_contents($host);
			if ($str) {
				 
				$str = iconv('gbk', 'utf8', $str);
				 
				$news = $imgNews = array();
		   
				preg_match_all('/<div class="module cl xl xl1">(.*?)<\/div>/is', $str, $match);
		   
				foreach ($match[0] as $matchStr) {
					preg_match_all('/<li>(.*?)<\/li>/is', $matchStr, $match);
					foreach ($match[1] as $k => $v) {
						$v = preg_replace(array('/<label>(.*?)<\/label>/is', '/<em>(.*?)<\/em>/is'), '', $v);
						$url = $host . $this->tp_match('/href="(.*?)"/is', $v);
						
						$news[] = array(
								'url' => $url, 
								'title' => mb_substr(trim(str_replace(array("\n", '>'), '', strip_tags($v))), 0, 24, 'utf8'), 
								'img' => '', 
								'desc' =>''
						);
						 
					}
				}
				$imgNewsStr = $this->tp_match('/<ul class="slideshow">(.*?)<\/ul>/is', $str);
				preg_match_all('/<li(.*?)<\/li>/is', $imgNewsStr, $match);
				foreach ($match[0] as $k => $v) {
					
					$url = $host . stripslashes($this->tp_match('/href="(.*?)"/is', $v));
					
					$contentStr = iconv('gbk', 'utf8', file_get_contents($url));
					
					$desc = strip_tags($this->tp_match('/<p class="msonormal"(.*?)>(.*?)<\/p>/is', $contentStr, 0));
					
					$imgNews[] = array(
							'url' => $url, 
							'title' => str_replace('"', '“',trim(strip_tags($this->tp_match('/<span class="title">(.*?)<\/span>/is', $v)))), 
							'img' => $host . $this->tp_match('/src="(.*?)"/is', $v),
							'desc' => mb_substr(trim(str_replace(array("\n", '>'), '', strip_tags($desc))), 0, 150, 'utf8')
					);
				}
				$hotNews = array('news' => $news, 'imgNews' => $imgNews);
				S('EnglishNewsList', $hotNews, 18000);
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
	protected function __blog_links123() {
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
				 
				S('BlogNewsList', $hotNews, 8000);
				 
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