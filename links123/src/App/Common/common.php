<?php

// +----------------------------------------------------------------------
// | ThinkPHP
// +----------------------------------------------------------------------
// | Copyright (c) 2007 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id: common.php 2601 2012-01-15 04:59:14Z liu21st $
// 自动转换字符集 支持数组转换
function auto_charset($fContents, $from = 'gbk', $to = 'utf-8') {
    $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
    $to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
    if (strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents))) {
        //如果编码相同或者非字符串标量则不转换
        return $fContents;
    }
    if (is_string($fContents)) {
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($fContents, $to, $from);
        } elseif (function_exists('iconv')) {
            return iconv($from, $to, $fContents);
        } else {
            return $fContents;
        }
    } elseif (is_array($fContents)) {
        foreach ($fContents as $key => $val) {
            $_key = auto_charset($key, $from, $to);
            $fContents[$_key] = auto_charset($val, $from, $to);
            if ($key != $_key)
                unset($fContents[$key]);
        }
        return $fContents;
    }
    else {
        return $fContents;
    }
}

//公共函数
function toDate($time, $format = 'Y-m-d H:i:s') {
    if (empty($time)) {
        return '';
    }
    $format = str_replace('#', ':', $format);
    return date($format, $time);
}

//
function toDateShort($time, $format = 'Y-m-d') {
    if (empty($time)) {
        return '';
    }
    $format = str_replace('#', ':', $format);
    return date($format, $time);
}

// 设定长度
function lengthLimit($str, $lng = 50) {
    import("@.ORG.String");
    return String::msubstr(cleanHtml($str), 0, $lng);
}

//
function getStatus($status, $imageShow = true) {
    switch ($status) {
        case 0 :
            $showText = '禁用';
            $showImg = '<IMG SRC="__PUBLIC__/Images/locked.gif" WIDTH="20" HEIGHT="20" BORDER="0" ALT="禁用">';
            break;
        case 2 :
            $showText = '待审';
            $showImg = '<IMG SRC="__PUBLIC__/Images/prected.gif" WIDTH="20" HEIGHT="20" BORDER="0" ALT="待审">';
            break;
        case - 1 :
            $showText = '删除';
            $showImg = '<IMG SRC="__PUBLIC__/Images/del.gif" WIDTH="20" HEIGHT="20" BORDER="0" ALT="删除">';
            break;
        case 1 :
        default :
            $showText = '正常';
            $showImg = '<IMG SRC="__PUBLIC__/Images/ok.gif" WIDTH="20" HEIGHT="20" BORDER="0" ALT="正常">';
    }
    return ($imageShow === true) ? $showImg : $showText;
}

function getDefault($default) {
    switch ($default) {
        case 1:
            return "<font style='color:red'>默认</font>";
            break;

        default:
            return "<font style='color:#000'>设为默认</font>";
            break;
    }
}

//
function getNodeGroupName($id) {
    if (empty($id)) {
        return '未分组';
    }
    if (isset($_SESSION ['nodeGroupList'])) {
        return $_SESSION ['nodeGroupList'] [$id];
    }
    $Group = D("Group");
    $list = $Group->getField('id,title');
    $_SESSION ['nodeGroupList'] = $list;
    $name = $list [$id];
    return $name;
}

//
function showStatus($status, $id) {
    switch ($status) {
        case 0 :
            $info = '<a href="javascript:resume(' . $id . ')">恢复</a>';
            break;
        case 2 :
            $info = '<a href="javascript:pass(' . $id . ')">批准</a>';
            break;
        case 1 :
            $info = '<a href="javascript:forbid(' . $id . ')">禁用</a>';
            break;
        case - 1 :
            $info = '<a href="javascript:recycle(' . $id . ')">还原</a>';
            break;
    }
    return $info;
}

//
function getGroupName($id) {
    if ($id == 0) {
        return '无上级组';
    }
    $list = F('groupName');
    if ($list) {
        return $list [$id];
    }
    $dao = D("Role");
    $list = $dao->select(array('field' => 'id,name'));
    foreach ($list as $vo) {
        $nameList [$vo ['id']] = $vo ['name'];
    }
    $name = $nameList [$id];
    F('groupName', $nameList);
    return $name;
}

function getVoiceName($voice) {
    switch ($voice) {
        case 1:
            return "美音";

            break;
        case 2:
            return "英音";
        default:
            return "未知";
            break;
    }
}

function getTargetName($target) {
    switch ($target) {
        case 1:
            return "听力";

            break;
        case 2:
            return "说力";
        default:
            return "未知";
            break;
    }
}

