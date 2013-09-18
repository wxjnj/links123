package com.links123.player.utils
{
	import flash.display.Stage;
	import flash.events.Event;
	import flash.events.FocusEvent;
	import flash.system.Security;
	import flash.system.SecurityPanel;
	
	import mx.core.FlexGlobals;
	
	/**
	 * 
	 **/
	public class SecuritySettings
	{
		/**
		 * 
		 **/
		public static function show(param:String, onClosed:Function):void
		{
			var stage:Stage = FlexGlobals.topLevelApplication.stage;
			
			var delegate:Function = function(event:FocusEvent):void {
				stage.removeEventListener(FocusEvent.FOCUS_IN, delegate);
				onClosed();
			}
			
			stage.addEventListener(FocusEvent.FOCUS_IN, delegate);
			Security.showSettings(param);
		}
	}
}