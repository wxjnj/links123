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

	public function index_new(){
		if(!empty($_SERVER["HTTP_CLIENT_IP"]))  
   		$cip = $_SERVER["HTTP_CLIENT_IP"];  
		else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))  
   		$cip = $_SERVER["HTTP_X_FORWARDED_FOR"];  
		else if(!empty($_SERVER["REMOTE_ADDR"]))  
   		$cip = $_SERVER["REMOTE_ADDR"];  
		else  
   		$cip = "无法获取！";  
		$region = file_get_contents('http://ip.taobao.com/service/getIpInfo.php?ip=182.92.247.2');
		$this->assign('region', $region->data->city);
		$this->display();
	}

	public function city(){
		if(isset($_GET['city'])){
			$szUrl = 'http://ext.weather.com.cn/city?'.$_GET['city'];
			$UserAgent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; .NET CLR 3.5.21022; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $szUrl);
			curl_setopt($curl, CURLOPT_REFERER, 'http://ext.weather.com.cn/');
			curl_setopt($curl, CURLOPT_HEADER, 0);  //0表示不输出Header，1表示输出
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_ENCODING, '');
			curl_setopt($curl, CURLOPT_USERAGENT, $UserAgent);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
			$data = curl_exec($curl); 
			$this->assign('city', $data);
		}
		$this->display();
	}

	public function data(){
		if(isset($_GET['id'])){
			$region = $_GET['id'];
			$szUrl = 'http://ext.weather.com.cn/' . $region . '.json';
			$UserAgent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; .NET CLR 3.5.21022; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $szUrl);
			curl_setopt($curl, CURLOPT_REFERER, 'http://ext.weather.com.cn/');
			curl_setopt($curl, CURLOPT_HEADER, 0);  //0表示不输出Header，1表示输出
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_ENCODING, '');
			curl_setopt($curl, CURLOPT_USERAGENT, $UserAgent);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
			$data = curl_exec($curl); 
			$this->assign('weather_json', $data);
		}
		$this->display();
	}
	
	public function index() {
		$ip = get_client_ip();
		//$ip = '112.22.83.38';
 		$url = 'http://ip.taobao.com/service/getIpInfo.php?ip='.urlencode($ip);
 		$body = getContent($url);
 		$d = json_decode($body, true);
 		
 		$city = $d['data']['city'];
 		if(empty($city)) {
 			//如何获取不到城市默认是北京
 			$cityId = "101010100";
 		} else {
 			$city = trim(str_replace('市', '', $city));
 			//$city = '无锡';
 			$cities = M('cities');
 			$cityId = $cities->where("city = '%s'", $city)->getField('id');
 		}
 		
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