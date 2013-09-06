<?php

class SliderWidget extends Action{
	public function run($config){
		//这个数组应该是从数据层取出, 这里仅作示范,所以手动实例化数组
		$dataProvider =array(
			array(
				"src"=>"__PUBLIC__/Index/imgs/meinv.png",
				"thumb" =>"__PUBLIC__/Index/imgs/meinv.png"  ,
				"link" =>"javascript:;",
				"alt" =>"美女1",
				"text"=>"美女1", 
			),
			array(
				"src"=>"__PUBLIC__/Index/imgs/meinv2.jpg",
				"thumb" =>"__PUBLIC__/Index/imgs/meinv2.jpg"  ,
				"link"=>"javascript:;",
				"alt" =>"美女2",
				"text"=>"美女2", 
			),
			array(
				"src"=>"__PUBLIC__/Index/imgs/meinv3.jpg",
				"thumb" =>"__PUBLIC__/Index/imgs/meinv3.jpg"  ,
				"link"=>"javascript:;",
				"alt" =>"美女3",
				"text"=>"美女3", 
			),
			array(
				"src"=>"__PUBLIC__/Index/imgs/meinv4.png",
				"thumb" =>"__PUBLIC__/Index/imgs/meinv4.png"  ,
				"link"=>"javascript:;",
				"alt" =>"美女4",
				"text"=>"美女4", 
			),
		);


		$this->assign('dataProvider',$dataProvider);
		$this->assign('config',$config);

        $this->display(dirname(__FILE__).'/views/slider.html');
	}

}