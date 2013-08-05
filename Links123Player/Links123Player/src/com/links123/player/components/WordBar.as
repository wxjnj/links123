package com.links123.player.components
{
	
	import com.links123.player.Mode.ClipsVO;
	import com.links123.player.event.WordBarStateChangeEvent;
	
	import spark.components.Group;
	import spark.components.supportClasses.SkinnableComponent;
	
	
	
public class WordBar extends SkinnableComponent
	{
		include "../../../../log/Logging/Logger.as";
		
		[SkinPart(required="true")]
		public var wordbar:Group;//noplay,playing,played
		private var _currentstate:int;
		private var _isPlayed:Boolean = false;
		private var _curclip:ClipsVO;
		
		public function WordBar(clip:ClipsVO)
		{
			_curclip = clip;
			super(); 
		}
		
		/**
		 * 是否已经播完 
		 * @return 
		 * 
		 */		
		public function get isPlayed():Boolean
		{
			return _isPlayed;
		}
		
		public function set isPlayed(value:Boolean):void
		{
			_isPlayed = value;
		}
		
		/**
		 * 当前分片数据 
		 */
		public function get curclip():ClipsVO
		{
			return _curclip;
		}
		
		/**
		 * 当前皮肤状态 
		 * @return 
		 * 
		 */		
		public function get currentstate():int
		{
			return _currentstate;
		}
		public function set currentstate(value:int):void
		{
			_currentstate = value;
			var skin:String;
			switch(value)
			{
				case 1:
					skin = "noplay";
					break;
				case 2:
					skin = "playing";
					break;
				case 3:
					skin = "played";
					break;
			}
			this.dispatchEvent(new WordBarStateChangeEvent(WordBarStateChangeEvent.WORDBAR_STATE_CHANGE,skin));
		}
		
		override protected function getCurrentSkinState():String
		{
			return super.getCurrentSkinState();
		} 
		
		override protected function partAdded(partName:String, instance:Object) : void
		{
			super.partAdded(partName, instance);
		}
		
		override protected function partRemoved(partName:String, instance:Object) : void
		{
			super.partRemoved(partName, instance);
		}
		
	}
}