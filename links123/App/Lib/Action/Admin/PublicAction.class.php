<?php
class PublicAction extends BaseAction {
    public function _initialize() {
        parent::_initialize();
    }
    // 检查用户是否登录
    protected function checkUser() {
        if(!isset($_SESSION[C('USER_AUTH_KEY')])) {
            $this->error('没有登录','Public/login');
        }
    }

    // 顶部页面
    public function top() {
    	//
        C('SHOW_RUN_TIME',false);			// 运行时间显示
        C('SHOW_PAGE_TRACE',false);
        $model	=	M("Group");
        $list	=	$model->where('status=1')->getField('id,title');
        $this->assign('nodeGroupList',$list);
        //
        $this->menu();
        //
        $this->display();
        return;
    }

    public function drag(){
        C('SHOW_PAGE_TRACE',false);
        C('SHOW_RUN_TIME',false);			// 运行时间显示
        $this->display();
        return;
    }

    // 尾部页面
    public function footer() {
        C('SHOW_RUN_TIME',false);			// 运行时间显示
        C('SHOW_PAGE_TRACE',false);
        $this->display();
        return;
    }

    // 菜单页面
    public function menu() {
        $this->checkUser();
        if(isset($_SESSION[C('USER_AUTH_KEY')])) {
            //显示菜单项
            $menu  = array();
            //if(isset($_SESSION['menu'.$_SESSION[C('USER_AUTH_KEY')]])) {
                //如果已经缓存，直接读取缓存
                //$menu = $_SESSION['menu'.$_SESSION[C('USER_AUTH_KEY')]];
            //}else {
                if(isset($_SESSION['_ACCESS_LIST'])) {
                    $accessList = $_SESSION['_ACCESS_LIST'];
                }else{
                    import('@.ORG.RBAC');
                    $accessList = RBAC::getAccessList($_SESSION[C('USER_AUTH_KEY')]);
                }
                //读取分组
                $groupModel = D("Group");
                $group = $groupModel->where("`status`=1 and `show`=1")->select();
                //读取数据库模块列表生成菜单项
                $node    =   M("Node");
                foreach($group as $k=>$value){
                    $where['level']=2;
                    $where['status'] = 1;
                    $where['group_id'] = $value['id'];
                    $group[$k]['menu'] = $node->where($where)->field('id,name,group_id,title')->order('sort asc')->select();
                    foreach ($group[$k]['menu'] as $key => $module) {
                        if (isset($accessList[strtoupper(GROUP_NAME)][strtoupper($module['name'])]) || isset($_SESSION[C('ADMIN_AUTH_KEY')])) {
                            //设置模块访问权限
//                            $module['access'] = 1;
                            $group[$k]['menu'][$key]['access']=1;
//                            $menu[$key] = $module;
                        }
                    }
                }
                
//                $id	=	$node->getField("id");
                //缓存菜单访问
                $_SESSION['menu'.$_SESSION[C('USER_AUTH_KEY')]]	= $group;
            //}
            if(!empty($_GET['tag'])){
                $this->assign('menuTag',$_GET['tag']);
            }
            else {
            	$this->assign('menuTag', 1);
            }
            $this->assign('menu',$group);
        }
        //
        C('SHOW_RUN_TIME',false);			// 运行时间显示
        C('SHOW_PAGE_TRACE',false);
        //
        $this->display();
        return;
    }

    // 后台首页 查看系统信息
    public function main() {
        $info = array(
            '操作系统'=>PHP_OS,
            '运行环境'=>$_SERVER["SERVER_SOFTWARE"],
            'PHP运行方式'=>php_sapi_name(),
            'ThinkPHP版本'=>THINK_VERSION,
            '上传附件限制'=>ini_get('upload_max_filesize'),
            '执行时间限制'=>ini_get('max_execution_time').'秒',
            '服务器时间'=>date("Y年n月j日 H:i:s"),
            '北京时间'=>gmdate("Y年n月j日 H:i:s",time()+8*3600),
            '服务器域名/IP'=>$_SERVER['SERVER_NAME'].' [ '.gethostbyname($_SERVER['SERVER_NAME']).' ]',
            '剩余空间'=>round((@disk_free_space(".")/(1024*1024)),2).'M',
            'register_globals'=>get_cfg_var("register_globals")=="1" ? "ON" : "OFF",
            'magic_quotes_gpc'=>(1===get_magic_quotes_gpc())?'YES':'NO',
            'magic_quotes_runtime'=>(1===get_magic_quotes_runtime())?'YES':'NO',
            );
        $this->assign('info',$info);
        $this->display();
        return;
    }

