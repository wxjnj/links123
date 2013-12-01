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
		if(preg_match("/天气\@(\w+)/",$userinput,$matches)){
			$wxObj->text(  Api::getWeather($matches[0]))->reply();
		}else if(Api::getReply($userinput)){
			$wxObj->text(  Api::getReply($userinput))->reply();
		}
		else
			$wxObj->text("你好，欢迎你访问另客网，另客网的网址是：http://www.links123.cn")->reply();
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
?>