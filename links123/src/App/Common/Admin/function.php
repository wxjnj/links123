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
    if (empty($type)) {
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
function getStorageTypeName($type) {
    $name = "";
    switch ($type) {
        case 1:
            $name = "本地";
            break;
        case 2:
            $name = "外链";
            break;
        default:
            $name = "未知";
            break;
    }
    return $name;
}

?>
