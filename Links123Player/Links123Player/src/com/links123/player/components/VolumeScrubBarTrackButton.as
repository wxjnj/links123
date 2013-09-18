package com.links123.player.components
{
	
	import com.exsky.controls.FlexMovieClip;
	
	import mx.containers.Canvas;
	
	import spark.components.Button;
	import spark.components.Group;
	
	
	
	public class VolumeScrubBarTrackButton extends Button
	{
		[SkinPart(required="true")]
		public var track2:Group;
		
		
		[SkinPart(required="true")]
		public var trackcon:Canvas;
		
		public function VolumeScrubBarTrackButton()
		{
			super();
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