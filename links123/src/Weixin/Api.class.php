<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 13-11-21
 * Time: 下午5:13
 */

class  Api {
    public static  function getWeather($city){
        $url="http://php.weather.sina.com.cn/xml.php?city=".mb_convert_encoding($city, 'GB2312', 'UTF-8')."&password=DJOYnieT8234jlsK&day=0";
         $content=file_get_contents($url);
         $xml= simplexml_load_string($content);
        $returnstr=$city."天气预报\n".$xml->Weather->status1."\t".$xml->Weather->direction1.$xml->Weather->power1
            ."级\n温度：".$xml->Weather->temperature2."~".$xml->Weather->temperature1."度，"
            ."\n紫外线：".$xml->Weather->zwx_l."\n洗车指数：".$xml->Weather->xcz_s."\n穿衣指数：".$xml->Weather->chy_shuoming;
        return  $returnstr;
    }

    public  static  function getReply($input){
        if($input=='另客网'){
            return "\t另客英语让你告别聋哑英文，另客桌面让你成为高效的懒人。\n\t我们是一支云团队，".
            "成员来自中国和美国的十多个城市。我们发现，地道的英文和高效的桌面是通向成功的捷径。甚至可以不夸张地说，".
            "她们本身就是美好生活的一部分。为了分享，我们每天都在努力----around the globe, around the clock!\n\t我们是英语迷，我们是桌面控！";
        }
       else if($input=='你好'||$input=='您好'||$input=='好'){
            return "你好，这是另客网，我们好好聊聊。";
        }
       else if($input=='你'||$input=='您'){
            return "你……，您是那位。";
        }
       else if($input=='靠'||$input=='我靠'||$input=='你靠'){
            return "靠边上。讲话请文明";
        }
        else if($input=='天'||$input=='气'){
            return "天气好晴朗，可以出门逛一逛";
        }
        else if($input=='逛街'){
            return "好啊，好啊。有人陪我逛街。";
        }
        else if(strtolower($input)=='hello'){
            return "Hello";
        }
        else{
            return false;
        }
    }

    public  static function translate($input){
        $url="http://fanyi.youdao.com/openapi.do?keyfrom=links123cn&key=1695588868&type=data&doctype=json&version=1.1&q=".urlencode($input);
        $content=file_get_contents($url);
        $returnstr="";
        if($content){
            $jsonobj=json_decode($content);

            foreach($jsonobj->translation as $value){
                $returnstr.=$value.";";
            }
        }
        return rtrim($returnstr,';');
    }

    public  static function getGNNews($count=0){
        if($count<=0){
          $count=10;
        }
        $content=file_get_contents("http://news.qq.com/newsgn/rss_newsgn.xml");
        $data=simplexml_load_string($content);
        $content="";
        for($i=0;$i<$count;$i++){
            $content.="$i.<a href='".$data->channel->item[$i]->link."'>".$data->channel->item[$i]->title."</a>\n";
        }
       return $content;
    }
}