function getPatternName($pattern) {
    switch ($pattern) {
        case 1:
            return "视频";

            break;
        case 2:
            return "音频";
        default:
            return "未知";
            break;
    }
}

//
function pwdHash($password, $type = 'md5') {
    return hash($type, $password);
}

/* * ************************* */

//
function setLevel($val) {
    $show = '';
    for ($i = 1; $i != $val; ++$i) {
        $show .= "　　";
    }
    return $show;
}

//
function getYorN($val) {
    switch ($val) {
        case 0:
            $showText = '否';
            break;
        case 1:
            $showText = '是';
            break;
    }
    return $showText;
}

//
function getLang($val) {
    switch ($val) {
        case 1 :
            $show = '中文';
            break;
        case 2 :
            $show = '英文';
            break;
    }
    return $show;
}

//
function getLinkToHref($link, $checked) {
    return "<a target='_blank' href='http://" . $link . "'>" . $link . "</a>";
}

// for directlinks
function getLinkToHref2($link, $checked) {
    return "<a target='_blank' href='http://" . $link . "' onclick=\"setChecked('" . $link . "'," . $checked . ")\">" . $link . "</a>";
}

//
function getLinkStt($val) {
    switch ($val) {
        case 0 :
            $show = '未审';
            break;
        case 1 :
            $show = '已审';
            break;
        case -1 :
            $show = '已删';
            break;
    }
    return $show;
}

//
function getCatStt($val) {
    switch ($val) {
        case 1 :
            $show = '有效';
            break;
        case -1 :
            $show = '已删';
            break;
    }
    return $show;
}

//
function getSugStt($val) {
    switch ($val) {
        case 1 :
            $show = '已回复';
            break;
        case 0 :
            $show = '未回复';
            break;
        case -1 :
            $show = '已删';
            break;
    }
    return $show;
}

//
function getSugType($val) {
    switch ($val) {
        case 1 :
            $show = '建议投诉';
            break;
        case 2 :
            $show = '申请取消链接';
            break;
        case 3 :
            $show = '其他';
            break;
    }
    return $show;
}

//
function getNeedkey($val) {
    switch ($val) {
        case 0 :
            $show = '直达';
            break;
        case 1 :
            $show = '需输入';
            break;
    }
    return $show;
}

/* 二维数组按指定的键值排序
 *  $arr  数组
 *  $key  排序键值
 *  $type 排序方式
 */

function array_sort($arr, $keys, $type = 'desc') {
    $keysvalue = $new_array = array();
    foreach ($arr as $k => $v) {
        $keysvalue[$k] = $v[$keys];
    }
    if ($type == 'asc') {
        asort($keysvalue);
    } else {
        arsort($keysvalue);
    }
    reset($keysvalue);
    foreach ($keysvalue as $k => $v) {
        $new_array[$k] = $arr[$k];
    }
    return $new_array;
}

/**
 * @name cleanParam
 * @desc 验证参数
 * @param string $param
 * @return string
 * @author Frank UPDATE 2013-08-19
 */
function cleanParam($param) {
    $param = trim($param);
    $ary_change = array(
        'AUX' => '',
        'CLOCK$' => '',
        'COM1' => '',
        'COM2' => '',
        'COM3' => '',
        'COM4' => '',
        'COM5' => '',
        'COM6' => '',
        'COM7' => '',
        'COM8' => '',
        'CON' => '',
        'CONFIG$' => '',
        'LPT1' => '',
        'LPT2' => '',
        'LPT3' => '',
        'LPT4' => '',
        'LPT5' => '',
        'LPT6' => '',
        'LPT7' => '',
        'LPT8' => '',
        'NUL' => '',
        'PRN' => '',
        ';' => '',
        "'" => '',
        '--' => '',
        '/*' => '',
        '*/' => '',
        'xp_' => '',
    );
    
    $param = strtr($param, $ary_change);
    $param = htmlspecialchars($param);
    $param1 = strtolower($param);
    $param = strpos($param1, 'script') > 0 || strpos($param1, 'iframe') > 0 ? '' : $param;
    return $param;
}

/* 去除html标签 */

function cleanHtml($str) {
    $arrStr = explode('<', $str);
    foreach ($arrStr as &$item) {
        $arrTemp = explode('>', $item);
        if (count($arrTemp) == 2) {
            $item = $arrTemp[1];
        }
    }
    return implode('', $arrStr);
}

/**
 * @desc 自动给所有的链接添加地址标签
 * @param unknown_type $cnt
 * @return mixed
 */
