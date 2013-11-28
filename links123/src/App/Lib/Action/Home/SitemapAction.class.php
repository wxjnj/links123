<?php
/**
 * @name SitemapAction
 * @desc 存档页
 * @package Home
 * @version 1.0
 * @author Go 2013-11-27
 */
import("@.Common.CommonAction");
class SitemapAction extends CommonAction {
	/**
	 * @desc index
	 */
	public function index() {
		$this->xml();
	}
	public function xml(){
		set_time_limit(0);
		$cat = M("Category");
		$links = M("Links");

		$file_array = array();
		$cat_list = $cat->field("id,cat_name,prt_id,level")->where(array("status"=>1))->select();

		//分类单独存储一个文件
		$cat_xml = '<?xml version="1.0" encoding="UTF-8"?>';
		$cat_xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		$lastmod = date('Y-m-d',time());
		foreach($cat_list as $cat){
			$cat_xml .= '<url>';
			$cat_xml .= '<loc>'.U('Home/Index/nav','',true,false,true).'?cid='.$cat['id'].'</loc>';
			$cat_xml .= '<lastmod>'.$lastmod.'</lastmod>';
			$cat_xml .= '<changefreq>daily</changefreq>';
			$cat_xml .= '</url>';
		}
		$cat_xml .= '</urlset>';
		$cat_filename = ROOT_PATH.'/sitemap_cat.xml';
		file_put_contents($cat_filename,$cat_xml);
		$file_array[] = $cat_filename;
		//一个站点地图文件包含的网址不得超过 5 万个,超过需要整合进索引文件
		//https://support.google.com/webmasters/answer/71453?hl=zh-Hans&ref_topic=8476
		$i = 0;
		$limit = 10000;//每次查询1万
		while($links_list = $links->field("id,create_time")->where(array("status"=>1))->limit($i * $limit,$limit)->select()){
			$i++;
			$xml = '<?xml version="1.0" encoding="UTF-8"?>';
			$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
			foreach($links_list as $link){
				$xml .= '<url>';
				$xml .= '<loc>'.U('Home/Detail/index','',true,false,true).'?id='.$link['id'].'</loc>';
				$xml .= '<lastmod>'.date('Y-m-d',$link['create_time']).'</lastmod>';
				$xml .= '<changefreq>daily</changefreq>';
				$xml .= '</url>';
			}
			$xml .= '</urlset>';
			$filename = ROOT_PATH.'/sitemap_'.$i.'.xml';
			file_put_contents($filename,$xml);
			$file_array[] = $filename;
		}
		$xmlindex = '<?xml version="1.0" encoding="UTF-8"?>';
		$xmlindex .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		foreach($file_array as $file){
			$file = (is_ssl()?'https://':'http://').$_SERVER['HTTP_HOST'].substr($file,strrpos($file,'/'));
			$xmlindex .= '<sitemap>';
			$xmlindex .= '<loc>'.$file.'</loc>';
			$xmlindex .= '<lastmod>'.$lastmod.'</lastmod>';
			$xmlindex .= '</sitemap>';
		}
		$xmlindex .= '</sitemapindex>';
		$index_file = ROOT_PATH.'/sitemap.xml';

		if(file_put_contents($index_file,$xmlindex)){
			echo "success";
		}else{
			echo "error";
		}
	}
	public function html(){
		set_time_limit(0);
		$this->getHeaderInfo();
		$cat = M("Category");
		$links = M("Links");

		$cat_list = $cat->field("id,cat_name,prt_id,level")->where(array("status"=>1))->select();
		$cat_list_tmp  = array();
		foreach($cat_list as $cat){
			if($cat['level']==1){
				$cat_list_tmp[$cat['id']] = $cat;
			}else{
				$cat_list_tmp[$cat['prt_id']]['children'][$cat['id']] = $cat;
			}
		}
		$this->assign("cat_list",$cat_list_tmp);
		$links_list = $links->field("id,title,link,category")->where(array("status"=>1))->select();
		$cat_links = array();
		foreach($links_list as $links){
			$cat_links[$links['category']][] = $links;
		}
		$this->assign("cat_links",$cat_links);
		$html = $this->fetch('html');
		$filename = ROOT_PATH.'/sitemap.html';
		if(file_put_contents($filename,$html)){
			echo "success";
		}else{
			echo "error";
		}
	}

} 