//---------------------------------------------------  
// 判断闰年  
//---------------------------------------------------  
Date.prototype.isLeapYear = function()   
{   
    return (0===this.getYear()%4&&((this.getYear()%100!==0)||(this.getYear()%400===0)));   
};   
  
//---------------------------------------------------  
// 日期格式化  
// 格式 YYYY/yyyy/YY/yy 表示年份  
// MM/M 月份  
// W/w 星期  
// dd/DD/d/D 日期  
// hh/HH/h/H 时间  
// mm/m 分钟  
// ss/SS/s/S 秒  
//---------------------------------------------------  
Date.prototype.Format = function(formatStr)   
{   
    var str = formatStr;   
    var Week = ['日','一','二','三','四','五','六'];  
  
    str=str.replace(/yyyy|YYYY/,this.getFullYear());   
    str=str.replace(/yy|YY/,(this.getYear() % 100)>9?(this.getYear() % 100).toString():'0' + (this.getYear() % 100));   
  
    var month = this.getMonth()+1;
    str=str.replace(/MM/,month>9?month.toString():'0' + month);   
    str=str.replace(/M/g,month);
  
    str=str.replace(/w|W/g,Week[this.getDay()]);   
  
    str=str.replace(/dd|DD/,this.getDate()>9?this.getDate().toString():'0' + this.getDate());   
    str=str.replace(/d|D/g,this.getDate());   
  
    str=str.replace(/hh|HH/,this.getHours()>9?this.getHours().toString():'0' + this.getHours());   
    str=str.replace(/h|H/g,this.getHours());   
    str=str.replace(/mm/,this.getMinutes()>9?this.getMinutes().toString():'0' + this.getMinutes());   
    str=str.replace(/m/g,this.getMinutes());   
  
    str=str.replace(/ss|SS/,this.getSeconds()>9?this.getSeconds().toString():'0' + this.getSeconds());   
    str=str.replace(/s|S/g,this.getSeconds());   
  
    return str;   
};   
  
//+---------------------------------------------------  
//| 求两个时间的天数差 日期格式为 YYYY-MM-dd   
//+---------------------------------------------------  
function daysBetween(DateOne,DateTwo)  
{   
    var OneMonth = DateOne.substring(5,DateOne.lastIndexOf ('-'));  
    var OneDay = DateOne.substring(DateOne.length,DateOne.lastIndexOf ('-')+1);  
    var OneYear = DateOne.substring(0,DateOne.indexOf ('-'));  
  
    var TwoMonth = DateTwo.substring(5,DateTwo.lastIndexOf ('-'));  
    var TwoDay = DateTwo.substring(DateTwo.length,DateTwo.lastIndexOf ('-')+1);  
    var TwoYear = DateTwo.substring(0,DateTwo.indexOf ('-'));  
  
    var cha=((Date.parse(OneMonth+'/'+OneDay+'/'+OneYear)- Date.parse(TwoMonth+'/'+TwoDay+'/'+TwoYear))/86400000);   
    return Math.abs(cha);  
};  
  
  
//+---------------------------------------------------  
//| 日期计算  
//+---------------------------------------------------  
Date.prototype.DateAdd = function(strInterval, Number) {   
    var dtTmp = this;  
    switch (strInterval) {   
        case 's' :
            return new Date(Date.parse(dtTmp) + (1000 * Number));  
        case 'n' :
            return new Date(Date.parse(dtTmp) + (60000 * Number));  
        case 'h' :
            return new Date(Date.parse(dtTmp) + (3600000 * Number));  
        case 'd' :
            return new Date(Date.parse(dtTmp) + (86400000 * Number));  
        case 'w' :
            return new Date(Date.parse(dtTmp) + ((86400000 * 7) * Number));  
        case 'q' :
            return new Date(dtTmp.getFullYear(), (dtTmp.getMonth()) + Number*3, dtTmp.getDate(), dtTmp.getHours(), dtTmp.getMinutes(), dtTmp.getSeconds());  
        case 'm' :
            return new Date(dtTmp.getFullYear(), (dtTmp.getMonth()) + Number, dtTmp.getDate(), dtTmp.getHours(), dtTmp.getMinutes(), dtTmp.getSeconds());  
        case 'y' :
            return new Date((dtTmp.getFullYear() + Number), dtTmp.getMonth(), dtTmp.getDate(), dtTmp.getHours(), dtTmp.getMinutes(), dtTmp.getSeconds());  
    }  
};  
  
//+---------------------------------------------------  
//| 比较日期差 dtEnd 格式为日期型或者 有效日期格式字符串  
//+---------------------------------------------------  
Date.prototype.DateDiff = function(strInterval, dtEnd) {   
    var dtStart = this;  
    if (typeof dtEnd === 'string' )//如果是字符串转换为日期型  
    {   
        dtEnd = StringToDate(dtEnd);  
    }  
    switch (strInterval) {   
        case 's' :
            return parseInt((dtEnd - dtStart) / 1000);  
        case 'n' :
            return parseInt((dtEnd - dtStart) / 60000);  
        case 'h' :
            return parseInt((dtEnd - dtStart) / 3600000);  
        case 'd' :
            return parseInt((dtEnd - dtStart) / 86400000);  
        case 'w' :
            return parseInt((dtEnd - dtStart) / (86400000 * 7));  
        case 'm' :
            return (dtEnd.getMonth()+1)+((dtEnd.getFullYear()-dtStart.getFullYear())*12) - (dtStart.getMonth()+1);  
        case 'y' :
            return dtEnd.getFullYear() - dtStart.getFullYear();  
    }  
};  

Date.parses=function(str){
    return str!==null && str.length > 0 ? Date.parse(str)/1000-3600*8 : '';
};

Date.parsee=function(str){
    return str!==null && str.length > 0 ? Date.parse(str)/1000 + (3600*16 -1) : '';
};

Date.getDateTime = function() {
    return new Date().Format('yyyy-MM-dd HH:mm:ss'); 
};

Date.getToday = function() {
    return new Date().Format('yyyy-MM-dd');
};

Date.getServerDateTime = function() {
    var serverDateTime=null;
    $.ajaxSetup({
        async : false
    });
    $.getJSON("public?f=getDateTime", function(json) {
        serverDateTime = json.dateTime;
    });
    $.ajaxSetup({
        async : true
    });
    if (serverDateTime === undefined) {
        serverDateTime = Date.getDateTime();
    }
    return serverDateTime;
};
