<?php

/**
 * @name RoleAction.class.php
 * @package Admin
 * @desc 角色模块
 * @author lawrence UPDATE 2013-08-20
 * @version 1.0
 */
class RoleAction extends CommonAction {

    protected function _filter(&$map, &$param){
    	$name = $this->_param('name');
    	$status = $this->_param('status');
    	$remark = $this->_param('remark');
    	if (!empty($name)) {
    		$map['name'] = array('like',"%".$name."%");
    	}
    	$this->assign('name',$name);
    	$param['name'] = $name;
    	if(!empty($status)) {
    		$map['status'] = $status;
    	}
    	$this->assign('status',$map['status']);
    	$param['status']=$map['status'];
    	
    	if (!empty($remark)) {
    		$map['remark'] = array('like',"%".$remark."%");
    	}
    	$this->assign('remark',$remark);
    	$param['remark'] = $remark;
    }
	
	/**
	 * @desc 项目授权
	 * @see RoleAction::setApp()
	 */
    public function setApp() {
        $id = $this->_post('groupAppId');
        $groupId = $this->_post('groupId');
        $group=D('Role');
        $group->delGroupApp($groupId);
        $result = $group->setGroupApps($groupId, $id);
        if($result === false) {
            $this->error('项目授权失败！');
        }else {
            $this->success('项目授权成功！');
        }
    }

	/**
	 * @desc 项目列表
	 * @see RoleAction::app()
	 */
    public function app() {
    	$groupId = $this->_param('groupId');
        //读取系统的项目列表
        $node=D("Node");
        $list=$node->where('level = 1')->field('id, title')->select();
        foreach ($list as $vo){
			$appList[$vo['id']] = $vo['title'];
        }
        //读取系统组列表
        $group=D('Role');
        $list=$group->field('id, name')->select();
        foreach ($list as $vo){
            $groupList[$vo['id']] = $vo['name'];
        }
        
        //获取当前用户组项目权限信息
        $groupAppList = array();
        if(!empty($groupId)) {
            $this->assign("selectGroupId",$groupId);
            //获取当前组的操作权限列表
            $list=$group->getGroupAppList($groupId);
            foreach ($list as $vo){
                $groupAppList[$vo['id']] = $vo['id'];
            }
        }
        $this->assign("groupList", $groupList);
        $this->assign('groupAppList', $groupAppList);
        $this->assign('appList', $appList);
        $this->display();
        return;
    }

	/**
	 * @desc 模块授权
	 * @see RoleAction::setModule()
	 */
    public function setModule() {
        $id = $this->_post('groupModuleId');
        $groupId = $this->_post('groupId');
        $appId = $this->_post('appId');
        $group = D("Role");
        $group->delGroupModule($groupId, $appId);
        $result=$group->setGroupModules($groupId, $id);
        if($result === false) {
            $this->error('模块授权失败！');
        }else {
            $this->success('模块授权成功！');
        }
    }
	
	/**
	 * @desc 模块列表
	 * @see RoleAction::module()
	 */
    public function module() {
        $groupId = $this->_param('groupId');
        $appId = $this->_param('appId');
        $group=D("Role");
        //读取系统组列表
        $list=$group->field('id, name')->select();
        foreach ($list as $vo){
            $groupList[$vo['id']] = $vo['name'];
        }
        $this->assign("groupList", $groupList);
        if(!empty($groupId)) {
            $this->assign("selectGroupId", $groupId);
            //读取系统组的授权项目列表
            $list=$group->getGroupAppList($groupId);
            foreach ($list as $vo){
                $appList[$vo['id']] = $vo['title'];
            }
            $this->assign("appList", $appList);
        }
        $node=D("Node");
        if(!empty($appId)) {
            $this->assign("selectAppId", $appId);
            //读取当前项目的模块列表
            $where['level'] = 2;
            $where['pid'] = $appId;
            $nodelist = $node->field('id, title')->where($where)->select();
            foreach ($nodelist as $vo){
                $moduleList[$vo['id']]=$vo['title'];
            }
        }
        //获取当前项目的授权模块信息
        $groupModuleList = array();
        if(!empty($groupId) && !empty($appId)) {
            $grouplist=$group->getGroupModuleList($groupId, $appId);
            foreach ($grouplist as $vo){
                $groupModuleList[$vo['id']] = $vo['id'];
            }
        }
        $this->assign('groupModuleList', $groupModuleList);
        $this->assign('moduleList', $moduleList);
        $this->display();
        return;
    }

