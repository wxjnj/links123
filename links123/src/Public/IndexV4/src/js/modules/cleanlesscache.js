//调试用 - 清空 less cache
if (typeof localStorage != "undefined") 
    for(i in localStorage) 
        if (i.indexOf('.less') != -1) delete localStorage[i];


var less = less || {};less.env = 'development';
localStorage.clear();