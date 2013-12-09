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
		
		//自留地
		$myareaModel = M("Myarea");
		$scheduleModel = M("Schedule");

		$user_id = $this->userService->getUserId();

		if ($user_id) {

			$memberModel = M("Member");
			$mbrNow = $memberModel->where(array('id' => $user_id))->find();
			$_SESSION['myarea_sort'] = $mbrNow['myarea_sort'] ? explode(',', $mbrNow['myarea_sort']) : '';
			
			$skinId = session('skinId');
			if (!$skinId) {
				$skinId = cookie('skinId');
			}
		} else {
			
			$skinId = cookie('skinId');
		}
		
		//快捷皮肤
		
		$skins = $this->getSkins();
		
		$this->assign("skinId", $skinId);
		$this->assign("skin", $skins['skin'][$skinId]);
		$this->assign("skinList", $skins['list']);
		$this->assign("skinCategory", $skins['category']);
		
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
		}
		
		if (!$schedule_list[0]['datetime']) {
			$schedule_list[0]['datetime'] = time();
			$schedule_list[0]['content'] = '快来创建第一个日程';
		}
		
		cookie(md5('schedule_list'), $schedule_list);
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
		
		//TED 发现
		$ted_list = S('ted_list');
		if (!$ted_list) {
			
			$linksModel = M("Links");
			$ted_ids = '124,143,144,155,158,166,171';	//TODO 放到后台管理
			$result = $linksModel->where('id in ('.$ted_ids.')')->limit(5)->select();
			
			$ted_list = array();
			foreach ($result as $value) {
				
				$ted_list[$value['id']] = array('id' => $value['id'], 'title' => $value['title'], 'link_cn_img' => $value['link_cn_img']);
			}
			S('ted_list', $ted_list);
		}
		$this->assign('ted_list', $ted_list);
		
		//图片精选
		
		$this->getHeaderInfo();
		$this->display();
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
	
		$result = 0;
	
		if ($this->userService->isLogin()) {

			$user_id = $this->userService->getUserId();
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
			
			if ($this->userService->isLogin()) {

				$user_id = $this->userService->getUserId();
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
		
		$result = 0;
		
		if ($id) {
			
			$result = 1;
			
			if ($this->userServcie->isLogin()) {

				$user_id = $this->userService->getUserId();
				
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
		
		$result = 0;
		
		if ($id) {
			if ($this->userService->isLogin()) {

				$user_id = $this->userService->getUserId();
				
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
	
		$result = 0;
	
		if ($this->userService->isLogin()) {

			$user_id = $this->userService->getUserId();
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
			
			//去除排序中最后一位的空值
			unset($area_list[count($area_list)-1]);
			
			$_SESSION['myarea_sort'] = $area_list;
			
			$memberModel = M("Member");
			
			if ($this->userService->isLogin()) {

				$user_id = $this->userService->getUserId();
	
				$memberModel->where(array('id' => $user_id))->save(array('myarea_sort' => implode(',', $area_list)));
			
			}
			
		} else {
			
			$result = 0;
		}
		
		echo $result;
	}
}