    // 用户登录页面
    public function login() {
    	//
        if(!isset($_SESSION[C('USER_AUTH_KEY')])) {
            $this->display();
            return;
        }else{
            $this->redirect('Index/index');
        }
    }

    public function index() {
        //如果通过认证跳转到首页
        redirect(__GROUP__);
    }

    // 用户登出
    public function logout() {
        if(isset($_SESSION[C('USER_AUTH_KEY')])) {
            unset($_SESSION[C('ADMIN_AUTH_KEY')]);
            unset($_SESSION['menu'.$_SESSION[C('USER_AUTH_KEY')]]);
            unset($_SESSION[C('USER_AUTH_KEY')]);
            unset($_SESSION['_ACCESS_LIST']);
            cookie(md5("manament_login_time"),null);
            //session_unset();
            //session_destroy();
            $this->assign("jumpUrl",__URL__.'/login/');
            $this->success('登出成功！');
        }
        else {
            $this->error('已经登出！');
        }
    }

    // 登录检测
    public function checkLogin() {
        if(empty($_POST['account'])) {
            $this->error('帐号错误！');
        }elseif (empty($_POST['password'])){
            $this->error('密码必须！');
        }elseif (empty($_POST['verify'])){
            $this->error('验证码必须！');
        }
        //生成认证条件
        $map            =   array();
        // 支持使用绑定帐号登录
        $map['account']	= $_POST['account'];
        $map["status"]	=	array('gt',0);
        if(session('verify') != md5($_POST['verify'])) {
            $this->error('验证码错误！');
        }
        import ( '@.ORG.RBAC' );
        $authInfo = RBAC::authenticate($map);
        //使用用户名、密码和状态的方式进行认证
        if(false === $authInfo) {
            $this->error('帐号不存在或已禁用！');
        }else {
            if($authInfo['password'] != md5($_POST['password'])) {
                $this->error('密码错误！');
            }
            $_SESSION[C('USER_AUTH_KEY')]	=	$authInfo['id'];
            $_SESSION['email']				=	$authInfo['email'];
            $_SESSION['loginUserName']		=	$authInfo['nickname'];
            $_SESSION['lastLoginTime']		=	$authInfo['last_login_time'];
            $_SESSION['login_count']		=	$authInfo['login_count'];
            if($authInfo['account']=='admin') {
                $_SESSION[C('ADMIN_AUTH_KEY')] = true;
            }
            //使用cookie过期时间来控制后台登陆的过期时间
            $admin_session_expire = D("Variable")->getVariable("admin_session_expire");
            cookie(md5("manament_login_time") , time() , $admin_session_expire);
            //保存登录信息
            $User	=	M('User');
            $ip		=	get_client_ip();
            $time	=	time();
            $data = array();
            $data['id']	=	$authInfo['id'];
            $data['last_login_time']	=	$time;
            $data['login_count']	=	array('exp','login_count+1');
            $data['last_login_ip']	=	$ip;
            $User->save($data);

            // 缓存访问权限
            RBAC::saveAccessList();
            $this->success('登录成功！',__GROUP__.'/Index/index');

        }
    }
    
