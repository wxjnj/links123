package com.links123.player.components
{
	import com.exsky.controls.FlexMovieClip;
	
	import flash.events.MouseEvent;
	
	import spark.components.Button;
	import spark.components.Label;
	import spark.components.supportClasses.SkinnableComponent;
	
	public class IntroWordPanel extends SkinnableComponent
	{
//		//单词
//		[SkinPart(required="false")]
//		public var word:Label;
//		
//		//词性
//		[SkinPart(required="false")]
//		public var type:Label;
//		
//		//举例
//		[SkinPart(required="false")]
//		public var example:Label;
//		
//		//单词翻译
//		[SkinPart(required="false")]
//		public var intro:Label;
		
		//继续按钮
		[SkinPart(required="false")]
		public var goon:FlexMovieClip;
		
		//语音
		[SkinPart(required="false")]
		public var speak:Button;
		
		private var _wordstr:String;
		private var _typestr:String;
		private var _examplestr:String;
		private var _introstr:String;
		public function IntroWordPanel()
		{
			super();
		}
		
		[Bindable]
		public function get introstr():String
		{
			return _introstr;
		}

		public function set introstr(value:String):void
		{
			_introstr = value;
		}

		[Bindable]
		public function get examplestr():String
		{
			return _examplestr;
		}

		public function set examplestr(value:String):void
		{
			_examplestr = value;
		}

		[Bindable]
		public function get typestr():String
		{
			return _typestr;
		}

		public function set typestr(value:String):void
		{
			_typestr = value;
		}

		[Bindable]
		public function get wordstr():String
		{
			return _wordstr;
		}

		public function set wordstr(value:String):void
		{
			_wordstr = value;
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