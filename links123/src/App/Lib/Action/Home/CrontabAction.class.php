<?php

/**
 * 定时任务
 *
 * @author slate date:2013-12-15
 */

ignore_user_abort();
set_time_limit(1000);

class CrontabAction extends Action {

	private $token = 'zDtikmJha4Lh6';
	
	private $errMsg = '';
	
    public function index() {
		
    	$token = $this->_param('token');
    	if ($this->token != $token) { exit('TOKEN ERROR');}
    	
    	$nowHour = intval(date('H'));
    	$nowMinute = intval(date('i'));
    	
    	switch ($nowHour) {
    		case 23 :
    			if ($nowMinute < 5) {
    				$this->updateSitemap();
    			}
    			break;
    		default:
    			
    			break;
    	}
    	
    	$this->updateHomeData();
    }
   
    /**
     * 定时更新首页数据缓存
     * 
     * @author slate date:2013-12-15
     */
    private function updateHomeData() {
    	
    	$newsService = D('News','Service');
    	$newsData = $newsService->getAllNews();
    	
    	if (!$newsData['news']) {
	    	Log::write('CRON NEWS ERROR', Log::ERR);
    	}
    }
    
    private function updateSitemap() {
    	file_get_contents('http://'.$_SERVER['HTTP_HOST'].'/Sitemap');
    }

}

?>
