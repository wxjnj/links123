<?php

// 目录模型
class CategoryModel extends CommonModel {

    public $_validate = array(
        array('cat_name', 'require', '目录名称必须'),
    );

    public function getIndexCategoryLinksList($lan, $cid, $grade, $sort,$page = false) {
        
    	import("@.ORG.String");
        $list = array();
        if ($cid == 0) {
            $cid = $this->where('status=1 and level=1')->min('id');
        }
        
        //当前分类信息
        $list['cat_info'] = $this->where("id={$cid} and status=1")->find();
        //获取根分类信息
        if ($list['cat_info']['prt_id'] == 0) {
            $list['root_cat_info'] = $list['cat_info'];
        } else {
            $list['root_cat_info'] = $this->where("id={$list['cat_info']['prt_id']} and status=1")->find();
        }
        //默认等级数组
        $ary_grade = array();
        switch ($list['root_cat_info']['id']) {
            case 1:
                $ary_grade = array('1' => '初级', '1,2' => '初级&nbsp;<span>|</span>&nbsp;中级', '1,2,3' => '初级&nbsp;<span>|</span>&nbsp;中级&nbsp;<span>|</span>&nbsp;高级', '2' => '中级', '2,3' => '中级&nbsp;<span>|</span>&nbsp;高级', '3' => '高级');
                $list['grades'] = array('初级', '中级', '高级');
                break;
            case 4:
                $ary_grade = array('1' => '苹果', '2' => '安卓+', '1,2' => '苹果&nbsp;<span>|</span>&nbsp;安卓+');
                $list['grades'] = array('苹果', '安卓+');
                break;
        }
        //当前顶级分类下的分类列表
        $list['cat_list'] = $this->where("prt_id={$cid} and flag={$lan} and status=1")->order("sort")->select();
        //当前顶级分类下的图片
        $list['cat_pic'] = D("CatPic")->where("rid=" . intval($list['root_cat_info']['id']))->order('sort')->select();
        $condition = array();
        if ($list['cat_info']['prt_id'] == 0) {
            $condition['category.prt_id'] = $list['cat_info']['id'];
        } else {
            $condition['links.category'] = $list['cat_info']['id'];
        }
        $condition['links.language'] = $lan;
        if (intval($grade) > 0) {
            $condition['links.grade'] = array('like', '%' . $grade . '%');
        }
        if (empty($sort)) {
            $sort = "category.sort asc,links.sort asc";
        }
        //
        if (!isset($_SESSION['pailie'])) {
            $dftPailie = M("Variable")->where("vname='pailie'")->getField("value_int");
            $_SESSION['pailie'] = $dftPailie;
            //
            if (isset($_SESSION[C('MEMBER_AUTH_KEY')])) {
                $_SESSION['pailie'] = M("Member")->where('id=' . $_SESSION[C('MEMBER_AUTH_KEY')])->getField('pailie');
            }
            //
            if (empty($_SESSION['pailie'])) {
                $_SESSION['pailie'] = $dftPailie;
            }
        }
        if ($_SESSION['pailie'] == 1) {
            $listRows = 20;
        } else {
            $listRows = 11;
        }
        if($page!==false){
        	$pg = $page;
        }else{
	        if (isset($_REQUEST[C('VAR_PAGE')])) {
	            $pg = $_REQUEST[C('VAR_PAGE')];
	        }else{
	        	$pg = 1;
	        }
        }
        if (intval($pg) <= 0) {
            $pg = 1;
        }
        $rst = ($pg - 1) * $listRows;
        //
        $linksViewModel = new LinksFntViewModel();
        $condition['links.status'] = 1;
        // 分页
        $count = $linksViewModel->where($condition)->count('links.id');
        if ($count > 0) {
            import("@.ORG.Page");
            $p = new Page($count, $listRows);
            $list['page'] = $p->show_ajax_js();
        }
        
        $list['links'] = $linksViewModel->getLists($condition, $sort, $rst, $listRows, $list['root_cat_info']['id'], $ary_grade);
        
        return $list;
    }

}

?>