import Versioncom.Version;

import com.adobe.serialization.json.JSON;
import com.exsky.DataLoader;
import com.exsky.logging.LogManager;
import com.links123.player.Mode.PlayerVO;
import com.links123.player.event.EventSprite;
import com.links123.player.event.LoadDataEvent;

import flash.events.ContextMenuEvent;
import flash.events.DataEvent;
import flash.events.MouseEvent;
import flash.external.ExternalInterface;
import flash.system.Security;
import flash.ui.ContextMenu;
import flash.ui.ContextMenuItem;

import mx.events.FlexEvent;
import mx.logging.Log;

import org.osmf.layout.ScaleMode;

/**
 * 程序从页面取得的配置 
 */
private var config:ProgramConfig;

/**
 * 两个状态一个loggingForm 另外一个player 
 */
private var loggingFormState:String="loggingForm";

/**
 * 记录状态变量 
 */
private var _OldState:String;

/**
 * 程序名字 日志中使用 
 */
private var programname:String = "Links123Player";

/**
 * 配置版本号 
 */
public static const Ver:Version=new Version(0,0,0,1);

/**
 * 取得程序页面配置 
 * @return 
 * 
 */
public function get Config():ProgramConfig{
	return config;
}

/**
 * 程序初始化 
 * @param event
 * 
 */
protected function application1_creationCompleteHandler(event:FlexEvent):void
{	
	Security.allowDomain("*");
	Security.allowInsecureDomain("*"); 
	InitProgram();
	initplayer();
	ExternalInterface.addCallback("PlayNew",playotherurl);
}

/**
 * 重新播放一个地址 
 * @param url
 * 
 */
private function playotherurl(url:String):void
{
	if(playercon != null)
	{
		playercon.source = url;
	}
	Logger.info("this player new url is:{0}",url);
}

/**
 * 初始化播放视频 
 * 
 */
private function initplayer():void
{
	//初始化播放器
	if(config != null && playercon != null && config.URL != "")
	{
		playercon.source = config.URL;
	}
	Logger.info("this player url is:{0}",playercon.source);
	//加载数据
	loadData(config.Host);
}


/**
 * 加载本视频数据 
 * 
 */
private function loadData(host:String):void
{
	Logger.info("this player host is:{0}",host);
	var dat:DataLoader = new DataLoader(host);
	dat.Load(null,function callback(data:Object):void
	{
		Logger.info("sucess get video data from server!");
		PlayerVO.getInstance().init(data);
		if(playercon != null)
		{
			Logger.debug("dispatching DataEvent.UPLOAD_COMPLETE_DATA event!");
			playercon.dispatchEvent(new LoadDataEvent(LoadDataEvent.LOAD_PLAYER_DATA_COMPLETE));
		}
	},function onError(e:Error):void
	{
		Logger.info("filed get data from server!");
	});
}

/**
 * 程序入口 
 * 
 */
private function InitProgram():void
{
	//添加页面配置
	config=ProgramConfig.config;
	//添加日志
	logControl(config.LogEnabled);
	//初始化鼠标右键
	initCustomContextMenu();
}

/**
 * 添加日志选项 是否显示
 * 
 */
private function logControl(islog:Boolean):void
{
	LogManager.LogEnabled=islog;
	this.initLog(programname);
}

/**
 * 创建鼠标右键值版本+日志
 */
private function initCustomContextMenu():void{
	this.contextMenu.builtInItems.print=false;
	var item:ContextMenuItem=new ContextMenuItem("Links123Player "+Ver.toString(),false,false);
	this.contextMenu.customItems.push(item);
	if(config.LogEnabled){
		item= new ContextMenuItem("切换日志");
		this.contextMenu.customItems.push(item);
		item.addEventListener(ContextMenuEvent.MENU_ITEM_SELECT,toggleLoggingForm);
	}
}

/**
 *  点击右键 日志选项 显示日志
 *  
 */
private function toggleLoggingForm(event:ContextMenuEvent):void {
	if(this.currentState!=loggingFormState){
		_OldState=this.currentState;
		this.currentState=loggingFormState;
		this.OnToggleToLogingForm(true);
	}
	else{
		this.OnToggleToLogingForm(false);
		this.currentState=_OldState;
	}
	Logger.info("start initializing,program version:{0}",Ver);
}