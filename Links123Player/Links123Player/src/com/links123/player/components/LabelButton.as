package com.links123.player.components
{
	
	import com.links123.player.Mode.WordClipsVO;
	
	import spark.components.Button;
	
	
	
	public class LabelButton extends Button
	{
		
		private var _wordcli:WordClipsVO;
		public function LabelButton(wordclip:WordClipsVO)
		{
			_wordcli = wordclip;
			super();
		}
		
		/**
		 * 保存 按钮数据
		 */
		public function get wordcli():WordClipsVO
		{
			return _wordcli;
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
				this.label = _wordcli.word;
			}
		}
		
		override protected function partRemoved(partName:String, instance:Object) : void
		{
			super.partRemoved(partName, instance);
		}
		
	}
}