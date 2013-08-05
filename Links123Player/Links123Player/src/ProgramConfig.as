package 
{
	import com.adobe.serialization.json.JSON;
	import com.exsky.logging.LogMessage;
	import com.exsky.utils.ConfigBase;
	
	import flash.events.IEventDispatcher;
	import flash.external.ExternalInterface;
	import flash.utils.unescapeMultiByte;
	
	import mx.core.FlexGlobals;
	import mx.events.StyleEvent;
	
	import spark.components.Application;

	public class ProgramConfig extends ConfigBase
	{
		private static const CONFIGITEMS:Array=[
			["skin","Skin","s",""],["logLevel","LogLevel","f","parsingLogLevel"],
			["logging","LogEnabled","b",""],["host","Host","s",""],["url","URL","s",""]
		];
		private static var _config:ProgramConfig;
		private var param:Object;
		public var LogEnabled:Boolean=false;
		public var LogLevel:int=0;//error level
		public var LogFilter:String="*";
		public var Host:String="";
		public var URL:String = "";
		
		public var application:Application;
		public function ProgramConfig()
		{
			this.application=mx.core.FlexGlobals.topLevelApplication as Application;
			if(this.application!=null){
				this.param=application.parameters; 
				super(param);
			}
		} 	
		
		public function set BackgroundColor(value:String):void{
			application.setStyle("backgroundColor",value);
		}
		
		public static function get config():ProgramConfig{
			if(_config==null){
				_config=new ProgramConfig();
			}
			return _config;
		}
		
		public function get parameters():Object{
			return this.param;
		}
		
		override protected function get configArray():Array{
			return CONFIGITEMS;
		}
		
		private function parsingLogLevel(level:String):int{
			return LogMessage.GetLevelValue(level.toLocaleLowerCase());
		}
		
		private var _skinLoading:Boolean=false;
		public function get skinLoading():Boolean{
			return _skinLoading;
		}
		public function loadSkin(skin:Object,cb:Function=null):void{
			if(!skin)
				return;
			var s:String=skin.toString();
			if(s.indexOf(".swf")<0)
				s+=".swf";
			if(!isAbsolute(s)){
				s="skins/"+s;
			}
			
			_skinLoading=true;
			var req:IEventDispatcher=this.application.styleManager.loadStyleDeclarations2(s);
			req.addEventListener(StyleEvent.COMPLETE,function(e:StyleEvent):void{
				_skinLoading=false;
				cb=cb||skinLoadComplete;
				if(cb!=null){
					cb();
				}
			});
			req.addEventListener(StyleEvent.ERROR,function(e:StyleEvent):void{
				_skinLoading=false;
				cb=cb||skinLoadComplete;
				if(cb!=null){
					cb();
				}
			});
		}
		public var skinLoadComplete:Function=null;
		public function set Skin(skin:String):void{
			loadSkin(skin);
		}
		override protected function setProperty(property:String,value:Object):void{
			this[property]=value;
		}
		override protected function callFun(name:String,value:Object):Object{
			var ret:*=this[name](value);
			if(ret)
				return ret;
			return null;
		}
		private function isAbsolute(path:String):Boolean{
			if(path.indexOf("://")>0)
				return true;
			if(path.indexOf("/")>=0)
				return true;
			return false;
		}
		
		private static const urlRegex:String="http(s)?://[^/:]+(:[0-9]+)*/";
		public static function getUrlDomain(url:String):String{
			var reg:RegExp=new RegExp(urlRegex,"i");
			if(reg.test(url)){
				return (reg.exec(url) as Array)[0];
			}
			return "";
		}
	}
}