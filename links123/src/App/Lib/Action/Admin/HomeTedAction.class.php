<?php

class HomeTedAction extends CommonAction {

    public function index() {
		
    	$variableModel = M('Variable');
    		
    	$home_ted_hot_list = S('home_ted_hot');
    	if (!$home_ted_hot_list) {
    	
    		$home_ted_hot_list = $variableModel->where(array('vname' => 'home_ted_hot'))->find();
    		$home_ted_hot_list =  unserialize($home_ted_hot_list['value_varchar']);
    		S('home_ted_hot', $home_ted_hot_list);
    	}
    	$ted_list = S('ted_list');
    	if (!$ted_list) {
    			
    		$linksModel = M("Links");
    		$ted_ids = implode(',', array_keys($home_ted_hot_list));
    		$result = $linksModel->where('id in ('.$ted_ids.')')->select();
    			
    		$ted_list = array();
    		foreach ($result as $value) {
						
				$ted_list[$value['id']] = array('id' => $value['id'], 'title' => $value['title'], 'link_cn_img' => $value['link_cn_img'], 'status' => $home_ted_hot_list[$value['id']]);
			}
    		S('ted_list', $ted_list);
    	}
    	
    	$this->assign('ted_list', $ted_list);
		$this->display();
	}

	/**
	 * @see 添加TED数据
	 * 
	 * @param Int id:links id
	 * 
	 * @return status:0,添加失败;1,成功;-1,id已存在;
	 * 
	 * @author slate date:2013-09-20
	 */
	public function add() {
		
		$status = 1;
		
		$id = $this->_param('id');
		
		if ($id) {
			
			$variableModel = M('Variable');
			
			$home_ted_hot_list = S('home_ted_hot');
			if (!$home_ted_hot_list) {
				
				$home_ted_hot_list = $variableModel->where(array('vname' => 'home_ted_hot'))->find();
				$home_ted_hot_list =  unserialize($home_ted_hot_list['value_varchar']);
				S('home_ted_hot', $home_ted_hot_list);
			}
			
			if (!isset($home_ted_hot_list[$id])) {
				
				if (!$home_ted_hot_list) {
					$home_ted_hot_list = array();
				}
				
				$linksModel = M('Links');
				$home_ted_hot_list[$id] = 0;
				
				$variableModel->where(array('vname' => 'home_ted_hot'))->save(array('value_varchar' => serialize($home_ted_hot_list)));
				S('home_ted_hot', $home_ted_hot_list);
				$ted_list = S('ted_list');
				if (!$ted_list) {
					
					$ted_ids = implode(',', array_keys($home_ted_hot_list));
					$result = $linksModel->where('id in ('.$ted_ids.')')->select();
					
					$ted_list = array();
					foreach ($result as $value) {
						
						$ted_list[$value['id']] = array('id' => $value['id'], 'title' => $value['title'], 'link_cn_img' => $value['link_cn_img'], 'status' => $home_ted_hot_list[$id]);
					}
					
				} else {
					
					$result = $linksModel->where(array('id' => $id))->find();
					if (!$result['link_cn']) {
						
						import("@.ORG.VideoHooks");
						
						$videoHooks = new VideoHooks();
						//英文岛TED视频使用TED link资源
						if ($result['language'] == 2 && strpos($result['intro'], '（需翻墙') === FALSE) {
							$link = $result['link'];
						} else {
							$link = str_replace('\'', '', $videoHooks->match('/http:(.+?)\s/', $result['intro'], 0));
						}
					
						$videoInfo = $videoHooks->analyzer($link);
						$link_cn = $videoInfo['swf'];
					
						//英文岛TED视频如果需要翻墙，则采用国内资源
						if ($result['language'] == 2 && !$link_cn) {
							$link = str_replace('\'', '', $videoHooks->match('/http:(.+?)\s/', $result['intro'], 0));
							$videoInfo = $videoHooks->analyzer($link);
							$link_cn = $videoInfo['swf'];
						}
					
						if (!$videoHooks->getError()) {
							$links->where('id=' . $result['id'])->save(array('link_cn' => $link_cn, 'link_cn_img' => $videoInfo['img']));
						}
						$result['link_cn'] = $link_cn;
					}
					$ted_list[$id] = array('id' => $result['id'], 'title' => $result['title'], 'link_cn_img' => $result['link_cn_img'], 'status' => $home_ted_hot_list[$id]);
				}
				S('ted_list', $ted_list);
			} else {
				
				$status = -1;
			}
		} else {
			
			$status = 0;
		}
		
		echo $status;
	}
	
	public function start() {
		
		$status = 1;
		
		$id = $this->_param('id');
		if ($id) {
			
			$variableModel = M('Variable');
			
			$home_ted_hot_list = S('home_ted_hot');
			
			if (isset($home_ted_hot_list[$id])) {
				
				$linksModel = M('Links');
				$home_ted_hot_list[$id] = 1;
				
				$variableModel->where(array('vname' => 'home_ted_hot'))->save(array('value_varchar' => serialize($home_ted_hot_list)));
				S('home_ted_hot', $home_ted_hot_list);
				$ted_list = S('ted_list');
				$ted_list[$id]['status'] = 1;
				S('ted_list', $ted_list);
			} else {
				
				$status = -1;
			}
		} else {
			
			$status = 0;
		}
		
		echo $status;
	}
	
	public function del() {
		
		$status = 1;
		
		$id = $this->_param('id');
		if ($id) {
			
			$variableModel = M('Variable');
			
			$home_ted_hot_list = S('home_ted_hot');
			
			if (isset($home_ted_hot_list[$id])) {
				
				$linksModel = M('Links');
				unset($home_ted_hot_list[$id]);
				
				$variableModel->where(array('vname' => 'home_ted_hot'))->save(array('value_varchar' => serialize($home_ted_hot_list)));
				S('home_ted_hot', $home_ted_hot_list);
				$ted_list = S('ted_list');
				unset($ted_list[$id]);
				S('ted_list', $ted_list);
			} else {
				
				$status = -1;
			}
		} else {
			
			$status = 0;
		}
		
		echo $status;
	}
}

?>
