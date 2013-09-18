package com.links123.player.components
{
	
	import com.links123.player.Mode.WordClipsVO;
	
	import spark.components.Button;
	
	
	
	public class LabelButton extends Button
	{
		
		private var _wordtitle:String;
		public function LabelButton(wordstr:String)
		{
			_wordtitle = wordstr;
			super();
		}
		
//		/**
//		 * 保存 按钮数据
//		 */
//		public function get wordcli():WordClipsVO
//		{
//			return _wordcli;
//		}

		public function get wordtitle():String
		{
			return _wordtitle;
		}

		override protected function getCurrentSkinState():String
		{
			return super.getCurrentSkinState();
		} 
		
		override protected function partAdded(partName:String, instance:Object) : void
		{
			super.partAdded(partName, instance);
			if(instance == labelDisplay)
			{
				//赋值
				this.label = _wordtitle;
			}
		}
		
		override protected function partRemoved(partName:String, instance:Object) : void
		{
			super.partRemoved(partName, instance);
		}
		
	}
}