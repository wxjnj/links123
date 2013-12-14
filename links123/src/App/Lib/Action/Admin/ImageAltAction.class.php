<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 13-12-14
 * Time: 下午4:09
 */

class ImageAltAction  extends  CommonAction{
        public function index(){
            $arr=$this->getImages($_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR."Public");
            print_r($arr);
            $this->show("Test");
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
                           $arr[]=$file;
                       }
                   }
               }
           }
           return $arr;

        }
} 