function checkLinkUrl($cnt) {
    $cnt = str_replace('<img src="http://', "|image|", $cnt);
    $cnt = clearLink($cnt);
    $ary = explode('<br />', $cnt);
    $rsl = '';
    foreach ($ary as $key => $value) {
        $temp = explode('http://', $value);
        if (count($temp) > 1) {
            foreach ($temp as $item => $val) {
                if ($item == 0) {
                    if (empty($rsl)) {
                        $rsl = $val;
                    } else {
                        $rsl .= "<br />" . $val;
                    }
                } else {
                    $temp2 = explode(' ', $val);
                    if (count($temp2) > 1) {
                        $rsl .= "<a href='http://" . $temp2[0] . "' target='_blank'>http://" . $temp2[0] . "</a> " . $temp2[1];
                    } else {
                        $rsl .= "<a href='http://" . $val . "' target='_blank'>http://" . $val . "</a> ";
                    }
                }
            }
        } else {
            if (empty($rsl)) {
                $rsl = $value;
            } else {
                $rsl .= "<br />" . $value;
            }
        }
    }
    //
    $rsl = str_replace('|image|', '<img src="http://', $rsl);
    return $rsl;
}

/**
 * @desc 去连接
 * @param unknown_type $cnt
 * @return string|unknown
 */
function clearLink($cnt) {
    $temp = explode('<a ', $cnt);
    if (count($temp) > 1) {
        $rsl = '';
        foreach ($temp as $item => $val) {
            if ($item == 0) {
                if (empty($rsl)) {
                    $rsl = $val;
                } else {
                    $rsl .= $val;
                }
            } else {
                $temp2 = explode("</a>", $val);
                $temp3 = explode(">", $temp2[0]);
                $rsl .= $temp3[1] . $temp2[1];
            }
        }
        return $rsl;
    } else {
        return $cnt;
    }
}

/**
 * @name sendMail
 * @desc 发邮件
 * @param string mail
 * @author Frank UPDATE 2013-08-25
 */
function sendMail($mail) {
	
    $variable = M("Variable");
    $send_from = $variable->where("vname = 'send_from'")->getField('value_varchar');
    $arrSendFrom = explode(',', $send_from);
    
    import('@.ORG.Email');
    $space = "\r\n";
    $emailer = new Email();
    $emailer->setConfig('smtp_host', $arrSendFrom[0]);
    $emailer->setConfig('smtp_user', $arrSendFrom[1]);
    $emailer->setConfig('smtp_pass', $arrSendFrom[2]);
    $emailer->setConfig('from', $arrSendFrom[1]);
    $emailer->setConfig('charset', 'UTF-8');
    
    $fname = "=?UTF-8?B?" . base64_encode('另客网') . "?=";
    $emailer->setConfig('fromName', $fname);
    $emailer->sendTo = $mail['mailto'];
    $mail['title'] = "=?UTF-8?B?" . base64_encode($mail['title']) . "?=";
    $emailer->subject = $mail['title'];
    
    $emailer->content = $mail['content'];

    return $emailer->send();
}

function getUserNickName($id) {
    $id = intval($id);
    if ($id == 0) {
        return "游客";
    }
    return D("Member")->where("id={$id}")->getField("nickname");
}

/**
 * 去除字符串前后一个或多个空格
 * @param string $str
 * @return string
 * @throws
 * @author Adam $date2013.6.18$
 */
function ftrim($str) {
    $str = trim($str);
    $str = str_replace("　", " ", $str); //将全角空格转换为半角空格
    $str = preg_replace("/&nbsp;+/", ' ', $str);//去掉&nbsp;
    $str = preg_replace("/(^\s+)|(\s+$)/", '', $str); //将开头或结尾的一个或多个半角空格转换为空
    return $str;
}


/**
 * 违法的关健字过滤
 * @param  string $str
 * @return string
 * @throws
 * @author yjj $date2013.7.13$
 */
function filterIllegal($str) {
	if (empty($str)){
		return false ;
	}
    $str = iconv("utf-8", "gbk", trim($str));
    //得到违法关健字数组
    $keywordArr = D("IllegalKeywords")->getIllegalKeywordsArr();
    if (empty($keywordArr)){
    	return false;
    }
    //遍历数组,替换到违法关健字
    foreach ($keywordArr as $k=>$v) {
		$keywordName = iconv("utf-8", "gbk", trim($v['keyword_name'])) ;
		if (preg_match("#$keywordName#i", $str)){
			for ($i=0,$repStr = "";$i<mb_strlen($keywordName)/2;$i++){
				$repStr.="*";
			}
			$str =str_replace($keywordName,$repStr, $str);
		}

    }
    $str = iconv("gbk", "utf-8", $str);
    return $str;
}

/**
 * 获取用户ip
 * @param
 * @return string
 * @throws
 * @author Lee $date2013.8.1$
 */