    // 更换密码
    public function changePwd() {
        $this->checkUser();
        //对表单提交处理进行处理或者增加非表单数据
        if(md5($_POST['verify'])	!= $_SESSION['verify']) {
            $this->error('验证码错误！');
        }
        $map	=	array();
        $map['password']= pwdHash($_POST['oldpassword']);
        if(isset($_POST['account'])) {
            $map['account']	 =	 $_POST['account'];
        }elseif(isset($_SESSION[C('USER_AUTH_KEY')])) {
            $map['id']		=	$_SESSION[C('USER_AUTH_KEY')];
        }
        //检查用户
        $User    =   M("User");
        if(!$User->where($map)->field('id')->find()) {
            $this->error('旧密码不符或者用户名错误！');
        }else {
            $User->password	=	pwdHash($_POST['password']);
            $User->save();
            $this->success('密码修改成功！');
         }
    }

    //显示个人资料
    public function profile() {
    	$this->checkUser();
    	$User = M("User");
    	$vo	= $User->getById($_SESSION[C('USER_AUTH_KEY')]);
    	$this->assign('vo',$vo);
    	$this->display();
    	return;
    }

    //验证码
    public function verify() {
    	$type = isset($_GET['type'])?$_GET['type']:'gif';
    	import("@.ORG.Image");
    	Image::buildImageVerify(3,1,$type,36);
    }

    // 修改资料
    public function change() {
        $this->checkUser();
        $User = D("User");
        if(!$User->create()) {
            $this->error($User->getError());
        }
        $result	=	$User->save();
        if(false !== $result) {
            $this->success('资料修改成功！');
        }
        else {
            $this->error('资料修改失败!');
        }
    }
    
