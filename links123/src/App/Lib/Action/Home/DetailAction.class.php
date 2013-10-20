<?php
import("@.Common.CommonAction");
class DetailAction extends CommonAction
{
	public function index() {
		
		import("@.ORG.String");
		import("@.ORG.VideoHooks");
		
		$id = intval($this->_get('id'));
		$id = max($id, 1);
		
		$links = M("Links");
		$linkNow = $links->getById($id);
        //如果没有对应记录，则转到404页面@author Adam 2013.10.20
        if(empty($linkNow)){
            $this->_empty();
            return;
        }
		
		$GLOBALS['_title'] = $linkNow['title'];
		$linkNow['intro'] = nl2br($linkNow['intro']);
		$linkNow["intro"] = checkLinkUrl($linkNow["intro"]);
		
		// 防采集
		$rand = randString();
		$tempstr = $rand['tempstr'];
		$linkNow["title"] = $linkNow["title"] . $tempstr;
		$linkNow["intro"] = $linkNow["intro"] . $tempstr;
		$this->assign("bq1", $rand['bq1']);
		$this->assign("bq2", $rand['bq2']);
		
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
		$pg = max($p, 1);
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
		
		//session防垃圾和时间设置 如果提交了评论
		if (preg_match("#saveComment#i", $_SERVER['HTTP_REFERER'])){
			//下一次提交评论的最小时间戳(时间戳 +两次评论的最小间隔时间)
			$this->timestamp = $_SESSION['timestamp']= time()+D("WebSettings")->getwebSettings("COMMENT_BETWEEN_TIME") ;
		}
		//随机的评论框名称
		$this->comment = $_SESSION['comment'] = 'comment'.rand(1000, 9999);
		
		$this->assign('linkNow', $linkNow);
		$this->assign("linkTitle", $linkNow['title']);
		$this->display();
	}
	
	/**
	 * @name SEOTitle
	 * @desc 设置seo头信息
	 * @param int $catid
	 * @author Frank UPDATE 2013-08-20
	 */
	public function SEOTitle($catid) {
			
		$cid = intval($this->_param('cid'));
		$variable = M("Variable");
		$title = $variable->getByVname('title');
	
		$cat = M("Category");
		if ($cid) {
			$_SESSION['catNow'] && $_SESSION['catNow'] = null;
		} else if (!$_SESSION['catNow'] || $_SERVER['REQUEST_URI'] == '/') {
			$cid = $cat->where('status = 1 and level = 1')->min('id');
		}
	
		$_SESSION['catNow'] = $cat->getById($cid);
	
		if ($catid) {
			$_SESSION['catNow'] = $cat->getById($catid);
			$_title = $GLOBALS['_title'] . ' | ' . $_SESSION['catNow']['cat_name'] . ' | 另客网';
		} else {
			$_firstTiles3 = $GLOBALS['firstTiles3'][0] . ($GLOBALS['firstTiles3'][1] ? '、' . $GLOBALS['firstTiles3'][1] . ($GLOBALS['firstTiles3'][2] ? '、' . $GLOBALS['firstTiles3'][2] : '') : '');
			import("@.ORG.Page");
			$p = new Page();
			
			$flag = $p->getCurrentPage() > 1 ? ' 第' . $p->getCurrentPage() . '页' : '';
			$_title = $title['value_varchar'] . ' - ' . $_SESSION['catNow']['cat_name'] . $flag;
			
			$Description = $variable->getByVname('Description');
			$GLOBALS['_description'] = $Description['value_varchar'] . '。本页热门网站有：' . $_firstTiles3;
		}
		$this->assign('title', $_title);
		$this->assign('Description', strip_tags(str_replace("\n","",$GLOBALS['_description'])));
	}
} 