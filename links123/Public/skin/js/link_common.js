if (!window.Links) {
	window.Links = new Object();
}
Links.Common = {
	focus: null,
	init: function() {
		Links.Common.enableDirect();
		Links.Common.enableSearch();
	},
	enableDirect: function() {
		var $directInput = $("#direct_input");
		var oldValue = $directInput.val();
		$directInput.mouseover(function(){
			$(this).select();
		});
		$directInput.mouseout(function(){
			$(this).blur();
		});
		$directInput.click(function(){
			$(this).val('');
		});
		$directInput.blur(function(){
			$(this).val(oldValue);
		});
	},
	enableSearch: function() {
		var $searchInput = $("#search_input");
		var $searchCategory = $("#search_category");
		var $search = $("#search");
		var $btnSearch = $("#btn_search");
		var $searchForm = $("#search_form");
		/**
		 * 统计输入框中输入文字的宽度
		 */
		var countInputWidth = function(text, font) {
	        var currentObj = $('<pre>').hide().appendTo(document.body);
	        $(currentObj).html(text).css('font', font);
	        var width = parseInt(currentObj.width());
	        currentObj.remove();
	        return width;
	    }
		/**
		 * 糖葫芦位置动态调整
		*/
	    var changeCategoryPos = function() {
	    	var inputWidth = countInputWidth($searchInput.val(),'微软雅黑,14px,normal');
			var newLeft = inputWidth+60;
			if(newLeft < 100) {
				$searchCategory.css("left",newLeft+'px');
				$searchCategory.css("top",'1px');
			} else {
				$searchCategory.css("left",'60px');
				$searchCategory.css("top",'28px');
			}
	    }
		/**
		 * 搜索分类切换
		 */
		var changeCategory = function() {
			/*	显示当前分类下的title	*/
			var cateName = $(this).html();
			var expr = "[cate="+cateName+"]";
			$("#search .title").addClass("none");
			$("#search").find(expr).removeClass("none");
			/*	高亮当前分类	*/
			$(this).closest("div").find("dl a").removeClass("red");
			$(this).addClass("red");
		}
		/**
		 * 搜索
		 */
		var doSearch = function() {
			if($(this).is("a")) {
				/*	高亮		*/
				$(this).closest("div.title").find("dl a").removeClass("red");
				$(this).closest("div.title").find("dl a").attr("on",0);
				$(this).addClass("red");
				$(this).attr("on",1);
			}
			$searchForm.submit();
			return false;
		}
		/**
		 * 搜索提交
		 */
		var searchCommit = function() {
			if(ACTION == 'Search') {
				var frame_url = $search.children(".title").not(".none").find("a[on=1]").attr("frame_url");
				var searchValue = $.trim($searchInput.val());
				frame_url = "http://" + frame_url.replace("keyword",searchValue);
				$("#main",parent.document.body).attr("src",frame_url);
				return false;
			} else {
				var searchHref = $search.children(".title").not(".none").find("a[on=1]").attr("href");
				var searchValue = $.trim($searchInput.val());
				if(searchValue != "") {
					searchHref = searchHref + searchValue + ".html";
				}
				$searchForm.attr("action",searchHref);
				return true;
			}
		}
		/**
		 *输入的时候焦点切换到搜索框
		 */
		$(window).keydown(function(event){
			if(event.keyCode>=48 && event.keyCode<=90) {
				if(Links.Common.focus != 'search') {
					Links.Common.focus = 'search';
					$searchInput.focus();
				}
				
			}
		});
		$searchInput.blur(function(){
			Links.Common.focus = null;
		});
		
		//糖葫芦动态展示
		$searchInput.mouseover(function(){ $searchCategory.show(); });
		$searchInput.mouseout(function(){ $searchCategory.hide(); });
		$searchInput.focus(function(){ $searchCategory.show(); });
		$searchInput.blur(function(){ $searchCategory.hide(); });
		$searchCategory.mouseover(function(){ $searchCategory.show(); });
		$searchCategory.mouseout(function(){ $searchCategory.hide(); });
		
		$searchInput.keyup(changeCategoryPos);							//输入时调整糖葫芦位置
		changeCategoryPos();											//初始化调整糖葫芦位置
		$searchCategory.find("dl a").click(changeCategory);				//切换分类
		$search.find(".title dl a").click(doSearch);					//点击链接搜索
		$btnSearch.click(doSearch);										//点击按钮搜索
		$searchForm.submit(searchCommit);								//搜索提交
		
		
	}
};
$(function($){
	Links.Common.init();
});