    // 上传图片
    public function uploadPic() {
    	import("@.ORG.UploadFile");
    	$upload = new UploadFile();
    	//设置上传文件大小
    	$upload->maxSize = 3292200;
    	//设置上传文件类型
    	$upload->allowExts = explode(',', 'jpg,gif,png,jpeg');
    	//设置附件上传目录
    	$path = realpath('./Public/Uploads/uploads.txt');
    	$upload->savePath = str_replace('uploads.txt', $_REQUEST["folder"], $path).'/';
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
    		//import("@.ORG.Image");
    		//$filename = $upload->savePath.$uploadList[0]['savename'];
    		//Image::thumb_db($filename,$filename,'',0,0,$_REQUEST["width"],10000,true);
    		//
    		$idNow = $_REQUEST["id"];
    		if ( empty($idNow) ) {
    			$idNow = "pic";
    		}
    		echo $idNow.'|'.$uploadList[0]['savename'];
    	}
    }
    
    // 上传附件
    public function uploadAtt() {
    	import("@.ORG.UploadFile");
    	$upload = new UploadFile();
    	//设置上传文件大小
    	//$upload->maxSize = 3292200;
    	//设置上传文件类型
    	$upload->allowExts = explode(',', 'pdf,doc,docx,zip,rar,xlsx');
    	//设置附件上传目录
    	$path = realpath('./Public/Uploads/uploads.txt');
    	$upload->savePath = str_replace('uploads.txt', $_REQUEST["folder"], $path).'/';
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
    		$idNow = $_REQUEST["id"];
    		if ( empty($idNow) ) {
    			$idNow = "att";
    		}
    		$size = $uploadList[0]['size']/1024;
    		if ($size > 1000) {
    			$size = (round($size/1024*10)/10)."M";
    		}
    		else {
    			$size = (round($size*10)/10)."K";
    		}
    		echo $idNow.'|'.$uploadList[0]['savename'].'|'.$size;
    	}
    }
    
    // 备份
    protected function data2sql($table) {
    	$model = new Model();
    	$tabledump =
    	"\n-- --------------------------------------------------------\n\n".
    	"--\n".
    	"-- 表的结构 `".$table."`\n".
    	"--\n\n";
    	$tabledump .= "DROP TABLE IF EXISTS $table;\n";
    	$createtable = $model->query("SHOW CREATE TABLE $table");
    	$tabledump .= $createtable[0]['Create Table'].";\n\n";
    
    	$tabledump .=
    	"--\n".
    	"-- 转存表中的数据 `".$table."`\n".
    	"--\n\n";
    
    	$rows = $model->query("SELECT * FROM $table");
    	foreach($rows as &$row) {
    		$comma = "";
    		$tabledump .= "INSERT INTO $table VALUES(";
    		foreach ($row as &$value) {
    			$tabledump .= $comma."'".mysql_escape_string($value)."'";
    			$comma = ",";
    		}
    		$tabledump .= ");\n";
    	}
    	$tabledump .= "\n";
    	return $tabledump;
    }
    
    // 自动备份
    public function autobackup() {
    	//
    	@header('Content-type:text/html;charset=UTF-8');
    	//
    	$model = new Model();
    	// 定义要保存的数据表
    	$tables = $model->query("SHOW TABLES"); //定义要保存的数据表，一个数组
    
    	// 定义数据保存的文件名
    	$filename = "db/".C("DB_PREFIX")."_backup_".date('Y-m-d').".sql";
    
    	// 获取数据库结构和数据内容
    	foreach($tables as $table) {
    		foreach($table as &$val) {
    			$sqldump .= $this->data2sql($val);
    		}
    	}
    
    	// 如果数据内容不是空就开始保存
    	if( trim($sqldump) ) {
    		// 写入开头信息
    		$sqldump =
    		"--\n".
    		"-- 数据库: `".C("DB_NAME")."`\n".
    		"-- 备份时间: ".date('Y年m月d日 H:i')."\n".
    		"--\n\n\n".
    		$sqldump;
    			
    		// 保存到服务器
    		if($filename != "") {
    			@$fp = fopen($filename, "w+");
    			if ($fp) {
    				@flock($fp, 3);
    				if(@!fwrite($fp, $sqldump)) {
    					@fclose($fp);
    					echo "数据文件无法保存到服务器，请检查目录属性你是否有写的权限。";
    				}
    				else {
    					echo "数据成功备份至服务器 <a href='".__ROOT__."/".$filename."'>".$filename."</a> 中。";
    				}
    			}
    			else {
    				echo "无法打开你指定的目录". $filename ."，请确定该目录是否存在，或者是否有相应权限";
    			}
    		}
    		else {
    			echo "您没有输入备份文件名，请返回修改。";
    		}
    	}
    	else {
    		echo "数据表没有任何内容";
    	}
    }
    
    // 清空缓存
    public function clearCache($path=null) {
    	//先删除目录下的文件：
    	if ( empty($path) ) {
    		$path = realpath('./App/Runtime/runtime.txt');
    		$path = str_replace('runtime.txt', '', $path);
    	}
    	$dh = opendir($path);
    	while ($file = readdir($dh)) {
    		if( $file!="." && $file!=".." ) {
    			$fullpath=$path."/".$file;
    			if( !is_dir($fullpath) ) {
    				if ( !strpos($fullpath, 'runtime.txt') ) {
    					if ( unlink($fullpath) ) {
    						echo "<span style='color:blue'>file</span> ".$fullpath." <span style='color:green'>clear OK!</span><br />";
    					}
    					else {
    						echo "<span style='color:blue'>file</span> ".$path." <span style='color:red'>clear faild!</span><br />";
    					}
    				}
    			}
    			else {
    				$this->clearCache($fullpath);
    			}
    		}
    	}
    
    	closedir($dh);
    	//删除当前文件夹：
    	if(rmdir($path)) {
    		echo "<span style='color:blue'>dir</span>&nbsp;&nbsp;".$path." <span style='color:green'>clear OK!</span><br />";
    	} else {
    		echo "<span style='color:blue'>dir</span>&nbsp;&nbsp;".$path." <span style='color:red'>clear faild!</span><br />";
    	}
    }
    
    // 读取excel
    public function read_excel() {
    	//
    	$pattern = '/[^\x00-\x80]/';
    	//
    	import("@.ORG.Pinyin");
    	$pinyin = new Pinyin();
    	//
    	error_reporting(E_ALL);
    	date_default_timezone_set('Asia/Shanghai');
    	//
    	vendor('PHPExcel.Classes.PHPExcel.IOFactory');
    	@header('Content-type: text/html;charset=UTF-8');
    	//
    	//echo date('H:i:s') . " Load from Excel5 template<br />";
    	$objReader = PHPExcel_IOFactory::createReader('Excel2007');
    	$path = realpath('./Public/Uploads/uploads.txt');
    	$dest = str_replace('uploads.txt', 'Excels/'.$_REQUEST['file'].'.xlsx', $path);
    	//$dest = str_replace('uploads.txt', 'Excels/'.$_REQUEST['file'], $path);
    	$objPHPExcel = $objReader->load($dest);
    	//
    	$model = M("DirectLinks");
    	//
    	//echo date('H:i:s') . " Iterate worksheets<br />";
    	foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
    		//echo '- ' . $worksheet->getTitle() . "<br />";
    		foreach ($worksheet->getRowIterator() as $row) {
    			//echo '    - Row number: ' . $row->getRowIndex() . "<br />";
    			$url = '';
    			$tag = '';
    			$cellIterator = $row->getCellIterator();
    			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
    			foreach ($cellIterator as $cell) {
    				if (!is_null($cell)) {
    					//echo '        - Cell: ' . $cell->getCoordinate() . ' - ' . $cell->getCalculatedValue() . "<br />";
    					if ($cell->getColumn()=="A") {
    						$url = $cell->getCalculatedValue();
    					}
    					else if ($cell->getColumn()=="B") {
    						$tag = $cell->getCalculatedValue();
    					}
    				}
    			}
    			if (!empty($url) && !empty($tag)) {
    				$url = str_replace("。", ".", trim($url));
    				$ary_tag = array();
    				$tempary = explode('.', $url);	// 自动获取域名标签
    				if ( $tempary[0] == "www" ) {
    					$ary_tag[0] = $tempary[1];
    				}
    				else {
    					$ary_tag[0] = $tempary[0];
    				}
    				$tempary = explode(",", str_replace("，", ",", trim($tag)));
    				foreach ($tempary as &$value) {
    					$temp = explode('网', $value);	// 自动去除tag末尾的“网”字
    					$sizeNow = count($temp);
    					if ( $sizeNow > 1 && $temp[$sizeNow-1]=='' ) {
    						$value = $temp[0];
    						if ( $value == '' ) {
    							$value = '网';
    						}
    						for ($i=1; $i!=$sizeNow-1; ++$i) {
    							$value .= '网'.$temp[$i];
    						}
    					}
    					array_push($ary_tag, $value);
    					// 自动生成拼音标签
    					if(preg_match($pattern,$value)){
    						array_push($ary_tag, $pinyin->toPinyin($value));
    					}
    				}
    				//
    				$data['url'] = $url;
    				$data['update_time'] = time();
    				$data['status'] = 1;	//默认从excel中导入数据为已审核，未查看状态
    				foreach ($ary_tag as &$value) {
    					$data['tag'] = $value;
    					if ( preg_match($pattern, $value) ) {
    						$data['cn_tag'] = 1;
    					}
    					else {
    						$data['cn_tag'] = 0;
    					}
    					if ( false === $model->add($data) ) {
    						echo "tag:<span style='color:red'>".$data['tag'].'</span> -- '."url:".$data['url'].' input failed!'."<br />";
    						echo $model->getLastSql()."<br />";
    					}
    				}
    			}
    		}
    	}
    	//
    	echo "excel文件导入已完成";
    }
    
    // 直达标签分类
    /*
    public function apartTag(){
    	$pattern = '/[^\x00-\x80]/';
    	//
    	$model = M("DirectLinks");
    	$list = $model->where('id>=400000')->order('id')->limit(100000)->select();
    	$cnt = 0;
    	foreach ($list as &$value) {
			if ( preg_match($pattern, $value['tag']) ) { 
				$model->where('id='.$value['id'])->setField('cn_tag', 1);
				$cnt++;
			}
    	}
    	//
    	@header('Content-type: text/html;charset=UTF-8');
    	echo "共有中文标签：".$cnt."个";
    }
    */
}