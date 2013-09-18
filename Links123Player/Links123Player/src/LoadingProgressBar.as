package
{
	import flash.display.Bitmap;
	import flash.display.GradientType;
	import flash.display.Graphics;
	import flash.display.Loader;
	import flash.display.Sprite;
	import flash.display.StageAlign;
	import flash.display.StageScaleMode;
	import flash.events.Event;
	import flash.events.IOErrorEvent;
	import flash.events.ProgressEvent;
	import flash.geom.Matrix;
	import flash.net.URLLoader;
	import flash.net.URLLoaderDataFormat;
	import flash.net.URLRequest;
	import flash.system.LoaderContext;
	import flash.text.TextField;
	import flash.text.TextFormat;
	import flash.utils.ByteArray;
	
	import mx.core.UIComponent;
	import mx.events.FlexEvent;
	import mx.events.RSLEvent;
	import mx.preloaders.DownloadProgressBar;
	
	import spark.components.Image;
	
	public class LoadingProgressBar extends DownloadProgressBar
	{
		private var bg:PLAYER_BACKGROUND;
		private var load:PLAYER_LOADING_MOVIE;
		
		// 总进度（字节描述） 
		private var progressText:TextField; 
		// 总进度（进度条描述） 
		private var progressBarSpritIsAdded:Boolean = false; 
		
		private var _urlrequest:URLRequest;
		private var _urlloader:Loader;
		
		public function LoadingProgressBar() 
		{
			super(); 
			this.addEventListener(Event.ADDED_TO_STAGE,addstagehandle);
		} 
		
		protected function addstagehandle(event:Event):void
		{
			if(stage != null)
			{
				stage.align=StageAlign.TOP_LEFT;  
				stage.scaleMode=StageScaleMode.NO_SCALE;
				this.stage.addEventListener(Event.RESIZE,stageReszieHandle);
			}
		}		
		
		protected function stageReszieHandle(event:Event):void
		{
			initsize();
		}
		
		private function initsize():void
		{
			if(stage != null)
			{
				if(this.stage.stageWidth != 0 && this.stage.stageHeight != 0)
				{
					bg = new PLAYER_BACKGROUND();
					bg.width = this.stage.stageWidth;
					bg.height = this.stage.stageHeight;
					bg.x = 0; 
					bg.y = 0;
					addChild(bg);

					load = new PLAYER_LOADING_MOVIE();
					load.x = stage.stageWidth/2;   
					load.y = stage.stageHeight/2;
					addChild(load);
				}
				
				
				//加载进度条文字
				progressText = new TextField();
				progressText.visible = false;
				var te:TextFormat = new TextFormat();
				te.bold = true;
				progressText.defaultTextFormat = te;
				progressText.textColor = 0x19CCEE;
				progressText.text = "正在加载..."
				progressText.width = 300; 
				progressText.height = 18; 
				progressText.x = stage.stageWidth/2-35;   
				progressText.y = stage.stageHeight/2+30;
				addChild(progressText); 
			}
		}
		
		override public function set preloader(preloader:Sprite):void{ 
			preloader.addEventListener(Event.COMPLETE, handleComplete); 
			preloader.addEventListener(FlexEvent.INIT_PROGRESS, handleInitProgress); 
			preloader.addEventListener(FlexEvent.INIT_COMPLETE, handleInitComplete); 
		} 
		
		//正在下载的进度 
		private function handleProgress(p:ProgressEvent):void { 
			
		} 
		
		// 预加载 
		protected function rslProgressr(r:RSLEvent):void 
		{ 
			if (progressBarSpritIsAdded == false){ 
				progressBarSpritIsAdded = true; 
				addProgressBarSprit(); 
			} 
		} 
 
		private function addProgressBarSprit():void{
			
		}  
		
		private function handleComplete(e:Event):void{ 
			//progressText.text="下载完成!"; 
		} 
		private function handleInitComplete(e:FlexEvent):void{ 
			//progressText.text="初始完成!"; 
			dispatchEvent(new Event(Event.COMPLETE)); 
		} 
		private function handleInitProgress(e:FlexEvent):void{ 
			//progressText.text="初始化..."; 
		} 
	}
}