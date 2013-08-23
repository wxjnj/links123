<?php 
/**
 * @name IndexAction
 * @desc 会员中心
 * @package Member
 * @version 1.0
 * @author frank qian 2013-08-13
 */

import("@.Common.CommonAction");
class IndexAction extends CommonAction
{
	/**
	 * @desc 会员中心默认页面
	 * @name Index
	 * @see IndexAction::index()
	 */
	public function index()
	{
		$this->checkLog();
		$mid = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
		$mbrNow = M("Member")->getById($mid);
		$mbrNow['face'] =  $mbrNow['face'] ? $mbrNow['face'] : 'face.jpg';
		
		for ($i = 1; $i != 120; ++$i) {
			$faces[] = $i . ".jpg";
		}
		
		$this->assign("mbrNow", $mbrNow);
		$this->assign("faces", $faces);
		$this->assign("funcNow", "index");
		
		$this->display();
	}
	
	/**
	 * @name saveEmail
	 * @desc 保存邮箱
	 * @param string email 邮箱
	 * @return boolean
	 * @author Frank UPDATE 2013-08-21
	 */
	public function saveEmail() {
		$this->checkLog(1);
		
		$email = $this->_param('email');
		$mid = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
		if (empty($email)) {
			echo "email丢失！";
			return false;
		}
		
		$member = M("Member");
		if ($member->where("id <> '%d' and email = '%s'", $mid, $email)->find()) {
			echo "该email已被使用，请换一个！";
			return false;
		}
		
		if (false === $member->where("id = '%d'", $mid)->setField('email', $email)) {
			Log::write('保存email失败：' . $member->getLastSql(), Log::SQL);
			echo "保存email失败！";
		} else {
			echo "saveOK";
		}
	}
	
	/**
	 * @name uploadPic
	 * @desc 上传图片
	 * @param string folder
	 * @param string width
	 * @param string height
	 * @param int id
	 * @return string
	 * @author Frank UPDATE 2013-08-21
	 */
	public function uploadPic() {
		$folder = $this->_param('folder');
		$width = $this->_param('width');
		$height = $this->_param('height');
		$id = $this->_param('id');
		
		import("@.ORG.UploadFile");
		$upload = new UploadFile();
		$upload->maxSize = 10240000;
		$upload->allowExts = explode(',', 'jpg,gif');
		$path = realpath('./Public/Uploads/uploads.txt');
		$upload->savePath = str_replace('uploads.txt', $folder, $path) . '/';
		$upload->thumb = false;
		$upload->saveRule = uniqid;
		
		if (!$upload->upload()) {
			$this->error($upload->getErrorMsg());
		} else {
			$uploadList = $upload->getUploadFileInfo();
			import("@.ORG.Image");
			$filename = $upload->savePath . $uploadList[0]['savename'];
			Image::thumb_db($filename, $filename, '', 0, 0, $width, $height, true);
			
			$idNow = $id ? $id : 'pic';
			echo $idNow . '|' . $uploadList[0]['savename'];
		}
	}
	
	/**
	 * @name saveNickName
	 * @desc 修改昵称
	 * @param string nickname 昵称
	 * @return boolean
	 * @author Frank UPDATE 2013-08-21
	 */
	public function saveNickname() {
		$this->checkLog(1);
		$nickname = $this->_param('nickname');
		
		if (empty($nickname)) {
			echo "昵称丢失！";
			return false;
		}
		
		$member = M("Member");
		$mid = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
		if ($member->where("id <> '%d' and nickname = '%s'", $mid, $nickname)->find()) {
			echo "该昵称已被使用，请换一个！";
			return false;
		}
		
		if (false === $member->where("id = '%d'", $mid)->setField('nickname', $nickname)) {
			Log::write('保存昵称失败：' . $member->getLastSql(), Log::SQL);
			echo "保存昵称失败！";
		} else {
			$_SESSION['nickname'] = $nickname;
			echo "saveOK";
		}
	}
	
	/**
	 * @name savePassword
	 * @desc 修改密码
	 * @param string password 密码
	 * @return boolean
	 * @author Frank UPDATE 2013-08-21
	 */
	public function savePassword() {
		$this->checkLog(1);
		$password = $this->_param('password');
		$mid = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
		
		if (empty($password)) {
			echo "密码丢失！";
			return false;
		}
		$member = M("Member");
		$salt = $member->where("id = '%d'", $mid)->getField('salt');
		$password = md5(md5($password) . $salt);
		if (false === $member->where("id = '%d'", $mid)->setField('password', $password)) {
			Log::write('保存密码失败：' . $member->getLastSql(), Log::SQL);
			echo "保存密码失败！";
		} else {
			echo "saveOK";
		}
	}
	
	/**
	 * @name saveCollect
	 * @desc 收藏
	 * @param int lnk_id
	 * @return string
	 */
	public function saveCollect() {
		$this->checkLog(1);
		$lnkId = intval($_POST['lnk_id']);
		$link = $_POST["link"];
		
		if (empty($lnkId) || empty($link)) {
			echo "链接丢失！";
			return false;
		}
		
		$collection = M("Collection");
		$data['link'] = $link;
		$data['mid'] = $_SESSION[C('MEMBER_AUTH_KEY')];
		
		if ($collection->where($data)->find()) {
			echo "已经收藏过了！";
			return false;
		}
		
		$data['lnk_id'] = $lnkId;
		$data['create_time'] = time();
		if (false !== $collection->add($data)) {
			$links = M("Links");
			if (false !== $links->where("link = '%s'", $link)->setInc('collect_num')) {
				Log::write('增加链接收藏数量失败：' . $links->getLastSql(), Log::SQL);
			}
			echo "saveOK";
		} else {
			Log::write('收藏失败：' . $collection->getLastSql(), Log::SQL);
			echo "收藏失败！";
		}
	}
	
	/**
	 * @desc 取消收藏
	 * @name savePassword
	 * @package Members
	 * @param lnk_id
	 * @return string
	 */
	public function del_collect() {
		$this->checkLog(1);
		$lnkId = intval($_POST["lnk_id"]);
		if (empty($lnkId)) {
			echo "链接id丢失！";
			return false;
		}
		
		$collection = M("Collection");
		$condition['lnk_id'] = $lnkId;
		$condition['mid'] = $_SESSION[C('MEMBER_AUTH_KEY')];
		
		if (!$collection->where($condition)->find()) {
			echo "无此收藏！";
			return false;
		}
		
		if (false !== $collection->where($condition)->delete()) {
			$links = M("Links");
			$linkNow = $links->getById($lnkId);
			if (false !== $links->where("link = '%s'", $linkNow['link'])->setDec('collect_num')) {
				Log::write('减少链接收藏数量失败：' . $links->getLastSql(), Log::SQL);
			}
			echo "delOK";
		} else {
			Log::write('取消收藏失败：' . $collection->getLastSql(), Log::SQL);
			echo "取消收藏失败！";
		}
	}
	
	/**
	 * @desc 设定头像
	 * @name saveFace
	 * @package Members
	 * @param face
	 * @return string
	 * @author Frank UPDATE 2013-08-21
	 */
	public function saveFace() {
		$this->checkLog(1);
		$face = $this->_param('face');
		if (empty($face)) {
			echo "头像丢失！";
			return false;
		}
		$mid = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
		$member = M("Member");
		if (false === $member->where("id = '%d'", $mid)->setField('face', $face)) {
			Log::write('设定头像失败：' . $member->getLastSql(), Log::SQL);
			echo "设定头像失败！";
		} else {
			$_SESSION['face'] = $face;
			echo "saveOK";
		}
	}
}