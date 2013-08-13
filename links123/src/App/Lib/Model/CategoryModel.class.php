<?php

// 目录模型
class CategoryModel extends CommonModel {

    public $_validate = array(
        array('cat_name', 'require', '目录名称必须'),
    );

    public function getIndexCategoryLinksList($lan, $cid, $grade, $sort,$page = false) {
        import("@.ORG.String");
        if (intval($lan) == 0) {
            $lan = 1;
        }
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
        $list['links'] = $linksViewModel->where($condition)->order($sort)->limit($rst . ',' . $listRows)->select();
        // 防采集
        $array_bq = array("dl", "div", "dl", "div");
        $array_bq2 = array("span", "font", "b", "strong");
        $array_class = array("cprt", "lnkcpt", "cpit", "lnkcpit", "fjc", "lnkfcj");
        foreach ($list['links'] as $key => $value) {
            //////////get all list tiles for SOED description/////////////////
            //////////get all list tiles for SOED description/////////////////
            $list['links'][$key]["more"] = 0;
            if (empty($value["logo"])) {
                if ($_SESSION['pailie'] == 1) {
                    $list['links'][$key]["sintro"] = String::msubstr($value["intro"], 0, 19);
                } else {
                    $list['links'][$key]["sintro"] = String::msubstr($value["intro"], 0, 208);
                    if ($list['links'][$key]["sintro"] != $value["intro"]) {
                        $list['links'][$key]["sintro"] = String::msubstr($value["intro"], 0, 150);
                        $list['links'][$key]["more"] = 1;
                    }
                }
            } else {
                if ($_SESSION['pailie'] == 1) {
                    if ($list['root_cat_info']['id'] == 5) {
                        $list['links'][$key]["sintro"] = String::msubstr($value["intro"], 0, 20);
                    } else {
                        $list['links'][$key]["sintro"] = String::msubstr($value["intro"], 0, 13);
                    }
                } else {
                    $list['links'][$key]["sintro"] = String::msubstr($value["intro"], 0, 184);
                    if ($list['links'][$key]["sintro"] != $value["intro"]) {
                        $list['links'][$key]["sintro"] = String::msubstr($value["intro"], 0, 132);
                        $list['links'][$key]["more"] = 1;
                    }
                }
            }
            if ($_SESSION['pailie'] == 2) {
                $list['links'][$key]["sintro"] = nl2br($list['links'][$key]["sintro"]);
                $list['links'][$key]["sintro"] = str_replace("<br />
                    <br />", "", $list['links'][$key]["sintro"]); // 特意写成这样的
                $list['links'][$key]["sintro"] = str_replace("<br />
                    <br />", "", $list['links'][$key]["sintro"]); // 特意写成这样的
                $tempary = explode("<br />", $list['links'][$key]["sintro"]);
                if (count($tempary) > 4) {
                    $lastline = String::msubstr($tempary[2], 0, 40);
                    if ($lastline == $tempary[2]) {
                        $lastline .= "…";
                    }
                    $list['links'][$key]["sintro"] = $tempary[0] . "<br />" . $tempary[1] . "<br />" . $lastline;
                    $list['links'][$key]["more"] = 1;
                }
                if (count($tempary) == 3 || (count($tempary) == 4 && $value["more"] == 1)) {
                    $lastline = String::msubstr($tempary[2], 0, 40);
                    $list['links'][$key]["sintro"] = $tempary[0] . "<br />" . $tempary[1] . "<br />" . $lastline;
                    if (count($tempary) == 4) {
                        $list['links'][$key]["sintro"] .= "…";
                    }
                }
                if (count($tempary) == 2) {
                    if ($list['links'][$key]["more"] == 1) {
                        if (strlen($tempary[1]) < strlen($tempary[0])) {
                            $lastline = String::msubstr($tempary[1], 0, 40);
                            $list['links'][$key]["sintro"] = $tempary[0] . "<br />" . $lastline;
                        }
                    } else {
                        $list['links'][$key]["sintro"] = $tempary[0] . "<br />" . $tempary[1];
                    }
                }
                $list['links'][$key]["sintro"] = checkLinkUrl($list['links'][$key]["sintro"]);
            }
            $idx1 = String::randNumber(0, 3);
//            $this->assign("bq", $array_bq[$idx1]);
            $idx2 = String::randNumber(0, 5);
            $rdm = String::uuid();
            $tempstr = "<" . $array_bq2[$idx1] . " class='" . $array_class[$idx2] . "'>欢迎来到另客网，" . $rdm . "新人类，新工具" . $rdm . "</" . $array_bq2[$idx1] . ">";
            $list['links'][$key]["linkTitle"] = $value["title"];
            $list['links'][$key]["title"] = $list['links'][$key]["title"] . $tempstr;
            $list['links'][$key]["sintro"] = $list['links'][$key]["sintro"] . $tempstr;
            //
//            if (!empty($value['mid'])) {
//                $list['links'][$key]['nickname'] = M("Member")->where('id=' . $value['mid'])->getField('nickname');
//            }
            $list['links'][$key]['grade_name'] = $ary_grade[$value['grade']];
        }
        return $list;
    }

}

?>