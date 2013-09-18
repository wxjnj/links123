package com.links123.player.components
{

	
	import spark.components.Button;
	import spark.components.mediaClasses.MuteButton;
	import spark.components.supportClasses.SkinnableComponent;
	
	public class HorizontalVolumeBar extends SkinnableComponent
	{
		[SkinPart(required="false")]
		public var muteButton:MuteButton;
		[SkinPart(required="false")]
		public var thumb:Button;
		
		[SkinPart(required="false")]
		public var track:Button; 
		
		public function HorizontalVolumeBar()
		{
			super();
		}
	}
}