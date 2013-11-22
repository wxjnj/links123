<<<<<<< HEAD
<?php
/**
 * Created by JetBrains PhpStorm.
 * User: spring
 * Date: 11/18/13
 * Time: 9:21 PM
 * To change this template use File | Settings | File Templates.
 */



include "WXClass.class.php";
include "Api.class.php";
$options = array(
    'token'=>'lkw' //填写你设定的key
);
$wxObj= new WXClass($options);
$wxObj->valid();
$type = $wxObj->getRev()->getRevType();
switch($type) {
    case WXClass::MSGTYPE_TEXT:
        $userinput=$wxObj->getRevContent();
        $matches=array();
        if(preg_match("/天气@(\W+)/",$userinput,$matches)){
            $city=$matches[1];
            $outputstr=Api::getWeather($city);
            $wxObj->text($outputstr)->reply();
        }else{
             $wxObj->text("你好，欢迎你访问另客网，另客网的网址是：http://www.links123.cn")->reply();
        }
        break;
    case WXClass::MSGTYPE_EVENT:
        if( $wxObj->getEventType()=="subscribe"){
            $wxObj->text("你好，欢迎你访问另客网，另客网的网址是：http://www.links123.cn")->reply();
        }
        break;
    case WXClass::MSGTYPE_IMAGE:
        break;
    default:
        $wxObj->text("你好，欢迎你访问另客网，另客网的网址是：http://www.links123.cn")->reply();
}
exit;
=======
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
>>>>>>> 7664c92aa29e3deb638fae641e23ce549e421c12
?>