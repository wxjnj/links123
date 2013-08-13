<?php 
/**
 * @name IndexAction.class.php
 * @package Member
 * @desc 会员中心
 * @author frank qian 2013-08-13
 * @version 0.0.1
 */

import("@.Common.CommonAction");
class IndexAction extends CommonAction
{
	// 检查登录
	protected function checkLog($ajax = 0) {
		if (!isset($_SESSION[C('MEMBER_AUTH_KEY')]) || empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
			if ($ajax == 1) {
				echo "请先登录！";
				return false;
			} else {
				header("Location: " . __APP__ . "/");
			}
		} else {
			return true;
		}
	}
	
	public function index()
	{
		//
		$this->checkLog();
		//
		$mbrNow = M("Member")->getById($_SESSION[C('MEMBER_AUTH_KEY')]);
		if (empty($mbrNow['face'])) {
			$mbrNow['face'] = 'face.jpg';
		}
		$this->assign("mbrNow", $mbrNow);
		//
		$faces = array();
		for ($i = 1; $i != 120; ++$i) {
			$faces[] = $i . ".jpg";
		}
		$this->assign("faces", $faces);
		//
		$this->assign("funcNow", "index");
		//
		$this->display();
	}
	
	// 保存邮箱
	public function saveEmail() {
		//
		if (!$this->checkLog(1)) {
			return false;
		}
		//
		$email = $_POST['email'];
		if (empty($email)) {
			echo "email丢失！";
			return false;
		}
		//
		$member = M("Member");
		if ($member->where('id!=' . $_SESSION[C('MEMBER_AUTH_KEY')] . ' and email = \'' . $email . '\'')->find()) {
			echo "该email已被使用，请换一个！";
			return false;
		}
		//
		if (false === $member->where('id=' . $_SESSION[C('MEMBER_AUTH_KEY')])->setField('email', $email)) {
			Log::write('保存email失败：' . $member->getLastSql(), Log::SQL);
			echo "保存email失败！";
		} else {
			echo "saveOK";
		}
	}
	
	// 上传图片
	public function uploadPic() {
		import("@.ORG.UploadFile");
		$upload = new UploadFile();
		//设置上传文件大小
		$upload->maxSize = 10240000;
		//设置上传文件类型
		$upload->allowExts = explode(',', 'jpg,gif');
		//设置附件上传目录
		$path = realpath('./Public/Uploads/uploads.txt');
		$upload->savePath = str_replace('uploads.txt', $_REQUEST["folder"], $path) . '/';
		//设置需要生成缩略图，仅对图像文件有效
		$upload->thumb = false;
		//设置上传文件规则
		$upload->saveRule = uniqid;
		if (!$upload->upload()) {
			//捕获上传异常
			$this->error($upload->getErrorMsg());
		} else {
			//取得成功上传的文件信息
			$uploadList = $upload->getUploadFileInfo();
			//
			import("@.ORG.Image");
			$filename = $upload->savePath . $uploadList[0]['savename'];
			Image::thumb_db($filename, $filename, '', 0, 0, $_REQUEST["width"], $_REQUEST["height"], true);
			//
			$idNow = $_REQUEST["id"];
			if (empty($idNow)) {
				$idNow = "pic";
			}
			echo $idNow . '|' . $uploadList[0]['savename'];
		}
	}
	
	// 保存昵称
	public function saveNickname() {
		//
		if (!$this->checkLog(1)) {
			return false;
		}
		//
		$nickname = $_POST['nickname'];
		if (empty($nickname)) {
			echo "昵称丢失！";
			return false;
		}
		//
		$member = M("Member");
		if ($member->where('id!=' . $_SESSION[C('MEMBER_AUTH_KEY')] . ' and nickname = \'' . $nickname . '\'')->find()) {
			echo "该昵称已被使用，请换一个！";
			return false;
		}
		//
		if (false === $member->where('id=' . $_SESSION[C('MEMBER_AUTH_KEY')])->setField('nickname', $nickname)) {
			Log::write('保存昵称失败：' . $member->getLastSql(), Log::SQL);
			echo "保存昵称失败！";
		} else {
			$_SESSION['nickname'] = $nickname;
			echo "saveOK";
		}
	}
	
	// 保存密码
	public function savePassword() {
		//
		if (!$this->checkLog(1)) {
			return false;
		}
		//
		$password = $_POST['password'];
		if (empty($password)) {
			echo "密码丢失！";
			return false;
		}
		//
		$member = M("Member");
		$salt = $member->where('id=' . $_SESSION[C('MEMBER_AUTH_KEY')])->getField('salt');
		$password = md5(md5($password) . $salt);
		if (false === $member->where('id=' . $_SESSION[C('MEMBER_AUTH_KEY')])->setField('password', $password)) {
			Log::write('保存密码失败：' . $member->getLastSql(), Log::SQL);
			echo "保存密码失败！";
		} else {
			echo "saveOK";
		}
	}
	
}