<?php

/**
 * 获取英语角媒体格式类型名称
 * @param int $pattern
 * @return string
 * @author Adam $date2013.08.26$
 */
function getMediaPatternName($pattern) {
    $name = "";
    switch ($pattern) {
        case 1:
            $name = "视频";
            break;
        case 2:
            $name = "音频";
            break;
        default:
            $name = "未知";
            break;
    }
    return $name;
}

/**
 * 获取英语角媒体播放类型名称
 * @param int $type
 * @return string
 * @author Adam $date2013.08.26$
 */
function getMediaPlayTypeName($type) {
    //0:正常URL播放;1:Object内嵌播放;2:iframe内嵌播放;3:swfobject插件播放;4:本地视频播放
    $name = "未知";
    if ($type != "0" && empty($type)) {
        return $name;
    }
    switch ($type) {
        case 0:
            $name = "正常URL播放";
            break;
        case 1:
            $name = "Object内嵌播放";
            break;
        case 2:
            $name = "iframe内嵌播放";
            break;
        case 3:
            $name = "swfobject插件播放";
            break;
        case 4:
            $name = "本地视频播放";
            break;
        default:
            $name = "未知";
            break;
    }
    return $name;
}

/**
 * 获取媒体存储类型名称
 * @param int $type
 * @return string
 * @author Adam $date2013.08.26$
 */
function getMediaPriorityTypeName($type) {
    $name = "";
    switch ($type) {
        case 1:
            $name = "外链";
            break;
        case 2:
            $name = "本地";
            break;
        default:
            $name = "外链";
            break;
    }
    return $name;
}

/**
 * 获取媒体专题父类名称
 * @param int $pid
 * @return string
 * @author Adam $date2013.08.26$
 */
function getEnglishMediaSubjectPidName($pid) {
    $name = "顶级专题";
    if (intval($pid) > 0) {
        $map['id'] = intval($pid);
        $name = D("EnglishMediaSubject")->where($map)->getField("name");
    }
    return $name;
}

/**
 * 获取媒体推荐分类父类名称
 * @param int $pid
 * @return string
 * @author Adam $date2013.08.30$
 */
function getEnglishMediaRecommendPidName($pid) {
    $name = "顶级推荐";
    if (intval($pid) > 0) {
        $map['id'] = intval($pid);
        $name = D("EnglishMediaRecommend")->where($map)->getField("name");
    }
    return $name;
}

/**
 * 获取媒体难度名称
 * @param int $difficulty
 * @return string
 * @author Adam $date2013.08.27$
 */
function getMediaDifficultyName($difficulty) {
    $name = "";
    switch ($difficulty) {
        case 1:
            $name = "初级";
            break;
        case 2:
            $name = "中级";
            break;
        case 3:
            $name = "高级";
            break;
        default:
            $name = "未知";
            break;
    }
    return $name;
}

//
function getLinkToHrefWithOutHttp($link) {
    return "<a target='_blank' href='" . $link . "'>" . $link . "</a>";
}

//
function getMediaRecommendYorN($recommend, $info) {
    $str = "否";
    if (!empty($recommend) && $recommend != 0) {
        $ret = D("EnglishMediaRecommend")->where(array("id", array("in", $recommend), "status" => 1))->count();
        if (intval($ret) > 0) {
            $str = "是";
        }
    }
    return $str;
}

?>
