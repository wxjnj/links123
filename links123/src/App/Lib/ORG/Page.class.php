<?php

// +----------------------------------------------------------------------

// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]

// +----------------------------------------------------------------------

// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.

// +----------------------------------------------------------------------

// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

// +----------------------------------------------------------------------

// | Author: liu21st <liu21st@gmail.com>

// +----------------------------------------------------------------------

// $Id: Page.class.php 2601 2012-01-15 04:59:14Z liu21st $



class Page {

    // 分页栏每页显示的页数

    public $rollPage = 5;

    // 页数跳转时要带的参数

    public $parameter  ;

    // 默认列表每页显示行数

    public $listRows = 20;

    // 起始行数

    public $firstRow	;

    // 分页总页面数

    protected $totalPages  ;

    // 总行数

    protected $totalRows  ;

    // 当前页数

    protected $nowPage    ;

    // 分页的栏的总页数

    protected $coolPages   ;

    // 分页显示定制

    protected $config  =	array('header'=>'条记录','prev'=>'上一页','next'=>'下一页','first'=>'第一页','last'=>'最后一页','theme'=>' %totalRow% %header%　<span style="color:#C00">%nowPage%</span>/%totalPage% 页　%upPage%　%downPage%　%first%　%prePage%　%linkPage%　%nextPage%　%end%　直达 <input url="%url%" type="text" style="width:40px;"/> 页');

    protected $config_front  =	array(

    		'header1'=>'<dt>本类别共<span class="red_text">',

    		'header2'=>'</span>条资讯<span class="margin_l">当前是第<span class="red_text">',

    		'header3'=>'</span>页(共<span class="red_text">',

    		'header4'=>'</span>页)</span></dt><dd>',

    		'prev'=>'<span class="china">&lt;</span>上一页',

    		'next'=>'下一页<span class="china">&gt;</span>',

    		'first'=>'',

    		'last'=>'',

    		'ender'=>'</dd>',

    		'theme'=>' %header1% %totalRow% %header2% %nowPage% %header3% %totalPage% %header4% %upPage% %linkPage% %downPage% %ender% %url%');

    

    /**

     +----------------------------------------------------------

     * 架构函数

     +----------------------------------------------------------

     * @access public

     +----------------------------------------------------------

     * @param array $totalRows  总的记录数

     * @param array $listRows  每页显示记录数

     * @param array $parameter  分页跳转的参数

     +----------------------------------------------------------

     */

    public function __construct($totalRows,$listRows='',$parameter='') {

        $this->totalRows = $totalRows;

        $this->parameter = $parameter;

        if(!empty($listRows)) {

            $this->listRows = intval($listRows);

        }

        $this->totalPages = ceil($this->totalRows/$this->listRows);     //总页数

        $this->coolPages  = ceil($this->totalPages/$this->rollPage);

        $this->nowPage  = !empty($_REQUEST[C('VAR_PAGE')])?intval($_REQUEST[C('VAR_PAGE')]):1;

        if(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {

            $this->nowPage = $this->totalPages;

        }

        $this->firstRow = $this->listRows*($this->nowPage-1);

    }



    public function setConfig($name,$value) {

        if(isset($this->config[$name])) {

            $this->config[$name]    =   $value;

        }

    }



    /**

     +----------------------------------------------------------

     * 分页显示输出

     +----------------------------------------------------------

     * @access public

     +----------------------------------------------------------

     */

    public function show() {

        if(0 == $this->totalRows) return '';

        $p = C('VAR_PAGE');

        $nowCoolPage      = ceil($this->nowPage/$this->rollPage);

        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'&':"?").$this->parameter;

        $parse = parse_url($url);

        if(isset($parse['query'])) {

            parse_str($parse['query'],$params);

            unset($params[$p]);

            $url   =  $parse['path'].'?'.http_build_query($params);

        }

        //上下翻页字符串

        $upRow   = $this->nowPage-1;

        $downRow = $this->nowPage+1;

        if ($upRow>0){

            $upPage="<a href='".$url."&".$p."=$upRow'>".$this->config['prev']."</a>";

        }else{

            $upPage="";

        }



        if ($downRow <= $this->totalPages){

            $downPage="<a href='".$url."&".$p."=$downRow'>".$this->config['next']."</a>";

        }else{

            $downPage="";

        }

        // << < > >>

        if($nowCoolPage == 1){

            $theFirst = "";

            $prePage = "";

        }else{

            $preRow =  $this->nowPage-$this->rollPage;

            $prePage = "<a href='".$url."&".$p."=$preRow' >上".$this->rollPage."页</a>";

            $theFirst = "<a href='".$url."&".$p."=1' >".$this->config['first']."</a>";

        }

        if($nowCoolPage == $this->coolPages){

            $nextPage = "";

            $theEnd="";

        }else{

            $nextRow = $this->nowPage+$this->rollPage;

            $theEndRow = $this->totalPages;

            $nextPage = "<a href='".$url."&".$p."=$nextRow' >下".$this->rollPage."页</a>";

            $theEnd = "<a href='".$url."&".$p."=$theEndRow' >".$this->config['last']."</a>";

        }

        // 1 2 3 4 5

        $linkPage = "";

        for($i=1;$i<=$this->rollPage;$i++){

            $page=($nowCoolPage-1)*$this->rollPage+$i;

            if($page!=$this->nowPage){

                if($page<=$this->totalPages){

                    $linkPage .= "&nbsp;<a href='".$url."&".$p."=$page'>&nbsp;".$page."&nbsp;</a>";

                }else{

                    break;

                }

            }else{

                if($this->totalPages != 1){

                    $linkPage .= "&nbsp;<span class='current'>".$page."</span>";

                }

            }

        }

        $pageStr	 =	 str_replace(

            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%','%url%'),

            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd,$url),$this->config['theme']);

