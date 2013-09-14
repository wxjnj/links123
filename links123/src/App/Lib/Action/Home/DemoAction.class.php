<?php
/**
 * @name DemoAction
 * 
 * @desc Demo: 新首页
 * 
 * @package Home
 * 
 * @version 3.0
 * 
 * @author slate date:2013-09-06
 */

import("@.Common.CommonAction");

class DemoAction extends CommonAction {
	
	/**
	 * @desc 新首页
	 * 
	 * @author slate date:2013-09-06
	 */
	public function index() {
		
		// 我的地盘
		$myarea = M("Myarea");
		
		//存在用户登录，获取用户的我的地盘
		$memberAuthKey = intval($this->_session(C('MEMBER_AUTH_KEY')));
		
		if (!$_SESSION['arealist']) {	
			$areaList = $myarea->where(array('mid' => $memberAuthKey))->select();
			
			foreach ($areaList as $value) {
				$_SESSION['arealist'][$value['id']] = $value;
			}
		}
		
		if (!$_SESSION['myarea_sort']) {
			
			$_SESSION['myarea_sort'] = array_keys($_SESSION['arealist']);
		}
		
		$this->getHeaderInfo();
		$this->display();
	}
	
	/**
	 * @name updateArea
	 * @desc 更新我的地盘
	 * @param string web_url
	 * @param string web_name
	 * @return 成功:0; 失败:1; 未登录或登录已失效: -1
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
				
			$now = time();
		
			$saveData = array(
					'url' => $url,
					'web_name' => $webname,
					'create_time' => $now
			);

			if (!$id) {
				$id = $myarea->where(array('mid' => $user_id))->add($saveData);
				if ($id) {
				
					$result = 1;
				} 
			} else {
				if (false !== $myarea->where(array('id' => $id, 'mid' => $user_id))->save($saveData)) {
					
					$result = 1;
				}
			}
				
			if ($result) {
				
				$_SESSION['arealist'][$id]['url'] = $url;
				$_SESSION['arealist'][$id]['web_name'] = $webname;
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
	 * @author slate date:2013-09-14
	 */
	public function sortArealist() {
		
		$result = 1;
		
		$area_list = $this->_post('area');
		
		if ($this->isAjax() && $area_list) {
			
			//去除排序中最后一位的空值
			unset($area_list[count($area_list)-1]);
			
			$_SESSION['myarea_sort'] = $area_list;
			
			$user_id = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
			
			$memberModel = M("Member");
			
			if ($user_id) {
	
				$myarea = M("Myarea");
				$list = $myarea->where("mid = '%d'", $user_id)->select();
				
				$memberModel->save(array('sort' => implode(',', $area_list)), array('id' => $user_id));
			
			}
			
		} else {
			
			$result = 0;
		}
		
		echo $result;
	}
}