	/**
	 * @desc 操作授权
	 * @see RoleAction::setAction()
	 */
    public function setAction() {
        $id = $this->_post('groupActionId');
        $groupId = $this->_post('groupId');
        $moduleId = $this->_post('moduleId');
        $group=D("Role");
        $group->delGroupAction($groupId, $moduleId);
        $result=$group->setGroupActions($groupId, $id);
        if($result === false) {
            $this->error('操作授权失败！');
        }else {
            $this->success('操作授权成功！');
        }
    }

	/**
	 * @desc 操作列表
	 * @see RoleAction::action()
	 */
    public function action() {
        $groupId = $this->_param('groupId');
        $appId = $this->_param('appId');
        $moduleId = $this->_param('moduleId');
        $group=D("Role");
        //读取系统组列表
        $grouplist=$group->field('id, name')->select();
        foreach ($grouplist as $vo){
            $groupList[$vo['id']] = $vo['name'];
        }
        $this->assign("groupList", $groupList);
        if(!empty($groupId)) {
            $this->assign("selectGroupId", $groupId);
            //读取系统组的授权项目列表
            $list=$group->getGroupAppList($groupId);
            foreach ($list as $vo){
                $appList[$vo['id']] = $vo['title'];
            }
            $this->assign("appList", $appList);
        }
        if(!empty($appId)) {
            $this->assign("selectAppId", $appId);
            //读取当前项目的授权模块列表
            $list=$group->getGroupModuleList($groupId, $appId);
            foreach ($list as $vo){
                $moduleList[$vo['id']] = $vo['title'];
            }
            $this->assign("moduleList", $moduleList);
        }
        $node=D("Node");
        if(!empty($moduleId)) {
            $this->assign("selectModuleId", $moduleId);
            //读取当前项目的操作列表
            $map['level'] = 3;
            $map['pid'] = $moduleId;
            $list=$node->where($map)->field('id, title')->select();
            if($list) {
                foreach ($list as $vo){
                    $actionList[$vo['id']] = $vo['title'];
                }
            }
        }

        //获取当前用户组操作权限信息
        $groupActionList = array();
        if(!empty($groupId) && !empty($moduleId)) {
            //获取当前组的操作权限列表
            $list=$group->getGroupActionList($groupId, $moduleId);
            if($list) {
				foreach ($list as $vo){
					$groupActionList[$vo['id']] = $vo['id'];
				}
            }
        }
        $this->assign('groupActionList', $groupActionList);
        $this->assign('actionList', $actionList);
        $this->display();
        return;
    }
	
	/**
	 * @desc 用户授权
	 * @see RoleAction::setUser()
	 */
    public function setUser() {
        $id = $this->_post('groupUserId');
        $groupId = $this->_post('groupId');
        $group = D("Role");
        $group->delGroupUser($groupId);
        $result = $group->setGroupUsers($groupId, $id);
        if($result === false) {
            $this->error('授权失败！');
        }else {
            $this->success('授权成功！');
        }
    }
	
	/**
	 * @desc 用户列表
	 * @see RoleAction::user()
	 */
    public function user() {
        //读取系统的用户列表
        $user=D("User");
        $list2 = $user->field('id, account, nickname')->select();
        foreach ($list2 as $vo){
            $userList[$vo['id']] = $vo['account'].' '.$vo['nickname'];
        }
        $group=D("Role");
        $list=$group->field('id, name')->select();
        foreach ($list as $vo){
            $groupList[$vo['id']] = $vo['name'];
        }
        $this->assign("groupList",$groupList);
        //获取当前用户组信息
        $groupId = $this->_param('id');
        $groupUserList = array();
        if(!empty($groupId)) {
            $this->assign("selectGroupId", $groupId);
            //获取当前组的用户列表
            $list = $group->getGroupUserList($groupId);
            foreach ($list as $vo){
                $groupUserList[$vo['id']] = $vo['id'];
            }
        }
        $this->assign('groupUserList', $groupUserList);
        $this->assign('userList', $userList);
        $this->display();
        return;
    }

    public function _before_edit(){
       $Group=D('Role');
       //查找满足条件的列表数据
       $list=$Group->field('id, name')->select();
       $this->assign('list', $list);
    }

    public function _before_add(){
       $Group=D('Role');
       //查找满足条件的列表数据
       $list=$Group->field('id, name')->select();
       $this->assign('list', $list);
    }
}