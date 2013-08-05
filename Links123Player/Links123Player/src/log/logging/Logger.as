// ActionScript file

import mx.logging.ILogger;
import mx.utils.ObjectUtil;
import com.exsky.logging.LogManager;
private var _logger:ILogger;

protected function get Logger():ILogger{
	if(_logger==null)
	{
		var name:String=mx.utils.ObjectUtil.getClassInfo(this).name;
		if(name){
			name=name.replace("::",".");
		}
		else{
			name="Common";
		}
		_logger=LogManager.getLogger(name);
	}
	return _logger;
} 