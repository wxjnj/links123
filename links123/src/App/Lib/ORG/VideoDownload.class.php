<?php

/**
 * 视频下载类
 * @package ORG
 * @author Adam $date2013-07-17$
 */
class VideoDownload {

    const USER_AGENT = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:19.0) Gecko/20100101 Firefox/19.0 Chromium/18.0.1025.168 Chrome/18.0.1025.168 Safari/535.19';

    private $_error; //错误信息
    private $_url; //下载的视频所在的网址
    private $_savePath; //视频下载的目录
    //支持下载的网站地址列表
    private $_supportWebsite = array(
        'youku.com' => "_youku",
        "peepandthebigwideworld.com" => "_peepandthebigwideworld"
    );
    private $_supportType = array('swf', 'flv', 'mp4', 'mp3'); //可下载视频的类型
    //Content-type对应的文件mineType数组
    private $_fileType = array(
        'application/x-shockwave-flash' => 'swf',
        'video/x-flv' => "flv"
    );

    public function __construct() {
        $path = realpath('./Public/Uploads/uploads.txt');
        $this->_savePath = str_replace('uploads.txt', 'Video', $path) . '\\';
        if (!is_dir($this->_savePath)) {
            @mkdir($this->_savePath);
        }
       // set_time_limit(0);
    }

    /**
     * 获取网址的内容
     * @param string $url [网址]
     * @return string
     * @author  Adam $date2013-07-17$
     */
    public function getWebContent($url) {
        if (!$url) {
            $this->setError('URL IS NULL');
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $str = trim(curl_exec($ch));
        curl_close($ch);

        if (!$str) {
            $this->setError('IS NOT HTML');
            return false;
        }

        $str = mb_convert_encoding($str, 'utf-8', 'gbk, gb18030, utf8, GB2312'); //内容编码转换

        return $str;
    }
    
    /**
     * curl download
     * 
     * @param  $remote 下载文件地址
     * @param  $local 本地文件存储
     * 
     * @return void
     * 
     * @author slate date: 2013-07-17
     */
    public function curl_download($remote, $local) {
    	$cp = curl_init($remote);
    	if (!$fp = fopen($local, "w")) {
    		$this->setError("SAVE ERROR1");
    		return false;
    	}
    
    	curl_setopt($cp, CURLOPT_FILE, $fp);
    	curl_setopt($cp, CURLOPT_HEADER, 0);
    
    	curl_exec($cp);
    	
    	//$errno = curl_errno(cp);
    	//$errmsg = curl_error($fp);
    	
    	curl_close($cp);
    	fclose($fp);
    }

    public function download($url) {
        $fileInfo = array();
        $url = trim($url);
        if (!$url) {
            $this->setError('URL IS NULL');
            return false;
        }

        $website = false;
        //获取url地址的基本地址
        foreach ($this->_supportWebsite as $key => $value) {
            if (false !== stripos($url, $key)) {
                $website = $value;
                break;
            }
        }

        //不支持的网站，提示
        if (!$website) {
            $this->setError('NOT  ALLOW WEBISITE');
            return false;
        }

        $this->_url = $url;
        $fileInfo['web_url'] = $url;
        
        $startTime = time();

        //根据网站对应的解析方法获取视频的真实源地址
        $videoRealUrl = $this->$website();
        //解析失败
        if (!$videoRealUrl) {
            $this->setError("ANALYSIS VIDEO REAL URL FAILUER");
            return false;
        }
        $fileInfo['source_url'] = $videoRealUrl;
       /** $str = "";
        //下载视频
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($ch, CURLOPT_URL, $videoRealUrl);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_REFERER, $url);//伪来源
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $str = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        //请求失败，源地址无效
        if (!$info) {
            $this->setError("SOURCE IS UNAVAILABLE");
            return false;
        }
        $videoMineType = $this->_fileType[$info['content_type']];
        **/
        $videoMineType = end(explode('.', $videoRealUrl));	//根据$videoRealUrl取文件后缀名
        //视频类型不支持
        if (empty($videoMineType)) {
            $this->setError("FILE IS NOT ALLOW");
            return false;
        }
        $fileInfo['mineType'] = $videoMineType;
        $fileInfo['name'] = uniqid() . "." . $videoMineType;
        $fileInfo['path'] = $this->_savePath;

       /** if (!$handle = fopen($this->_savePath . $fileInfo['name'], "a")) {
            $this->setError("SAVE ERROR1");
            return false;
        }
        //分节写入文件
        $now_length = 0;
        $total_length = strlen($str);
        while ($now_length < $total_length && fwrite($handle, substr($str, $now_length, 10))) {
            $now_length+=10;
        }
        fclose($handle);**/
        $this->curl_download($videoRealUrl, $this->_savePath . $fileInfo['name']);
        
        $fileInfo['downloadTime'] = time() - $startTime;	//下载时间
        return $fileInfo;
    }

    public function getError() {
        return $this->_error;
    }

    private function setError($error) {
        $this->_error = $error;
    }

    public function setSavePath($path) {
        $this->_savePath = $path;
    }

    private function _peepandthebigwideworld() {
        $html = $this->getWebContent($this->_url);
        if (!$html) {
            return false;
        }
        // 获取视频源地址
        $flashvars = $this->match('/<param name=\"flashvars\" value=\"epis=(.*)&amp;cc=0\" \/\>/i', $html, 1);
        $swfUrl = $this->match('/<embed.*src=(\'|\")(.*\.swf)(\'|\")/i', $html, 2);
        $baseUrl = substr($swfUrl, 0, strpos($swfUrl, "video_player.swf"));
        // <embed src="http://d21na5cfk0jewa.cloudfront.net/videos/media/video_player.swf">
        //视频源：http://d21na5cfk0jewa.cloudfront.net/videos/media/patternplay.flv
        $realUrl = $baseUrl . $flashvars . ".flv";
        return $realUrl;
    }

    /**
     * 使用正则匹配获取字符串
     * @param string $pattern 需要匹配的正则表达式
     * @param string $subject 用于匹配的字符串
     * @param integer $num 需要获取匹配的第几个值
     * @return string
     */
    public static function match($pattern, $subject, $num = 1) {
        $boolean = preg_match($pattern, $subject, $matches);
        $str = '';
        if ($boolean) {
            $str = $matches[$num];
        }
        return $str;
    }

    public static function parsmEncode($params, $isRetStr = true) {
        $fieldStr = '';
        $spr = '';
        $result = array();
        foreach ($params as $key => $value) {
            $value = urlencode($value);
            $fieldStr .= $spr . $key . '=' . $value;
            $spr = '&';
            $result[$key] = $value;
        }
        return $isRetStr ? $fieldStr : $result;
    }

}

?>
