<?php

/**
 * $Id: VideoHooks.class.php
 *
 * video处理类
 *
 * @package ORG
 *
 * @version 1.0
 *
 * @author slate $date: 2013-05-08$
 *
 */
class VideoHooks {
	const VERSION = '1.1.0';

	const WEBSITE_IQIYI = 'iqiyi.com';
	const WEBSITE_CNTV = 'cntv.cn';
	const WEBSITE_QQ = 'qq.com';
	const WEBSITE_YOUKU = 'youku.com';
	const WEBSITE_TUDOU = 'tudou.com';
	const WEBSITE_KU6 = 'ku6.com';
	const WEBSITE_SINA = 'sina.com.cn';
	const WEBSITE_56 = '56.com';
	const WEBSITE_LETV = 'letv.com';
	const WEBSITE_TED = 'ted.com';
	const WEBSITE_163 = '163.com';
	const WEBSITE_UMIWI = 'umiwi.com';

	const WEBSITE_SOHU = 'sohu.com';

	const USER_AGENT = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:19.0) Gecko/20100101 Firefox/19.0 Chromium/18.0.1025.168 Chrome/18.0.1025.168 Safari/535.19';

	private $_error;
	private $_url;
	private $_hasImg;
	private $_supportWebsite = array(
			'iqiyi.com'  => '_iqiyi',
			'cntv.cn'    => '_cntv',
			'qq.com'     => '_qq',
			'youku.com'  => '_youku',
			'tudou.com'  => '_tudou',
			'ku6.com'    => '_ku6',
			'sina.com.cn'=> '_sina',
			'56.com'     => '_56',
			'letv.com'   => '_letv',
			'sohu.com'   => '_sohu',
			'ted.com'	 => '_ted',
			'163.com'	 => '_163',
			'umiwi.com'	 => '_umiwi',
			'about.com'  => '_about',
			'videojug.com' => '_videojug',
            'hujiang.com' => '_hujiang',
			'kizphonics.com' => '_kizphonics',//
			'1kejian.com' => '_1kejian',
			'britishcouncil.org' => '_britishcouncil',//
			'ebigear.com' => '_ebigear',
			'bbc.co.uk' => '_bbc_co',
			'open.edu' => '_open_edu',
			'kekenet.com' => '_kekenet',
			'kumi.cn' => '_kumi',
			'wimp.com' => '_wimp',
			'youban.com' => '_youban',
			'hujiang.com' => '_hujiang',
			'literacycenter.net' => '_literacycenter',
			'peepandthebigwideworld.com' => '_peepandthebigwideworld',
			'ehow.co.uk' => '_ehow_co_uk',
			'starfall.com' => '_starfall',
			'kids.beva.com' => '_kids_beva',
			'englishcentral.com' => '_englishcentral',
			'nationalgeographic.com' => '_nationalgeographic'
	);


	public function __construct() {
	}

	public function getWebContent($url) {
		if (!$url) {
			$this->setError('URL IS NULL');
			return false;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$str = trim(curl_exec($ch));
		curl_close($ch);

		if (!$str) {
			$this->setError('IS NOT HTML');
			return false;
		}

		$str = mb_convert_encoding($str, 'utf-8', 'gbk,gb18030,utf8,GB2312');

		return $str;
	}

	/**
	 * 解析
	 * @param string $url 需要分析的url地址
	 * @param boolean $hasImg 是否需要返回缩略图
	 * @return array 成功返回array('title'=>'', 'img'=>'', 'url'=>'')，失败返回空数组：array()
	 */
	public function analyzer($url, $hasImg = true) {
		$url = trim($url);
		$data = array();
		if (!$url) {
			$this->setError('URL IS NULL');
			return $data;
		}
			
		$website = false;
		foreach ($this->_supportWebsite as $k=>$v) {
			if (false !== stripos($url, $k)) {
				$website = $v;
				break;
			}
		}
			
		if (!$website) {
			$this->setError('NOT ALLOW WEBISITE');
			return $data;
		}
			
		$this->_url = $url;
		$this->_hasImg = $hasImg;
			
		$data = $this->$website();
			
		return $data;
	}

	public function getError() {
		return $this->_error;
	}

	private function setError($error) {
		$this->_error = $error;
	}
	
	/**
	 * video.nationalgeographic.com 视频解析
	 * 
	 * @author slate date: 2013-08-03
	 */
	private function _nationalgeographic() {
	
		$html = $this->getWebContent($this->_url);
		$data = array();
		if (!$html) {
			return $data;
		}
		
		$data['url'] = $this->_url;
	
		$swfParams = array(
				'adprogramid' => '',
				'poster' => '',
				'permalink' => '',
				'siteid' => '',
				'caption' => '',
				'title' => '',
				'slug' => ''
		);
		
		foreach ($swfParams as $k => $v) {
				if ($k == 'slug') {
					
					$reg = 'slug\s*:\s*"http:\/\/"\+window.location.hostname\+"(.+?),';
				}else {
					$reg = $k.'\s*:\s*(.+?),';
				}
				$match = $this->match('/'.$reg.'/is', $html);
				$match = trim(trim($match, '"'), '\'');
				$match = str_replace('"+window.location.hostname+"', 'video.nationalgeographic.com', $match);
				$swfParams[$k] = $match;
		}
		
		$flashVars = array(
				'adenabled'   => 'true',
				'adprogramid' => $swfParams['adprogramid'],
				'caption'     => urlencode($swfParams['caption']),
				'img'         => $swfParams['poster'],
				'permalink'   => $swfParams['permalink'],
				'share'       => 'false',
				'restricted'  => 'false',
				'autoplay'    => 'false',
				'siteid'      => $swfParams['siteid'],
				'slug'        => 'http://video.nationalgeographic.com'.$swfParams['slug'],
				'vtitle'      => urlencode($swfParams['title']),
				'cuepoints'   => '',
				'vwidth'      => '610',
				'vheight'     => '383'
		);
		
		$flashVars_img = $flashVars['img'];
		$flashVars = $this->parsmEncode($flashVars);
		$flashVars = preg_replace('/img=(.*?)permalink/is', 'img='.$flashVars_img.'&permalink', $flashVars);
		
		$swf = '<object width="100%" height="100%" type="application/x-shockwave-flash" id="ngplayer" data="http://images.nationalgeographic.com/wpf/sites/video/swf/ngplayer_v2.5.swf">';
		$swf .= '<param name="allowfullscreen" value="true">
				<param name="allowscriptaccess" value="always">
				<param name="autoplay" value="false">
				<param name="bgcolor" value="#ffffff">
				<param name="menu" value="true">
				<param name="name" value="ngplayer">
				<param name="quality" value="best">
				<param name="wmode" value="opaque">';
		$swf .= '<param name="flashvars" value="'.$flashVars.'">';
		$swf .= '</object>';
		
		$data['swf'] = $swf;
	
		if ($this->_hasImg) {
			$data['img'] = $this->match('/\?url=(.*)/is', $flashVars['img']);
		}
	
		$data['media_type'] = 1;
	
		return $data;
	}
	
	private function _englishcentral() {
	
		$html = $this->getWebContent($this->_url);
		$data = array();
		if (!$html) {
			return $data;
		}
		$data['url'] = $this->_url;
	
		//$swf = $this->match('/<PARAM NAME="movie" VALUE="../../../(.*?)">/is', $html);
	
		$data['swf'] = $this->_url;
	
		$data['title'] = $this->match('/<title>(.+?)<\/title>/is', $html);
	
		if ($this->_hasImg) {
			//$data['img'] = $this->match('/"image":"(.+?)"/is', $swf);
		}
	
		$data['isFrame'] = true;
	
		return $data;
	}
	
	private function _kids_beva() {
	
		$html = $this->getWebContent($this->_url);
		$data = array();
		if (!$html) {
			return $data;
		}
		$data['url'] = $this->_url;
	
		//$swf = $this->match('/<PARAM NAME="movie" VALUE="../../../(.*?)">/is', $html);
	
		$data['swf'] = $this->_url;
	
		$data['title'] = $this->match('/<title>(.+?)<\/title>/is', $html);
	
		if ($this->_hasImg) {
			//$data['img'] = $this->match('/"image":"(.+?)"/is', $swf);
		}
	
		$data['media_type'] = 2;	//iframe
	
		return $data;
	}
	private function _starfall() {
	
		$html = $this->getWebContent($this->_url);
		$data = array();
		if (!$html) {
			return $data;
		}
		$data['url'] = $this->_url;
	
		//$swf = $this->match('/<PARAM NAME="movie" VALUE="../../../(.*?)">/is', $html);
		file_put_contents('test.txt', $html);
		$data['swf'] = $this->_url;
	
		$data['title'] = $this->match('/<title>(.+?)<\/title>/is', $html);
	
		if ($this->_hasImg) {
			//$data['img'] = $this->match('/"image":"(.+?)"/is', $swf);
		}
	
		$data['media_type'] = 2;	//iframe
	
		return $data;
	}
	/**
	 * echow.co.uk video
	 */
	private function _ehow_co_uk() {
	
		$html = $this->getWebContent($this->_url);
		$data = array();
		if (!$html) {
			return $data;
		}
		$data['url'] = $this->_url;
	
		$swf = $this->match('/<object(.*?)<\/object>/is', $html, 0);
	
		$data['swf'] = $swf;
	
		$data['title'] = $this->match('/<title>(.+?)<\/title>/is', $html);
	
		if ($this->_hasImg) {
			//$data['img'] = $this->match('/"image":"(.+?)"/is', $swf);
		}
	
		$data['media_type'] = 1;	//object
	
		return $data;
	}
	
	/**
	 * peepandthebigwideworld.com video
	 *
	 */
	private function _peepandthebigwideworld() {
	
		$html = $this->getWebContent($this->_url);
		$data = array();
		if (!$html) {
			return $data;
		}
		$data['url'] = $this->_url;
	
		$swf = $this->match('/<object(.*?)<\/object>/is', $html, 0);
	
		$data['swf'] = $swf;
	
		$data['title'] = $this->match('/<title>(.+?)<\/title>/is', $html);
	
		if ($this->_hasImg) {
			//$data['img'] = $this->match('/"image":"(.+?)"/is', $swf);
		}
	
		$data['media_type'] = 1;	//object
	
		return $data;
	
	}
	/**
	 * wimp.com video
	 *
	 * @todo
	 */
	private function _wimp() {
	
		$html = $this->getWebContent($this->_url);
		$data = array();
		if (!$html) {
			return $data;
		}
		$data['url'] = $this->_url;
	
		// 获取视频
		$swf = $this->match('/<div id="player">(.*?)<\/div>/is', $html);
	
		$data['swf'] = $swf;
	
		// 获取标题
		$data['title'] = $this->match('/<title>(.+?)<\/title>/is', $html);
		// 获取图片
		if ($this->_hasImg) {
			//$data['img'] = $this->match('/"image":"(.+?)"/is', $swf);
		}
	
		//$data['isObject'] = true;
	
		return $data;
	
	}
	
	/**
	 * kumi.cn video
	 * @todo 编码问题
	 */
	private function _kumi() {
	
		$html = $this->getWebContent($this->_url);
		$data = array();
		if (!$html) {
			return $data;
		}
		$data['url'] = $this->_url;
	
		// 获取视频
		$swf = $this->match('/write_flash\("(.*?)",/is', $html);
	
		$data['swf'] = $swf;
	
		// 获取标题
		$data['title'] = $this->match('/<title>(.+?)<\/title>/is', $html);
		// 获取图片
		if ($this->_hasImg) {
			//$data['img'] = $this->match('/"image":"(.+?)"/is', $swf);
		}
	
		//$data['isObject'] = true;
	
		return $data;
	
	}
	
	/**
	 * open.edu video
	 */
	private function _open_edu() {
	
		$html = $this->getWebContent($this->_url);
		$data = array();
		if (!$html) {
			return $data;
		}
	
		$data['url'] = $this->_url;
	
		// 获取视频
		$swf = $this->match('/<ins class="podcastOUVideo">(.*?)<br \/>/is', $html);
	
		$data['swf'] = $swf;
	
		// 获取标题
		$data['title'] = $this->match('/<title>(.+?)<\/title>/is', $html);
		// 获取图片
		if ($this->_hasImg) {
			//$data['img'] = $this->match('/"image":"(.+?)"/is', $swf);
		}
	
		$data['media_type'] = 1;	//object
	
		return $data;
	
	}
	
	/**
	 * bbc.co.uk video
	 */
	private function _bbc_co() {
	
		$html = $this->getWebContent($this->_url);
		$data = array();
		if (!$html) {
			return $data;
		}
	
		$data['url'] = $this->_url;
	
		$swf = $this->match('/var oeTags = \'(.*?)\';/is', $html);
		
		$swfUrl = str_replace(end(explode('/', $this->_url)), '', $this->_url);
		$swf = $swfUrl.$this->match('/src="(.*?)"/is', $swf);

		$data['swf'] = $swf;
	
		// 获取标题
		$data['title'] = $this->match('/<title>(.+?)<\/title>/is', $html);
		// 获取图片
		if ($this->_hasImg) {
			//$data['img'] = $this->match('/"image":"(.+?)"/is', $swf);
		}
	
		$data['media_type'] = 2;	//iframe
	
		return $data;
	
	}
	
	/**
	 * ebigear.com video
	 */
	private function _ebigear() {
	
		$html = $this->getWebContent($this->_url);
		$data = array();
		if (!$html) {
			return $data;
		}
	
		$data['url'] = $this->_url;
	
		// 获取视频
		$swf = $this->match('/<div id="tool"(.*?)>(.*?)<\/div>/is', $html, 2);
	
		$data['swf'] = str_replace(array('audioAuto=true'), array('audioAuto=false'), $swf);
	
		// 获取标题
		$data['title'] = $this->match('/<title>(.+?)<\/title>/is', $html);
		// 获取图片
		if ($this->_hasImg) {
			//$data['img'] = $this->match('/"image":"(.+?)"/is', $swf);
		}
	
		$data['media_type'] = 1;	//object
	
		return $data;
	
	}
	/**
	 * britishcouncil.org video
	 * @todo play
	 */
	private function _britishcouncil() {
	
		$html = $this->getWebContent($this->_url);
		$data = array();
		if (!$html) {
			return $data;
		}
	
		$data['url'] = $this->_url;
	
		// 获取视频
		$swf = $this->match('/<div class="swftools">(.*?)<\/div>/is', $html);
	
		$data['swf'] = $swf;
	
		// 获取标题
		$data['title'] = $this->match('/<title>(.+?)<\/title>/is', $html);
		// 获取图片
		if ($this->_hasImg) {
			//$data['img'] = $this->match('/"image":"(.+?)"/is', $swf);
		}
	
		$data['media_type'] = 1;	//object
		return $data;
	
	}
	/**
	 * 1kejian.com video
	 */
	private function _1kejian() {
	
		$html = $this->getWebContent($this->_url);
		$data = array();
		if (!$html) {
			return $data;
		}
	
		$data['url'] = $this->_url;
	
		// 获取视频
		$match = $this->match('/<div id="video">(.*?)<\/div>/is', $html);
		$swf = 'http://video.1kejian.com'.$this->match('/src="(.*?)"/is', $match);
		$data['swf'] = $swf;
	
		// 获取标题
		$data['title'] = $this->match('/<title>(.+?)<\/title>/is', $html);
		// 获取图片
		if ($this->_hasImg) {
			$data['img'] = $this->match('/my_pic=(.+?)\&/is', $match);
		}
	
		$data['media_type'] = 2;	//iframe
		return $data;
	
	}
	/**
	 * kizphonics video
	 */
	private function _kizphonics() {
	
		$html = $this->getWebContent($this->_url);
		$data = array();
		if (!$html) {
			return $data;
		}
	
		$data['url'] = $this->_url;
	
		// 获取视频
		$swf = $this->match('/jwplayer\(\'jwplayer-1\'\).setup\((.*?)\);<\/script>/is', $html);
	
		$data['swf'] = $swf;
	
		// 获取标题
		$data['title'] = $this->match('/<title>(.+?)<\/title>/is', $html);
		// 获取图片
		if ($this->_hasImg) {
			$data['img'] = $this->match('/"image":"(.+?)"/is', $swf);
		}
	
		//$data['isFlashVars'] = true;
		$data['media_type'] = 3;	//swfobject
		$data['swfUrl'] = 'http://www.kizphonics.com/wp-content/uploads/jw-player-plugin-for-wordpress/player/player.swf';
		return $data;
	
	}
	
	/**
	 * videojug video
	 */
	private function _videojug() {

		$html = $this->getWebContent($this->_url);
		$data = array();
		if (!$html) {
			return $data;
		}
		
		$data['url'] = $this->_url;
		
		// 获取视频
		$swf = $this->match('/<meta property="og:video" content="(.+?)"\/>/is', $html);
		if (!$swf) {
			$match =  $this->match('/new Player\(\'vjPlayerContainer\',(.*?)\);/is', $html);
			$match = explode(', ', str_replace('\'', '', $match));var_dump($match);
			$swf   = 'type='.$match[1].'&amp;id='.$match[2].'&amp;lcId='.trim($match[0]).'.&amp;host='.$match[3];
			$swf  .= '&amp;abtest=5&amp;'.str_replace('[', '', $match[7]).'&amp;'.$match[8].'&amp;'.$match[9].'&amp;'.str_replace(']', '', $match[10]);
		}
		$data['swf'] = $swf;
		
		// 获取标题
		$data['title'] = $this->match('/<meta property="og:title" content="(.+?)"\/>/is', $html);
		// 获取图片
		if ($this->_hasImg) {
			$data['img'] = $swf = $this->match('/<meta property="og:image" content="(.+?)"\/>/is', $html);
		}
		
		return $data;
		
	}

	/**
	 * about video
	 */
	private function _about() {
	
		$html = $this->getWebContent($this->_url);
		$data = array();
		if (!$html) {
			return $data;
		}
	
		$data['url'] = $this->_url;
	
		// 获取视频
		
		$zIvdoId = $this->match('/var zIvdoId="(.+?)"/', $html);
		$zIvdw = $this->match('/zIvdw=(.+?);/', $html);
		$zIvdh = $this->match('/zIvdh=(.+?);/', $html);
		$flashID = 'bcExperienceObj0';
		
		$swfParams = array(
				'playerID' => '',
				'autoStart' => '',
				'bgcolor' => '',
				'width' => $zIvdw,
				'height' => $zIvdh,
				'isVid' => '',
				'videoId' => $zIvdoId,
				'linkBaseURL' => '',
				'wmode' => '',
				'adServerURL' => '',
				'flashID' => $flashID
		);
		
		foreach ($swfParams as $k => $v) {
			if (!$v) {
				$reg = 'params.'.$k.' = "(.+?)";';
				$swfParams[$k] = $this->match('/'.$reg.'/is', $html);
				if (!$swfParams[$k]) {
					$reg = 'param\s+name=\"'.$k.'\"\s+value=\"(.+?)\"';
					$swfParams[$k] = $this->match('/'.$reg.'/is', $html);
				}
			}
		}
		if ($swfParams['playerID']) {
			$swf = 'http://c.brightcove.com/services/viewer/federated_f9?'.$this->parsmEncode($swfParams);
		}
	
		$data['swf'] = $swf;
	
		// 获取标题
		$data['title'] = $this->match('/<title>(.+?)<\/title>/is', $html);
		// 获取图片
		if ($this->_hasImg) {
			$data['img'] = $this->match('/<meta property="og:image" content="(.+?)"\/>/is', $html);
		}
	
		return $data;
	
	}
	
	/**
	 * umiwi video
	 */
	private function _umiwi() {

		$html = $this->getWebContent($this->_url);
		$data = array();
		if (!$html) {
			return $data;
		}

		$data['url'] = $this->_url;

		// 获取视频
		$swf = 'http://union.bokecc.com/flash/player.swf?vid=' . $this->match('/http:\/\/union.bokecc.com\/player\?vid=(.+?)\"/is', $html);

		$data['swf'] = $swf;

		// 获取标题
		$data['title'] = $this->match('/<title>(.+?)<\/title>/', $html);
		// 获取图片
		if ($this->_hasImg) {
			//$data['img'] = 'http://vimg1.ws.126.net' . $this->match('/image: \'http:\/\/vimg1.ws.126.net\' \+ \'(.+?)\' \+ \'.jpg\'/is', $html).'.jpg';
		}

		return $data;

	}

	/**
	 * 163 video
	 */
	private function _163() {

		$html = $this->getWebContent($this->_url);
		$data = array();
		if (!$html) {
			return $data;
		}

		$data['url'] = $this->_url;

		// 获取视频
		$swf = $this->match('/src: \'http:\/\/swf.ws.126.net\/(.+?)\'/is', $html);
		if ($swf) {
			$swf = 'http://swf.ws.126.net/' . $swf;
		}

		$data['swf'] = $swf;

		// 获取标题
		$data['title'] = $this->match('/<title>(.+?)<\/title>/', $html);
		// 获取图片
		if ($this->_hasImg) {//image: 'http://vimg1.ws.126.net' + '/image/snapshot_movie/2011/9/R/V/M7E3PUPRV' + '.jpg',
			$data['img'] = 'http://vimg1.ws.126.net' . $this->match('/image: \'http:\/\/vimg1.ws.126.net\' \+ \'(.+?)\' \+ \'.jpg\'/is', $html).'.jpg';
		}

		return $data;

	}

	/**
	 * ted video
	 */
	private function _ted() {
		$html = $this->getWebContent($this->_url);
		$data = array();
		if (!$html) {
			return $data;
		}

		//iframe swf mp4

		//div[id=videoHolder] object[id=streamingPlayerSWF] div[class=external_player]

		$data['url'] = $this->_url;

		// 获取视频
		/**$swf = stripcslashes($this->match('/"file":"(.+?)"/is', $html));

		if (!$swf) {
			$swf = $this->match('/"flashVars":{(.+?)}/is', $html);
			$swf = str_replace(array('":"', '"'), array('=', ''), $swf);
			//$swf = urlencode(stripcslashes(urldecode($swf)));
		}elseif (!$swf) {
			$swf = $this->match('/<iframe src="(.+?)"/is', $html);
		}**/
		
		$swf = $this->match('/var talkDetails = (.*?)<\/script>/is', $html);

		$data['swf'] = $swf;

		// 获取标题
		$data['title'] = $this->match('/<title>(.+?)<\/title>/', $html);
		// 获取图片
		if ($this->_hasImg) {
			$data['img'] = $this->match('/<meta property="og:image" content="(.+?)"/is', $html);
		}

		return $data;
	}

	private function _iqiyi() {
		$html = $this->getWebContent($this->_url);
		$data = array();
		if (!$html) {
			return $data;
		}

		$data['url'] = $this->_url;

		$videoId = $this->match('/"videoId"\s*:\s*"(\w+)"/', $html);
		$data['swf'] = $this->match('/var\s+flashUrl_old\s*=\s*\'(.+?)\'/', $html) . '?vid=' . $videoId;

		$data['title'] = $this->match('/"title":"(.+?)"/', $html);

		if ($this->_hasImg) {
			$data['img'] = $this->imgByQQShare($this->_url);
		}

		return $data;
	}

	private function _youku() {
		$html = $this->getWebContent($this->_url);
		$data = array();
		if (!$html) {
			return $data;
		}

		$data['url'] = $this->_url;

		$data['swf'] = $this->match('/id="link2"\s+value="(.*?)"\s*>/', $html);

		$pattern = array('/—在线播放/', '/—优酷网/', '/，视频高清在线观看/');
		$replacement = array('', '', '');
		$data['title'] = preg_replace($pattern, $replacement, $this->match('/<meta\s+name="title"\s+content="([^"]+)">/', $html));

		if ($this->_hasImg) {
			$data['img'] = $this->match('/screenshot=(.*?)"\s+/', $html);
		}

		return $data;
	}

	private function _tudou() {
		$html = $this->getWebContent($this->_url);
		$data = array();
		if (!$html) {
			return $data;
		}

		$data['url'] = $this->_url;
		$boolean = preg_match('/\/view\/([\w-]+)\/?/', $this->_url, $mateches);
		if ($boolean) { // 类似：http://www.tudou.com/programs/view/M5ZlqcwZtTQ/

			$data['swf'] = 'http://www.tudou.com/v/' . $mateches[1] . '/v.swf';

			$data['title'] = $this->match('/<span\s+id\s*=\s*"vcate_title"\s+.*>([^<]+?)<\/span>/', $html); // <span id="vcate_title" class="vcate_title">2013 Ford Focus ST 发动机声 and 百公里加速</span>

			if ($this->_hasImg) {
				$data['img'] = $this->match('/pic\s*:\s*\'(.+?)\'/', $html); // pic: 'http://i1.tdimg.com/164/609/134/p.jpg'
			}
		} else {
			if (stripos($this->_url, 'albumplay') === false) { // http://www.tudou.com/listplay/WG6NNGdL3Ps.html

				$lcode = $this->match('/lcode\s*=\s*\'([^\']+?)\'/', $html); // ,lcode = 'WG6NNGdL3Ps'
				$iid = $this->match('/iid:(\d+)/', $html); // iid:98250734
				$data['swf'] = 'http://www.tudou.com/l/' . $lcode . '/&iid=' . $iid . '/v.swf';
					
				$data['title'] = $this->match('/kw\s*:\s*"([^"]+?)"/', $html); // kw:"美媒爆911内幕五角大楼被导弹击中而非飞机"

				if ($this->_hasImg) {
					$data['img'] = $this->match('/pic:"(http:\/\/[^"]+?)"/', $html); // pic:"http://i1.tdimg.com/098/250/734/p.jpg"
				}
			} else { // http://www.tudou.com/albumplay/vFwUST3pLx4.html

				$acode = $this->match('/acode\s*=\s*\'(\w+)\'/', $html); // acode='vFwUST3pLx4'
				$iid = $this->match('/iid\s*:\s*(\d+)/', $html); // iid: 130561607
				$data['swf'] = 'http://www.tudou.com/a/' . $acode . '/&iid=' . $iid . '&resourceId=0_04_02_99/v.swf';

				$data['title'] = $this->match('/kw\s*:\s*["\']([^"]+?)["\']/', $html); // kw: "49天-第1集"
				if ($this->_hasImg) {
					$data['img'] = $this->match('/pic\s*:\s*["\'](http:\/\/[^"\',]+)["\']/', $html); // pic: "http://g1.ykimg.com/01270F1F46511C43547BD8000000009307566D-9498-B7E6-5168-C7F433C339CF"
				}
			}
		}

		return $data;
	}

	private function _qq() {
		$oldUrl = $this->_url;
		$this->_url = preg_replace('/\_\w+$/', '', $this->_url);
		//消除类似http://v.qq.com/cover/n/ngdlegvgf8v80g6.html?vid=9H9ozv5eAIs_0后面的“_0”造成获取的swf为http://imgcache.qq.com/tencentvideo_v1/player/TencentPlayer.swf?_v=20110829&vid=9H9ozv5eAIs_0不能播放
		$data = array();
		$support = $this->match('/(\/cover\/|\/detail\/|\/play\/|\/page\/)/', $this->_url);
		if (!$support) {
			$this->setError('暂不能解析该地址');
			return $data;
		}

		$html = $this->getWebContent($this->_url);
		if (!$html) {
			return $data;
		}
		$data['url'] = $oldUrl;
		$qqSwf = 'http://static.video.qq.com/TPout.swf?vid=';
		switch ($support) {
			case '/cover/':
				// 获取视频
				$vid = $this->match('/vid:"(\w+?)"/', $html); // vid:"h00112web9l"
				$data['swf'] = $qqSwf . $vid;
				// 获取标题
				$data['title'] = $this->match('/title:"(.+?)"/', $html); // title:"十二生肖"
				// 获取图片
				if ($this->_hasImg) {
					$data['img'] = trim($this->match('/pic\s*\:\s*"(.*?)"/', $html)); // pic :"http://i.gtimg.cn/qqlive/img/jpgcache/files/qqvideo/o/opq82bnh2jjjlha_h.jpg"
					if ($data['img'] == '') {
						$data['img'] = $this->imgByQQShare($oldUrl); //
					}
				}
				break;
			case '/page/':
				// 获取视频
				$vid = $this->match('/vid:"(\w+?)"/', $html); // vid:"8gRWZGQSFXa"
				$data['swf'] = $qqSwf . $vid;
				// 获取标题
				$data['title'] = $this->match('/title:"([^"]+)"/', $html); // title:" 七雄争霸巴彦淖尔联盟宣传片测试片原片",
				// 获取图片
				if ($this->_hasImg) {
					$data['img'] = $this->imgByQQShare($this->_url);
				}
				break;
			case '/detail/':
					
				break;
			case '/play/' :
				// 获取视频
				$vid = $this->match('/\/(\w+)\.html/', $this->_url);
				$data['swf'] = $qqSwf . $vid;
				// 获取标题
				$data['title'] = $this->match('/title:"([^"]+?)"/', $html); // title:" 爆笑恶搞之《疯狂的舟子》 何仙姑夫作品"
				if ($this->_hasImg) {
					$data['img'] = $this->imgByQQShare($this->_url);
				}
				break;
			default : $this->setError('暂不能解析该地址');return array();break;
		}

		return $data;
	}

	private function _sina() {
		$data = array();
		$html = $this->getWebContent($this->_url);
		if (!$html) {
			return $data;
		}
		$boolean = preg_match('/(\d+)[-|_](\d+)/', $this->_url);
		$data['url'] = $this->_url;
		if ($boolean) { // http://video.sina.com.cn/v/b/98436902-2430117877.html

			$data['swf'] = $this->match('/swfOutsideUrl\s*\:\s*\'(http\:\/\/.+\.swf?)\'/', $html); // swfOutsideUrl:'http://you.video.sina.com.cn/api/sinawebApi/outplayrefer.php/vid=98436902_2430117877_ZxixSiAxWTLK+l1lHz2stqkP7KQNt6nnjWm0ulOjLQleQ0/XM5GQatkE5iDWAtkEqDhATZA7dvgv1x8/s.swf'

			$data['title'] = $this->match('/title\s*\:\s*\'(.+?)\'/', $html); // title:'20130302周笔畅澳门水舞间之非凡之旅演出部分'

			if ($this->_hasImg) {
				$data['img'] = $this->match('/pic\s*:\s*\'(.+?)\'/', $html); // pic: 'http://p3.v.iask.com/522/269/98436902_2.jpg'
			}
		} else { // http://video.sina.com.cn/m/xzphqj_61897881.html

			$vid = $this->match('/vid:\'(\d+\|\d+)\'/', $html); // vid:'88870101|88870103'
			if (!$vid) {
				$vid = $this->match('/vid:\'(.+?)\'/', $html);
			}
			
			$vid = str_replace('|', '_', $vid);
			$data['swf'] = 'http://you.video.sina.com.cn/api/sinawebApi/outplayrefer.php/vid=' . $vid . '/s.swf';

			$data['title'] = $this->match('/<title>(.*?)_.*<\/title>/', $html); // <title>《向着炮火前进》第1集_高清在线观看_新浪大片_新浪网</title>

			if ($this->_hasImg) {
				$data['img'] = $this->match('/pic\s*:\s*\'(.+?)\'/', $html); // pic:'http://p3.v.iask.com/85/3/88870101_2.jpg'
			}
		}

		return $data;
	}


	private function _56() {
		$data = array();
		$boolean = preg_match('/\/v_(\w+)\.html|\_vid\-(\w+)\.html/i', $this->_url, $matches);
		if ($boolean) { // http://www.56.com/u68/v_NjI2NTkxMzc.html  http://www.56.com/w90/play_album-aid-10236058_vid-ODg0Mzg3ODA.html
			if ($matches[1]) {
				$vid = $matches[1];
			} else {
				$vid = $matches[2];
			}

			$url = 'http://vxml.56.com/json/' . $vid . '/?src=out';
			$html = $this->getWebContent($url);
			if (!$html) {
				$this->setError('获取页面出错');
				return $data;
			}
			$html = json_decode($html);
			if (isset($html->info)) {
				$data['url'] = $this->_url;
				// 获取视频
				$data['swf'] = 'http://player.56.com/v_' . $vid . '.swf';
				// 获取标题
				$data['title'] = $html->info->Subject;
				// 获取图片
				if ($this->_hasImg) {
					$data['img'] = $html->info->bimg;
				}
			} else {
				$this->setError('请求网页，返回错误');
			}
		} else {
			$this->setError('暂不支持此视频地址');
		}
		return $data;
	}

	private function _letv() {
		$data = array();
		$html = $this->getWebContent($this->_url);
		if (!$html) {
			return $data;
		}
		// 通过新浪微博分享 http://v.t.sina.com.cn/share/share.php?url=$url // /scope.picLst.*?,"(.*?)"/

		$data['url'] = $this->_url;
		// 获取视频
		$data['swf'] = 'http://i7.imgs.letv.com/player/swfPlayer.swf?autoPlay=0&id=' . $this->match('/vid\s*:\s*(\w+)/', $html); // vid:1929864  http://www.letv.com/player/x1929864.swf
		// http://img1.c0.letv.com/ptv/player/swfPlayer.swf?id=1929864
		// $data['swf'] = $this->match('/input\sname=""\stype="text"\svalue="(.*)"\s/', $html);
		// 获取标题
		$data['title'] = $this->match('/title\s*:\s*"(.+?)"/', $html); // title:"我叫郝聪明06"
		// 获取图片
		if ($this->_hasImg) {
			$data['img'] = $this->match('/pic\s*:\s*"(http:\/\/.+?)"/', $html); // pic:"http://i0.letvimg.com/yunzhuanma/201303/19/0023564646d1bb07cb15a6219cbe7890/thumb/2.jpg"
		}

		return $data;
	}

	/**
	 * sohu.com 搜狐视频
	 * http://tv.sohu.com/20130318/n369187910.shtml#3241
	 *
	 * @return array
	 */
	private function _sohu() {
		$data = array();
		$html = $this->getWebContent($this->_url); // iconv("GB2312", "UTF-8", $html);
		preg_match_all('/"og:(?:videosrc|title|image)"\s+content\s*=\s*"(.+?)"/is', $html, $matches); // ?: 非捕获匹配

		$data['url'] = $this->_url;
		// 获取视频
		$data['swf'] = $matches[1][0];
		// 获取标题
		$data['title'] = str_replace(' - 搜狐视频', '', $matches[1][1]);
		// 获取图片
		if ($this->_hasImg) {
			$data['img'] = $matches[1][2];
		}

		return $data;
	}

	/**
	 * ku6.com 酷六网
	 * http://v.ku6.com/special/show_6578054/H8iskaWn5zBCYZCf3aaKeg...html?nr=1
	 * @return array
	 */
	private function _ku6() {
		$html = $this->getWebContent($this->_url);
		$data = array();
		if (!$html) {
			return $data;
		}

		$data['url'] = $this->_url;
		// 获取视频
		$data['swf'] = $this->match('/<input\s+class="text_A"\s+value="(http\:\/\/.*\.swf)"/', $html);
		// <input class="text_A" value="http://player.ku6.com/refer/H8iskaWn5zBCYZCf3aaKeg../v.swf"
		//  id: "H8iskaWn5zBCYZCf3aaKeg..",
		// 获取标题
		$data['title'] = $this->match('/<h1\s+title\s*=\s*".*">(.*)<\/h1>/', $html); // <h1 title="【拍客】山村孩子孤岛艰难求学路之大山坚守的老师">【拍客】山村孩子孤岛艰难求学路之大山坚守的老师</h1>
		// 获取图片
		if ($this->_hasImg) {
			$data['img'] = $this->match('/cover\s*:\s*"(.*?)"/', $html); // cover: "http://vi0.ku6img.com/data1/p2/ku6video/2013/3/11/19/1368234392830_46155466_46155466/6.jpg"
		}

		return $data;
	}

	/**
	 * cntv.cn 中国网络电视台
	 * http://tv.cntv.cn/video/C40657/7997f6053489453a96352e58d54a96b3
	 * http://shaoer.cntv.cn/children/C30147/classpage/video/20110906/100432.shtml
	 * http://xiyou.cntv.cn/v-7cef4d76-8930-11e2-b474-a4badb4689bc.html
	 * @return array
	 */
	private function _cntv() {
		$html = $this->getWebContent($this->_url);
		$data = array();
		if (!$html) {
			return $data;
		}

		$data['url'] = $this->_url;
		if (strpos($this->_url, 'xiyou.cntv.cn') === false) {
			// 获取视频
			$videoId = $this->match('/"videoId",\s*"(\w+)"/', $html, 1); // "videoId", "VIDE100144371768"
			$videoCenterId = $this->match('/"videoCenterId",\s*"(\w+)"/', $html); // "videoCenterId","7997f6053489453a96352e58d54a96b3"
			$data['swf'] = 'http://player.cntv.cn/standard/cntvOutSidePlayer.swf?v=2.0.2013.1.30.0';
			$data['swf'] .= '&videoId=' . $videoId . '&videoCenterId=' . $videoCenterId;
			// 获取标题
			$data['title'] = $this->match('/<title>(.*?)\_.*中国网络电视台<\/title>/', $html); // <title>电视剧《触摸未来》预告片_触摸未来_中国网络电视台</title> ? : 表示非贪婪
			// 获取图片
			if ($this->_hasImg) {
				$data['img'] = $this->imgBySinaShare($this->_url);
			}
		} else { // 爱西柚
			// 获取视频
			$id = $this->match('/var\s+item_id\s*=\s*fid\s*=\s"([^"]+?)"\s*;/', $html); // var item_id = fid = "f8bb4c94-910e-11e2-89a1-001e0bd5b3ca";
			$data['swf'] = 'http://static.xiyou.cntv.cn/flash/player.swf?ver=2.090&id=' . $id;
			// 获取标题
			$data['title'] = $this->match('/<h1.*>.*>([^><]+?)<.*<\/h1>/', $html); // <h1 class="vtitle"><a href="#">俄罗斯：两女反应神速 幸运躲过车祸 </a></h1> (可能以后有变)
			// 获取图片
			if ($this->_hasImg) {
				$data['img'] = $this->imgByQQShare($this->_url);
			}
		}

		return $data;
	}
        
        /**
         * 解析kekenet.com的视频
         * @return array
         * @author  Adam $date2013-07-14$
         */
        private function _kekenet(){
            $html = $this->getWebContent($this->_url);
            $data = array();
            if (!$html) {
                return $data;
            }
            $data['url'] = $this->_url;
            // 获取视频
            $data['swf'] = $this->match('/<embed.*src=\'(http\:\/\/.*\.swf)\'/i', $html);
            // <embed src="http://k11.kekenet.com/Sound/child/shulaibao/S5_10[1].swf">
            preg_match_all('/<div class=\"e_title\">\s+<h1>(.*)<\/h1>/i', $html, $matches); 
            $data['title'] = $matches[1][0];
            return $data;
        }
        /**
         * 解析youban.com的视频
         * @return array
         * @author  Adam $date2013-07-14$
         */
        private function _youban(){
            $html = $this->getWebContent($this->_url);
            $data = array();
            if (!$html) {
                return $data;
            }
            $data['url'] = $this->_url;
            // 获取视频
            $data['swf'] = $this->match('/<embed.*id=\"FFvideogreen\".*src=(\'|\")(http\:\/\/.*\.swf)(\'|\")/i', $html , 2);
            // <embed id="FFvideogreen" src="http://swf.youban.com/swf/tongqu/13361218397029.swf">
            preg_match_all('/<div class=\"MediaTit">\s+.*<\/span>\s+.*<h1>(.*)<\/h1>/i', $html, $matches);
            $data['title'] = $matches[1][0];
            return $data;
        }
        /**
         * 解析hujiang.com的视频
         * @return array
         * @author  Adam $date2013-07-15$
         */
        private function _hujiang(){
            $html = $this->getWebContent($this->_url);
            $data = array();
            if (!$html) {
                return $data;
            }
            $data['url'] = $this->_url;
            // 获取视频
            $data['swf'] = $this->match('/<embed.*src=(\'|\")(http\:\/\/.*\.swf)(\'|\")/i', $html , 2);
            // <embed src="http://f1.hjfile.cn/file/201108/777770000198185dc.SWF">
            preg_match_all('/<h1 id\=\"detail_article_title\">\s*(.*)<\/h1>/i', $html, $matches); 
            $data['title'] = $matches[1][0];
            return $data;
        }
        /**
         * 解析literacycenter.net的视频
         * @return array
         * @author  Adam $date2013-07-15$
         */
        private function _literacycenter(){
            $html = $this->getWebContent($this->_url);
            $data = array();
            if (!$html) {
                return $data;
            }
            $data['url'] = $this->_url;
            $base_url = $this->match("/(http\:\/\/www\.literacycenter\.net\/.*\/)/i", $this->_url);
            // 获取视频
            $data['swf'] = $this->match('/<embed.*src=(\'|\")(.*\.swf)(\'|\")/i', $html , 2);
            $data['title'] = substr($data['swf'], 0, strpos($data['swf'], ".swf"));
            $data['swf'] = $base_url.$data['swf'];
            // <embed src="letters_en_lc.swf">
            return $data;
        }


        /**
	 * 通过QQ分享页来获取图片
	 * @param string $url 要分享的各视频网站详情页地址
	 * @return string
	 */
	public function imgByQQShare($url) {
		$qqShare = 'http://share.v.t.qq.com/index.php?c=share&a=index&url=' . $url;
		$shareHtml = $this->getWebContent($qqShare);
		return $this->match('/<img\s+src="(.*)"\s+id="video_minipic"\s*\/>/', $shareHtml); // <img src="http://pic2.qiyipic.com/thumb/20130210/v389678.jpg" id="video_minipic"/>
	}

	/**
	 * 通过新浪(sina)微博分享页来获取图片
	 * @param string $url
	 * @return string
	 */
	public function imgBySinaShare($url) {
		$sinaShare = 'http://v.t.sina.com.cn/share/share.php?url=' . urlencode($url);
		//$shareHtml = $this->getWebContent($sinaShare);
		$shareHtml = file_get_contents($sinaShare); // 无解：貌似用curl无法返回html
		return $this->match('/scope.picLst\s*=\s*\[.*?,\s*"(http:\/\/[^"]+?)"\s*\]\s*;/', $shareHtml);
		// scope.picLst = ["","http://p1.img.cctvpic.com/fmspic/2011/09/06/D465A477B3524a308B89C568DA8EC3BB-180.jpg"];
	}

	/**
	 * 使用正则匹配获取字符串
	 * @param string $pattern 需要匹配的正则表达式
	 * @param string $subject 用于匹配的字符串
	 * @param integer $num 需要获取匹配的第几个值
	 * @return string
	 */
	public static function match($pattern, $subject, $num = 1) {
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

	public static function parsmEncode($params,$isRetStr=true){
		$fieldStr = '';
		$spr = '';
		$result = array();
		foreach($params as $key=>$value){
			$value = urlencode($value);
			$fieldStr .= $spr.$key .'='. $value;
			$spr = '&';
			$result[$key] = $value;
		}
		return $isRetStr ? $fieldStr : $result;
	}
}