        return $pageStr;

    }

    

    public function show_front() {

    	if(0 == $this->totalRows) return '';

    	$p = C('VAR_PAGE');

    	$nowCoolPage = ceil($this->nowPage/$this->rollPage);

    	$url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;

    	$parse = parse_url($url);

    	if(isset($parse['query'])) {

    		parse_str($parse['query'],$params);

    		unset($params[$p]);

    		$url   =  $parse['path'].'?'.http_build_query($params);

    	}

    	//上下翻页字符串

    	$upRow   = $this->nowPage-1;

    	$downRow = $this->nowPage+1;

    	if ($upRow>0){

    		$upPage="<a href='".$url."&".$p."=$upRow' class='page_go'>上一页</a>";

    	}else{

    		$upPage="";

    	}

    

    	if ($downRow <= $this->totalPages){

    		$downPage="<a href='".$url."&".$p."=$downRow' class='page_go'>下一页</a>";

    	}else{

    		$downPage="";

    	}

    	// << < > >>

    	if($nowCoolPage == 1){

    		$theFirst = "";

    		$prePage = "";

    	}else{

    		$preRow =  $this->nowPage-$this->rollPage;

    		$prePage = "<a href='".$url."&".$p."=$preRow' >上".$this->rollPage."页</a>";

    		$theFirst = "<a href='".$url."&".$p."=1' >1</a>&nbsp;...";

    	}

    	if($nowCoolPage == $this->coolPages){

    		$nextPage = "";

    		$theEnd="";

    	}else{

    		$nextRow = $this->nowPage+$this->rollPage;

    		$theEndRow = $this->totalPages;

    		$nextPage = "<a href='".$url."&".$p."=$nextRow' >下".$this->rollPage."页</a>";

    		$theEnd = "...&nbsp;<a href='".$url."&".$p."=$theEndRow' >".$theEndRow."</a>";

    	}

    	// 1 2 3 4 5

    	$linkPage = "";

    	for($i=1;$i<=$this->rollPage;$i++){

    		$page=($nowCoolPage-1)*$this->rollPage+$i;

    		if($page!=$this->nowPage){

    			if($page<=$this->totalPages){

    				$linkPage .= "&nbsp;<a href='".$url."&".$p."=$page'>".$page."</a>";

    			}else{

    				break;

    			}

    		}else{

    			if($this->totalPages != 1){

    				$linkPage .= "&nbsp;<span>".$page."</span>";

    			}

    		}

    	}

    	$pageStr = "<div class='page'>".$upPage."&nbsp;".$theFirst.$linkPage."&nbsp;".$theEnd."&nbsp;".$downPage."</div>";

    	$pageStr .= "<div class='page_tj'>本类别&nbsp;共&nbsp;<span>".$this->totalRows."</span>&nbsp;条信息&nbsp;当前是第&nbsp;".$this->nowPage."&nbsp;页（共<span>".$this->totalPages."</span>页）</div>";

    	return $pageStr;

    }

    

    //

    public function show_js() {

    	if(0 == $this->totalRows) return '';

    	$p = C('VAR_PAGE');

    	$nowCoolPage = ceil($this->nowPage/$this->rollPage);

    	//上下翻页字符串

    	$upRow   = $this->nowPage-1;

    	$downRow = $this->nowPage+1;

    	if ($upRow>0){

    		$upPage="<a p='".$upRow."' class='page_go'>上一页</a>";

    	}else{

    		$upPage="";

    	}

    

    	if ($downRow <= $this->totalPages){

    		$downPage="<a p='".$downRow."' class='page_go'>下一页</a>";

    	}else{

    		$downPage="";

    	}

    	// << < > >>

    	if($nowCoolPage == 1){

    		$theFirst = "";

    		$prePage = "";

    	}else{

    		$preRow =  $this->nowPage-$this->rollPage;

    		$prePage = "<a p='".$preRow."' >上".$this->rollPage."页</a>";

    		$theFirst = "<a p='1'>1</a>&nbsp;...";

    	}

    	if($nowCoolPage == $this->coolPages){

    		$nextPage = "";

    		$theEnd="";

    	}else{

    		$nextRow = $this->nowPage+$this->rollPage;

    		$theEndRow = $this->totalPages;

    		$nextPage = "<a p='".$nextRow."' >下".$this->rollPage."页</a>";

    		$theEnd = "...&nbsp;<a p='".$theEndRow."' >".$theEndRow."</a>";

    	}

    	// 1 2 3 4 5

    	$linkPage = "";

    	for($i=1;$i<=$this->rollPage;$i++){

    		$page=($nowCoolPage-1)*$this->rollPage+$i;

    		if($page!=$this->nowPage){

    			if($page<=$this->totalPages){

    				$linkPage .= "&nbsp;<a p='".$page."'>".$page."</a>";

    			}else{

    				break;

    			}

    		}else{

    			if($this->totalPages != 1){

    				$linkPage .= "&nbsp;<span>".$page."</span>";

    			}

    		}

    	}

    	$pageStr = "<div class='page'>".$upPage."&nbsp;".$theFirst.$linkPage."&nbsp;".$theEnd."&nbsp;".$downPage;

    	if ($this->totalPages > 1) {

    		$pageStr .= "&nbsp;　&nbsp;直达&nbsp;<input type='text' />&nbsp;页</div>";

    	}

    	else {

    		$pageStr .= "</div>";

    	}

    	$pageStr .= "<div class='page_tj'>本类别&nbsp;共&nbsp;<span>".$this->totalRows."</span>&nbsp;条信息&nbsp;<!-- 当前是第&nbsp;".$this->nowPage."&nbsp;页（共<span>".$this->totalPages."</span>页） --></div>";

    	return $pageStr;

    }
    /**
     * @author michael
     * 2013-07-27
     * ajax异步请求翻页调用
     * @return string
     */
    public function show_ajax_js() {

    	if(0 == $this->totalRows) return '';

    	$p = C('VAR_PAGE');
		if($p<1){$p=1;}
		if($this->nowPage<1){$this->nowPage=1;}
    	$nowCoolPage = ceil($this->nowPage/$this->rollPage);

    	//上下翻页字符串

    	$upRow   = $this->nowPage-1;

    	$downRow = $this->nowPage+1;

    	if ($upRow>0){

    		$upPage="<a href='javascript:getPage(".$upRow.")'  p='".$upRow."' >上一页</a>";

    	}else{

    		$upPage="";

    	}

    

    	if ($downRow <= $this->totalPages){

    		$downPage="<a p='".$downRow."' href='javascript:getPage(".$downRow.")' >下一页</a>";

    	}else{

    		$downPage="";

    	}

    	// << < > >>

    	if($nowCoolPage == 1){

    		$theFirst = "";

    		$prePage = "";

    	}else{

    		$preRow =  $this->nowPage-$this->rollPage;

    		$prePage = "<a p='".$preRow."' href='javascript:getPage(".$this->rollPage.")' >上".$this->rollPage."页</a>";

    		$theFirst = "<a href='javascript:getPage(1)' p='1'>1</a>&nbsp;...";

    	}

    	if($nowCoolPage == $this->coolPages){

    		$nextPage = "";

    		$theEnd="";

    	}else{

    		$nextRow = $this->nowPage+$this->rollPage;

    		$theEndRow = $this->totalPages;

    		$nextPage = "<a p='".$nextRow."'  href='javascript:getPage(".$this->rollPage.")' >下".$this->rollPage."页</a>";

    		$theEnd = "...&nbsp;<a p='".$theEndRow."'  href='javascript:getPage(".$theEndRow.")'>".$theEndRow."</a>";

    	}

    	// 1 2 3 4 5

    	$linkPage = "";

    	for($i=1;$i<=$this->rollPage;$i++){

    		$page=($nowCoolPage-1)*$this->rollPage+$i;

    		if($page!=$this->nowPage){

    			if($page<=$this->totalPages){

    				$linkPage .= "&nbsp;<a p='".$page."' href='javascript:getPage(".$page.")'>".$page."</a>";

    			}else{

    				break;

    			}

    		}else{

    			if($this->totalPages != 1){

    				$linkPage .= "&nbsp;<span>".$page."</span>";

    			}

    		}

    	}

    	$pageStr = "<div class='page'>".$upPage."&nbsp;".$theFirst.$linkPage."&nbsp;".$theEnd."&nbsp;".$downPage;

    	if ($this->totalPages > 1) {

    		$pageStr .= "&nbsp;　&nbsp;直达&nbsp;<input type='text' class='keyPage' />&nbsp;页</div>";

    	}

    	else {

    		$pageStr .= "</div>";

    	}

    	$pageStr .= "<div class='page_tj'>本类别&nbsp;共&nbsp;<span>".$this->totalRows."</span>&nbsp;条信息&nbsp;<!-- 当前是第&nbsp;".$this->nowPage."&nbsp;页（共<span>".$this->totalPages."</span>页） --></div>";

    	return $pageStr;

    }

    

    //

    public function show_js2() {

    	if(0 == $this->totalRows) return '';

    	$p = C('VAR_PAGE');

    	$nowCoolPage = ceil($this->nowPage/$this->rollPage);

    	//上下翻页字符串

    	$upRow   = $this->nowPage-1;

    	$downRow = $this->nowPage+1;

    	if ($upRow>0){

    		$upPage="<a p='".$upRow."' class='page_go'>上一页</a>";

    	}else{

    		$upPage="";

    	}

    

    	if ($downRow <= $this->totalPages){

    		$downPage="<a p='".$downRow."' class='page_go'>下一页</a>";

    	}else{

    		$downPage="";

    	}

    	// << < > >>

    	if($nowCoolPage == 1){

    		$theFirst = "";

    		$prePage = "";

    	}else{

    		$preRow =  $this->nowPage-$this->rollPage;

    		$prePage = "<a p='".$preRow."' >上".$this->rollPage."页</a>";

    		$theFirst = "<a p='1'>1</a>";

    	}

    	if($nowCoolPage == $this->coolPages){

    		$nextPage = "";

    		$theEnd="";

    	}else{

    		$nextRow = $this->nowPage+$this->rollPage;

    		$theEndRow = $this->totalPages;

    		$nextPage = "<a p='".$nextRow."' >下".$this->rollPage."页</a>";

    		$theEnd = "<a p='".$theEndRow."' >".$theEndRow."</a>";

    	}

    	$pageStr = "<div class='grzx_sc_page'>&nbsp;".$this->nowPage."/".$this->totalPages."&nbsp;".$upPage."&nbsp;".$downPage."</div>";

    	return $pageStr;

    }
	/**
	 * 生成并获得英语角视频搜索页面的分页导航
	 *
	 * @author Rachel
	 */
	public function getPaginationForVideoSearch($current_url, $pageNum) {
		if ($pageNum < 1 || $pageNum > $this->totalPages) {
			return "";
		}
		$currentPage = "<li class='page-current' ><a href='$current_url&".C('var_page')."=$pageNum'>" . $pageNum . "</a></li>";
		$prePage = "<li><a  href='$current_url&".C('var_page')."=" . ($pageNum - 1) . "'>" . ($pageNum - 1) . "</a></li>";
		$nextPage = "<li><a  href='$current_url&".C('var_page')."=" . ($pageNum + 1) . "'>" . ($pageNum + 1) . "</a></li>";
		$firstPage = "<li><a  href='$current_url&".C('var_page')."=" . (1) . "'>" . (1) . "</a></li>";
		$lastPage = "<li><a  href='$current_url&".C('var_page')."=" . ($this->totalPages) . "'>" . ($this->totalPages) . "</a></li>";
		if ($this->totalPages <= 1) {
			$pageStr = "";
		} else if ($this->totalPages > 1) {
			if ($pageNum == 1) {
				$prePage = "";
			}
			if ($pageNum == $this->totalPages) {
				$nextPage = "";
			}
			if ($pageNum <= 2) {
				$firstPage = "";
			}
			if ($pageNum >= $this->totalPages - 1) {
				$lastPage = "";
			}
			if ($pageNum > 3) {
				$firstPage = $firstPage . "<li>……</li>";
			}
			if ($pageNum < $this->totalPages - 2) {
				$lastPage = "<li>……</li>" . $lastPage;
			}
		}
		//var_dump($nextPage); exit;
		$pageStr = "<ul>$firstPage$prePage$currentPage$nextPage$lastPage</ul>";
		
		if ($this->totalPages > 1) {
			
			$pageStr = '<div class="pagination"><div>
                            直达 <input type="text" /> 页
                        </div>' . $pageStr . "</div>";
		}
		return $pageStr;
	}

////////////////////////////// Andrew's Code for SEO ///////////////////////////////////////    
public function getCurrentPage(){
	
return $this->nowPage;
	
}
 ////////////////////////////// Andrew's Code for SEO ///////////////////////////////////////
}