<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 13-12-14
 * Time: 下午4:09
 */

class ImageAltAction  extends  CommonAction{
        public function index(){
            $page=$_REQUEST["page"];
            $type=$_REQUEST["type"];
            $page=intval($page);
            if($page<0)
                $page=0;
            $arr=$this->getImages($_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR."Public");
            $allrecords=count($arr);
            $arr=array_chunk($arr,25);
            $pages=count($arr);
            if($page>=$pages){
                $page=$pages-1;
            }
            $pagestr ="<div class=\"page\"> $allrecords 条记录&#12288;<span style=\"color:#C00\">".($page+1)."</span>/".$pages." 页&#12288;&#12288;";
            if($page>=5)
                $pagestr.="<a href='/Admin/ImageAlt?page=".($page-5)."'>上5页</a>";
            if($page%5==0&&$pages>$page+5)
                $temppages=$page+5;
            else
                $temppages=$page-($page%5)+5;
            for($i=$page-($page%5);$i<=$temppages;$i++){
                    $pagestr.="&nbsp;&nbsp;<a href='/Admin/ImageAlt?page=".$i."'>".($i+1)."</a>";
            }
            $pagestr.="<a href='/Admin/ImageAlt?page=".($page+6)."'>下5页</a></div>";
            $this->assign("pagestr",$pagestr);
            $this->assign("list",$arr[$page]);
            $this->display();
        }
        private function getImages($dir){
            $image_type=array("jpg",'jpeg','gif','png');
           if(! $handle=@opendir($dir)){
               return false;
           }
            $arr=array();
           while(false!==($file=readdir($handle))){
               if($file!=='.'&&$file!=='..'){
                   $file=$dir.DIRECTORY_SEPARATOR.$file;
                   if(is_dir($file)){
                       $temparr=$this->getImages($file);
                       $arr=array_merge($arr,$temparr);
                   }
                   else{
                       $file_ext=  end(explode('.', $file));
                       if(in_array(strtolower($file_ext),$image_type)){
                           $arr[]=str_replace($_SERVER["DOCUMENT_ROOT"],'',$file);
                       }
                   }
               }
           }
           return $arr;

        }
} 