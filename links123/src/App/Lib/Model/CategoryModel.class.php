<?php
/**
 * @package Model
 * @name CategoryModel.class.php
 * @author Frank UPDATE 2013-08-17
 */
class CategoryModel extends CommonModel {

	/**
	 * @desc 验证条件
	 * @author Frank UPDATE 2013-08-17
	 * @return void
	 */
    public $_validate = array(
        array('cat_name', 'require', '目录名称必须'),
    );

    /**
     * @desc 获取目录链接列表
     * @author Frank UPDATE　2013-01-17
     * @param int $lan
     * @param int $cid
     * @param string $grade
     * @param srting $sort
     * @param int $page
     * @return array
     */
    public function getIndexCategoryLinksList($lan, $cid, $grade, $sort,$page = false) {
        import("@.ORG.String");
        $cid = $cid ? : $this->where('status=1 and level=1')->min('id');
        $list['cat_info'] = $this->where("id='%d' and status=1", $cid)->find();
        
        //获取根分类信息
        $prtId = &$list['cat_info']['prt_id']; 
        $list['root_cat_info'] = $prtId == 0 ? $list['cat_info'] : $this->where("id='%d' and status=1", $prtId)->find();
        
        //获取默认等级数组
        $rootCatInfoId = &$list['root_cat_info']['id'];
        $gradeArr = getGradeArr($rootCatInfoId);
        $ary_grade = $gradeArr['aryGrade'];
        $list['grades'] = $gradeArr['grades'];
        
        //当前顶级分类下的分类列表
        $list['cat_list'] = $this->where("prt_id='%d' and flag='%d' and status=1", $cid, $lan)->order("sort")->select();

        //当前顶级分类下的图片
        $list['cat_pic'] = D("CatPic")->where("rid='%d'", $rootCatInfoId)->order('sort')->select();
        $condition[$list['cat_info']['prt_id'] == 0 ? 'category.prt_id' : 'links.category'] = $list['cat_info']['id'];
        $condition['links.language'] = $lan;
        $condition['links.status'] = 1;
        intval($grade) > 0 && $condition['links.grade'] = array('like', '%' . $grade . '%');
        empty($sort) && $sort = "category.sort asc,links.sort asc";
        
        $dftPailie = $_SESSION['pailie'];
        
        if (empty($dftPailie)) {
            if (isset($_SESSION[C('MEMBER_AUTH_KEY')])) {
            	$dftPailie = M("Member")->where('id=' . $_SESSION[C('MEMBER_AUTH_KEY')])->getField('pailie');
            } else {
            	$dftPailie = M("Variable")->where("vname='pailie'")->getField("value_int");
            }
            $_SESSION['pailie'] = $dftPailie;
        }
        
        $listRows = $dftPailie == 1 ? 20 : 11;
        
        $pg = $page !== false ? $page : isset($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1;
        intval($pg) <= 0 && $pg = 1;
        $rst = ($pg - 1) * $listRows;
        
        $linksViewModel = new LinksFntViewModel();
        //分页
        $count = $linksViewModel->where($condition)->count('links.id');
        if ($count > 0) {
            import("@.ORG.Page");
            $p = new Page($count, $listRows);
            $list['page'] = $p->show_ajax_js();
        }
        
        $list['links'] = $linksViewModel->getLists($condition, $sort, $rst, $listRows, $rootCatInfoId, $ary_grade);
        return $list;
    }

}

?>