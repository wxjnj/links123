<?php
/**
 * @name WeatherAction
 * @desc 天气预报接口
 * @package Home
 * @version 1.0
 * @author frank UPDATE 2013-08-27
 */
import("@.Common.CommonAction");
class WeatherAction extends CommonAction {
	
	public function index() {
		//$ip = get_client_ip();
		$ip = '112.22.83.38';
 		$url = 'http://ip.taobao.com/service/getIpInfo.php?ip='.urlencode($ip);
 		$body = getContent($url);
 		$d = json_decode($body, true);
 		$city = $d['data']['city'];
 		$city = str_replace('市', '', $city);
        //$city = '无锡';
		$cities = M('cities');
		$cityId = $cities->where("city = '%s'", $city)->getField('id');
		
		//获取6天的天气信息
		$url = 'http://m.weather.com.cn/data/'.$cityId.'.html';
		$sixweather = getContent($url);
		$sixweather = json_decode($sixweather, true);
		//echo "<pre>";
		//print_r($sixweather);
		//echo "</pre>";
		//获取当前的实时天气
		$url = 'http://www.weather.com.cn/data/cityinfo/'.$cityId.'.html';
		$curweather = getContent($url);
		$curweather = json_decode($curweather, true);
		$weekarray=array("日","一","二","三","四","五","六");
		//echo "<br>";
		//print_r($curweather);
		$data[] = "星期".$weekarray[date("w")%7];
		$data[] = "星期".$weekarray[(date("w")+1)%7];
		$data[] = "星期".$weekarray[(date("w")+2)%7];
		$data[] = "星期".$weekarray[(date("w")+3)%7];
		$data[] = "星期".$weekarray[(date("w")+4)%7];
		//http://www.weather.com.cn/data/cityinfo/101010100.html
		$this->assign('data', $data);
		$this->assign('sixweather', $sixweather);
		$this->assign('curweather', $curweather);
		$this->display();
	}
}