package com.links123.player.components
{
	
	import com.exsky.controls.FlexMovieClip;
	
	import spark.components.Button;
	import spark.components.Group;
	import spark.components.Label;
	import spark.components.supportClasses.SkinnableComponent;
	
	
	/**
	 * 得分面板 
	 * @author Administrator
	 * 
	 */	
	public class ScorePanel extends SkinnableComponent
	{
		[SkinPart(required="false")]
		public var score:Label;
		
		[SkinPart(required="false")]
		public var statelabel:Label;
		
		//录音对比
		[SkinPart(required="false")]
		public var bitrecord:Button;
		
		//重新跟读
		[SkinPart(required="false")]
		public var repeatrecord:Button;
		
		//继续
		[SkinPart(required="false")]
		public var goon:Button;
		
		[SkinPart(required="false")]
		public var sucessshow:Group;
		
		[SkinPart(required="false")]
		public var failshow:FlexMovieClip
		
		/**
		 * 实际得分 
		 */		
		private var _sco:String;
		private var _state:String;
		public function ScorePanel()
		{
			super();
		}
		
		/**
		 * 评分状态 
		 */
		[Bindable]
		public function get state():String
		{
			return _state;
		}

		/**
		 * @private
		 */
		public function set state(value:String):void
		{
			_state = value;
		}

		[Bindable]
		public function get sco():String
		{
			return _sco;
		}

		public function set sco(value:String):void
		{
			if(sucessshow != null && failshow != null)
			{
				if(Number(value) != 0)
				{
					sucessshow.visible = true;
					failshow.visible = false;
				}else
				{
					sucessshow.visible = false;
					failshow.visible = true;
				}
			}
			_sco = value;
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