function getIP() {
    if (@$_SERVER["HTTP_X_FORWARDED_FOR"]) {
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    }else if (@$_SERVER["HTTP_CLIENT_IP"]) {
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    }else if (@$_SERVER["REMOTE_ADDR"]) {
        $ip = $_SERVER["REMOTE_ADDR"];
    }else if (@getenv("HTTP_X_FORWARDED_FOR")) {
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    }else if (@getenv("HTTP_CLIENT_IP")) {
        $ip = getenv("HTTP_CLIENT_IP");
    }else if (@getenv("REMOTE_ADDR")) {
        $ip = getenv("REMOTE_ADDR");
    }else {
        $ip = "0.0.0.0";
    }

    return $ip;
}
/**
 * 表单提交(post或get)
 * param  string $url
 * param  array  $paramArr
 * param  string $method
 * @return html->goto url page
 *
 * @throws
 * @author yjj $date2013.7.13$
 */
function formRequest($url, $paramArr=array(),$method="post") {
	if (empty($url)){
		return false;
	}
	$form= '<form id="formRequest" action="'.$url.'" method="'.$method.'"> ';
	if (!empty($paramArr)){
		foreach ($paramArr as $k=>$v) {
			$form.='<input type="hidden" name="'.$k.'" value="'.v.'" />';
		}
	}
  	$form.='</form>';
  	$form.='<script>document.getElementById("formRequest").submit();</script>';
  	echo $form ;
}

/**
 * @desc 获取静态资源的md5值，在引用css,js的时候跟上，以便静态资源文件更新的时候能够立即呈现
 * @param string $resourcePath css,js等静态资源文件的路径
 * @return string 返回静态资源文件内容的md5值
 */
/*function md5Resource($resourcePath) {
    if(file_exists($resourcePath)){
        return md5(file_get_contents($resourcePath));
    }
}*/

/**
 * @desc 2-20位 数字 字母 下划线
 * @param string $name
 * @return boolean
 * @author frank qian
 */
function checkName($name){
	return preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9_]{2,20}$/u', $name);
}
/**
 * @desc 6-20位 数字 字母
 * @param string $name
 * @return boolean
 * @author frank qian
 */
function checkStr($str){
	return preg_match('/^[0-9a-zA-Z]{6,20}$/', $str);
}
/**
 * @desc 验证是否为Email
 * @param string $value
 * @return boolean
 * @author frank qian
 */
function checkEmail($value) {
	return strlen($value) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $value);
}

/**
 * @desc 获取分组数组
 * @param int $rid
 * @return array
 * @author Frank 2013-08-17
 */
function getGradeArr($rid) {
	$separate = "&nbsp;<span>|</span>&nbsp;";
	switch ($rid) {
		case 1:
			$aryGrade = array('1' => '初级', '1,2' => '初级' . $separate . '中级',
			'1,2,3' => '初级' . $separate . '中级' . $separate . '高级',
			'2' => '中级', '2,3' => '中级' . $separate . '高级', '3' => '高级');
			$grades = array('初级', '中级', '高级');
			break;
		case 4:
			$aryGrade = array('1' => '苹果', '2' => '安卓+',
			'1,2' => '苹果' . $separate . '安卓+');
			$grades = array('苹果', '安卓+');
			break;
		default:
			$aryGrade = array();
			$grades = array();
	}
	$gradeArr['aryGrade'] = $aryGrade;
	$gradeArr['grades'] = $grades;
	return $gradeArr;
}
/**
 * @desc 防采集
 * @name randString
 * @return string randstr
 * @author Frank 2013-08-25
 */
function randString() {
	$array_bq = array("span", "font", "b", "strong", "div", "em");
	$array_class = array("cprt", "lnkcpt", "cpit", "lnkcpit", "fjc", "lnkfcj");
	$idx1 = rand(0, 5);
	$idx2 = rand(0, 5);
	
	$arr['bq1'] = $array_bq[$idx1];
	$arr['bq2'] = $array_bq[$idx2];
	$rdm = String::uuid();
	$arr['randstr'] = "<" . $array_bq[$idx1] . " class='" . $array_class[$idx2] . "'>欢迎来到另客网，" . $rdm . "近一点，更近一点" . $rdm . "</" . $array_bq[$idx1] . ">";
	return $arr;
}

/**
 * @desc 根据url获取页面内容，大部分返回的是json格式的数据
 * @name getContent
 * @param string url
 * @return array
 * @author Frank 2013-08-27
 */
function getContent($url) {
	$curl = curl_init();
	$options = array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $url
	);
	curl_setopt_array($curl, $options);
	$body = curl_exec($curl);
	curl_close($curl);
	return $body;
}
?>