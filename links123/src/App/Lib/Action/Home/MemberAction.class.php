<?php
//已分离，待确认后删除该文件
import("@.Common.CommonAction");
class MemberAction extends CommonAction {
	
    // 检查登录
    protected function checkLog($ajax = 0) {
        if (!$this->userService->isLogin()) {
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

    // 会员主页
    public function index() {
        //
        $this->checkLog();
        //
        $mbrNow = M("Member")->getById($this->userService->getUserId());
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

    // 我的收藏
    public function myCollection() {
        //
        $this->checkLog();
        //
        $mbrNow = M("Member")->getById($this->userService->getUserId());
        $this->assign("mbrNow", $mbrNow);
        //
        $condition['mid'] = $this->userService->getUserId();
        //
        $rid = $_REQUEST['rid'];
        if (!empty($rid)) {
            $condition['category'] = array('in', $this->_getSubCats($rid));
            $this->assign('rid', $rid);
        }
        //
        $listRows = 12;
        $pg = !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1;
        $rst = ($pg - 1) * $listRows;
        //
        $collectionView = new CollectionViewModel();
        $list = $collectionView->where($condition)->order('create_time desc')->limit($rst . ',' . $listRows)->group("link")->select();
        //echo $collectionView->getLastSql();
        $this->assign('collList', $list);
        // 分页
        $count = $collectionView->where($condition)->count('lnk_id');
        if ($count > 0) {
            import("@.ORG.Page");
            $p = new Page($count, $listRows);
            $page = $p->show_js2();
            $this->assign("page", $page);
        }
        //
        $this->getRootCats();
        //
        $this->assign("funcNow", "myCollection");
        //
        $this->display();
    }

    // 收藏
    public function saveCollect() {
        //
        if (!$this->checkLog(1)) {
            return false;
        }
        //
        $lnk_id = $_POST["lnk_id"];
        if (empty($lnk_id)) {
            echo "链接id丢失！";
            return false;
        }
        //
        $link = $_POST["link"];
        if (empty($link)) {
            echo "链接丢失！";
            return false;
        }
        //
        $collection = M("Collection");
        $data = array();
        $data['link'] = $link;
        $data['mid'] = $this->userService->getUserId();
        //
        if ($collection->where($data)->find()) {
            echo "已经收藏过了！";
            return false;
        }
        //
        $data['lnk_id'] = $lnk_id;
        $data['create_time'] = time();
        if (false !== $collection->add($data)) {
            $links = M("Links");
            if (false !== $links->where("link='" . $link . "'")->setInc('collect_num')) {
                Log::write('增加链接收藏数量失败：' . $links->getLastSql(), Log::SQL);
            }
            echo "saveOK";
        } else {
            Log::write('收藏失败：' . $collection->getLastSql(), Log::SQL);
            echo "收藏失败！";
        }
    }

    // 取消收藏
    public function del_collect() {
        //
        if (!$this->checkLog(1)) {
            return false;
        }
        //
        $lnk_id = $_POST["lnk_id"];
        if (empty($lnk_id)) {
            echo "链接id丢失！";
            return false;
        }
        //
        $collection = M("Collection");
        $condition = array();
        $condition['lnk_id'] = $lnk_id;
        $condition['mid'] = $this->userService->getUserId();
        //
        if (!$collection->where($condition)->find()) {
            echo "无此收藏！";
            return false;
        }
        //
        if (false !== $collection->where($condition)->delete()) {
            $links = M("Links");
            $linkNow = $links->getById($lnk_id);
            if (false !== $links->where("link='" . $linkNow['link'] . "'")->setDec('collect_num')) {
                Log::write('减少链接收藏数量失败：' . $links->getLastSql(), Log::SQL);
            }
            echo "delOK";
        } else {
            Log::write('取消收藏失败：' . $collection->getLastSql(), Log::SQL);
            echo "取消收藏失败！";
        }
    }

    // 我的推荐
    public function myRecommend() {
        //
        $this->checkLog();
        //
        $mbrNow = M("Member")->getById($this->userService->getUserId());
        $this->assign("mbrNow", $mbrNow);
        //
        $condition['mid'] = $this->userService->getUserId();
        //
        $rid = $_REQUEST['rid'];
        if (!empty($rid)) {
            $condition['category'] = array('in', $this->_getSubCats($rid));
            $this->assign('rid', $rid);
        }
        //
        $listRows = 12;
        $pg = !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1;
        $rst = ($pg - 1) * $listRows;
        //
        $links = M("Links");
        $list = $links->where($condition)->order('create_time desc')->limit($rst . ',' . $listRows)->select();
        //echo $links->getLastSql();
        $this->assign('recList', $list);
        // 分页
        $count = $links->where($condition)->count('id');
        if ($count > 0) {
            import("@.ORG.Page");
            $p = new Page($count, $listRows);
            $page = $p->show_js2();
            $this->assign("page", $page);
        }
        //
        $this->getRootCats();
        //
        $this->assign("funcNow", "myRecommend");
        //
        $this->display();
    }

    // 我的说说
    public function myComment() {
        //
        $this->checkLog();
        //
        $mbrNow = M("Member")->getById($this->userService->getUserId());
        $this->assign("mbrNow", $mbrNow);
        //
        $condition['mid'] = $this->userService->getUserId();
        //
        $rid = $_REQUEST['rid'];
        if (!empty($rid)) {
            $condition['category'] = array('in', $this->_getSubCats($rid));
            $this->assign('rid', $rid);
        }
        //
        $listRows = 12;
        $pg = !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1;
        $rst = ($pg - 1) * $listRows;
        //
        $commentView = new CommentViewModel();
        $mycmts = $commentView->where($condition)->order('create_time desc')->limit($rst . ',' . $listRows)->select();
        foreach ($mycmts as &$value) {
            $value["comment"] = checkLinkUrl($value["comment"]);
            $value['create_time'] = date('Y-m-d h:i', $value['create_time']);
        }
        $this->assign('mycmts', $mycmts);
        // 分页
        $count = $commentView->where($condition)->count('id');
        if ($count > 0) {
            import("@.ORG.Page");
            $p = new Page($count, $listRows);
            $page = $p->show_js2();
            $this->assign("page", $page);
        }
        //
        $this->getRootCats();
        //
        $this->assign("funcNow", "myComment");
        //
        $this->display();
    }

    // 编辑说说
    public function editComment() {
        $id = $_POST['id'];
        if (empty($id)) {
            echo '说说id丢失';
            return false;
        }
        //
        $comment = M("Comment");
        if (false === $comment->where('id=' . $id)->setField('comment', htmlspecialchars(trim($_POST['comment'])))) {
            Log::write('编辑说说失败：' . $comment->getLastSql(), Log::SQL);
            echo '编辑说说失败';
        } else {
            echo 'editOK';
        }
    }

    // 我的建议投诉
    public function mySuggestion() {
        //
        $this->checkLog();
        //$aryType = array('','留言板','申请取消链接','其他');
        //
    	$mbrNow = M("Member")->getById($this->userService->getUserId());
        $this->assign("mbrNow", $mbrNow);
        //
        $condition['pid'] = 0;
        $condition['mid'] = $this->userService->getUserId();
        //
        $listRows = 12;
        $pg = !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1;
        $rst = ($pg - 1) * $listRows;
        //
        $sugView = new SuggestionViewModel();
        $mysugs = $sugView->where($condition)->order('create_time desc')->limit($rst . ',' . $listRows)->select();
        foreach ($mysugs as &$value) {
            $value['create_time'] = date('Y-m-d h:i', $value['create_time']);
            //$value['typeName'] = $aryType[$value['type']];
            //$value["suggest"] = checkLinkUrl($value["suggest"]);
            //$value["reply"] = checkLinkUrl($value["reply"]);
            /*
              $value['subsug'] = $sugView->where('pid='.$value['id'])->order('create_time asc')->select();
              foreach ($value['subsug'] as &$val) {
              $val['create_time'] = date('Y-m-d h:i', $val['create_time']);
              $val['typeName'] = $aryType[$val['type']];
              $val["suggest"] = checkLinkUrl($val["suggest"]);
              $val["reply"] = checkLinkUrl($val["reply"]);
              }
             */
        }
        $this->assign('mysugs', $mysugs);
        // 分页
        $count = $sugView->where($condition)->count('id');
        if ($count > 0) {
            import("@.ORG.Page");
            $p = new Page($count, $listRows);
            $page = $p->show_js2();
            $this->assign("page", $page);
        }
        //
        $this->assign("funcNow", "mySuggestion");
        //
        $this->display();
    }

    //
    public function saveSuggestion() {
        //
        if (!$this->checkLog(1)) {
            return false;
        }
        //
        $suggestion = M("Suggestion");
        //
        if (empty($_POST['id'])) {
            $_POST['mid'] = $this->userService->getUserId();
            $_POST['type'] = 1;
            $_POST['create_time'] = time();
            //
            if (false === $suggestion->add($_POST)) {
                Log::write('新增留言失败：' . $suggestion->getLastSql(), Log::SQL);
                echo "新增留言失败！";
            } else {
                echo "saveOK";
            }
        } else {
            if (false === $suggestion->save($_POST)) {
                Log::write('编辑留言失败：' . $suggestion->getLastSql(), Log::SQL);
                echo "编辑留言失败！";
            } else {
                echo "saveOK";
            }
        }
    }

    /**
      +----------------------------------------------------------
     * 功能函数
      +----------------------------------------------------------
     */
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

    // 获取zTree菜单
    public function getZtCats() {
        //
        import("@.ORG.String");
        //
        $category = M("Category");
        $data = "[";
        //
        $cats = $category->field('id,cat_name')->where('level=1')->order('sort')->select();
        $cntCats = count($cats);
        $i = 1;
        foreach ($cats as &$value) {
            $data .= "{name:'" . $value['cat_name'] . "', children:[";
            $submenu = $category->field('id,cat_name')->where('prt_id=' . $value['id'])->order('sort')->select();
            $cntsm = count($submenu);
            $j = 1;
            foreach ($submenu as &$val) {
                $data .= "{name:'" . String::msubstr($val['cat_name'], 0, 14) . "', cid:'" . $val['id'] . "', iconSkin:'pIcon01'}";
                if ($j++ < $cntsm) {
                    $data .= ",";
                }
            }
            $data .= "]}";
            if ($i++ < $cntCats) {
                $data .= ",";
            }
        }
        $data .= "]";
        //
        echo $data;
    }

    // 回复留言
    public function saveReply() {
        //
        $this->checkLog();
        //
        $id = $_POST["id"];
        if (empty($id)) {
            echo "留言id丢失！";
            return false;
        }
        //
        $msg = M("Message");
        $data = array();
        $data['reply'] = htmlspecialchars($_POST['reply']);
        $data['reply_time'] = time();
        if (false !== $msg->where('id=' . $id)->save($data)) {
            echo "submitOK";
        } else {
            echo "回复留言失败！";
        }
    }

    //验证码
    public function verify() {
        $type = isset($_GET['type']) ? $_GET['type'] : 'gif';
        import("@.ORG.Image");
        Image::buildImageVerify(4, 1, $type);
    }

    // 用户登录
    public function checklogin() {
        //
		$result = $this->userService->login($_POST['username'],$_POST['password'],$_POST['auto_login']);
		switch($result){
			case 200:
				echo "loginOK";
				return true;
			case 202:
				echo '用户名有不法字符';
				break;
			case 203:
				echo '无此用户！';
				break;
			case 204:
				echo '已禁用！';
				break;
			case 205:
				echo '密码错误';
				break;
			case 206:
				echo '登录失败';
				break;
		}
		return false;
    }

    // 登出
    public function logout() {
        //
		$this->userService->logout();
        //
        header("Location: " . $_SERVER["HTTP_REFERER"]); //退出后刷新页面
//        header("Location: " . __APP__ . "/");
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
		$status = $this->userService->changeEmail($email);
		switch($status){
			case 209:
				echo '邮箱格式有误';
				return false;
			case 213:
				echo '该email已被使用，请换一个！';
				return false;
			case 212:
				echo '保存email失败！';
				return false;
			case 200:
				echo 'saveOK';
				return true;
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
		$status = $this->userService->changeNickname($nickname);echo $status;
		switch($status){
			case 207:
				echo '昵称只能包含2-20个字符、数字、下划线和汉字';
				return false;
			case 210:
				echo '该昵称已被使用，请换一个！';
				return false;
			case 212:
				echo '保存昵称失败！';
				return false;
			case 200 :
				echo 'saveOK';
				return true;
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
		$status = $this->userService->changePassword($password);
		switch($status){
			case 208:
				echo '密码应为6到20位数字或字母';
				return false;
			case 212:
				echo '保存密码失败！';
				return false;
			case 200:
				echo 'saveOK';
				return false;
		}
    }

    // 设定头像
    public function saveFace() {
        //
        if (!$this->checkLog(1)) {
            return false;
        }
        //
        $face = $_POST['face'];
        if (empty($face)) {
            echo "头像丢失！";
            return false;
        }
        //
        $member = M("Member");
        if (false === $member->where('id=' . $this->userService->getUserId())->setField('face', $face)) {
            Log::write('设定头像失败：' . $member->getLastSql(), Log::SQL);
            echo "设定头像失败！";
        } else {
            $_SESSION['face'] = $face;
            echo "saveOK";
        }
    }
}

?>