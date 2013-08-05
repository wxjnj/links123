// code behind file for SoPlayer.mxml
import com.exsky.logging.LogCategoriesChangedEvent;
import com.exsky.logging.LogManager;
import com.exsky.logging.TextAreaTarget;

import flash.events.MouseEvent;

import mx.collections.ArrayCollection;
import mx.collections.ArrayList;
import mx.logging.*;
import mx.logging.targets.*;


private var myLogger : ILogger;

public function get Logger():ILogger{
	return myLogger;
}


private var logTarget:TextAreaTarget;


public function printLog(level:Number):void
{
	if(level ==2)
		myLogger.debug("This is debug click");
	if(level == 4)
		myLogger.info("This is info click");
	if(level == 6)
		myLogger.warn("This is warn click");
	if(level == 8)
		myLogger.error("This is error click");
	if(level ==1000)
		myLogger.fatal("This is fatal click");
}
private function initLog(name:String):void{	
	myLogger = LogManager.getLogger(name);
	if(!config.LogEnabled){
		return;
	}
	
	// Create a target.
	logTarget = new TextAreaTarget(loggingTextArea);
	// Log only messages for the classes in the mx.rpc.* and
	// mx.messaging packages.
	logTarget.filters=[config.LogFilter];
	// Log all log levels.
	logTarget.level = config.LogLevel;
	// Add date, time, category, and log level to the output.
	logTarget.includeDate = true;
	logTarget.includeTime = true;
	logTarget.includeCategory = true;
	logTarget.includeLevel = true;
	// Begin logging.
	Log.addTarget(logTarget);
	
	
}
private var loggingformInited:Boolean=false;
private function OnToggleToLogingForm(flag:Boolean):void{
	if(!ProgramConfig.config.LogEnabled)
		return;
	if(flag){
		if(!loggingformInited){
			initLoggingForm();
			loggingformInited=true;
		}
		logTarget.Show();
	}
	else{
		logTarget.Hide();
	}
}

/////////////////////////////////loggingForm init////////////////////////////////////////////
[bindable]
private var logLevelArray:ArrayList=new ArrayList(["all","debug","info","warning","error","fatal"]);
[bindable]
private var logCategoriesArray:ArrayCollection;
private function initLoggingForm():void{
	this.logLevelCmb.dataProvider=logLevelArray;
	logCategoriesArray=logTarget.Categorys;
	this.logLevelCmb.selectedIndex=0;
	this.logCategoryCmb.dataProvider=logCategoriesArray;
	this.logCategoryCmb.selectedIndex=0;
	logTarget.addEventListener(LogCategoriesChangedEvent.CATEGORIES_CHANGED,logCategoryChanged);
	this.logRefreshBtn.addEventListener(MouseEvent.CLICK,logRefreshBtnClick);
	this.logClearBtn.addEventListener(MouseEvent.CLICK,logClearBtnClick);
	this.logBackBtn.addEventListener(MouseEvent.CLICK,logBackBtnClick);
}

private function logCategoryChanged(event:LogCategoriesChangedEvent):void{
	logCategoriesArray=event.Categories;
	Logger.info("{0},categories:{1}",event.type,event.Categories);
}

private function logRefreshBtnClick(event:MouseEvent):void{
	logTarget.ShowingCategory=this.logCategoryCmb.selectedItem as String;
	logTarget.ShowingLevel=this.logLevelCmb.selectedItem as String;
	logTarget.StrictMode=this.logLevelStrictModeCbx.selected;
	logTarget.Refresh();
}
private function logClearBtnClick(event:MouseEvent):void{
	logTarget.clear(true);
}
private function logBackBtnClick(event:MouseEvent):void{
	this.toggleLoggingForm(null);
}







