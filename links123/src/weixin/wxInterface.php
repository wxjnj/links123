<?php
/**
 * Created by JetBrains PhpStorm.
 * User: spring
 * Date: 11/18/13
 * Time: 9:21 PM
 * To change this template use File | Settings | File Templates.
 */



include "WXClass.class.php";

$options = array(
    'token'=>'lkw' //填写你设定的key
);
$wxObj= new Wechat($options);
$wxObj->valid();
$type = $wxObj->getRev()->getRevType();
switch($type) {
    case WXClass::MSGTYPE_TEXT:
        $wxObj->text("你好，欢迎你访问另客网，另客网的网址是：http://www.linkx123.cn")->reply();
        exit;
        break;
    case WXClass::MSGTYPE_EVENT:
        break;
    case WXClass::MSGTYPE_IMAGE:
        break;
    default:
        $wxObj->text("你好，欢迎你访问另客网，另客网的网址是：http://www.linkx123.cn")->reply();
}
?>