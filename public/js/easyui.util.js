$.fn.panel.defaults.loadingMessage = '加载中....';
$.fn.datagrid.defaults.loadMsg = '加载中....';
var easyuiErrorFunction = function(XMLHttpRequest) {
    $.messager.progress('close');
    $.messager.alert('错误', XMLHttpRequest.responseText);
};
$.fn.datagrid.defaults.onLoadError = easyuiErrorFunction;
$.fn.treegrid.defaults.onLoadError = easyuiErrorFunction;
$.fn.tree.defaults.onLoadError = easyuiErrorFunction;
$.fn.combogrid.defaults.onLoadError = easyuiErrorFunction;
$.fn.combobox.defaults.onLoadError = easyuiErrorFunction;
$.fn.form.defaults.onLoadError = easyuiErrorFunction;

var createGridHeaderContextMenu = function(e, field) {
    e.preventDefault();
    var grid = $(this);/* grid本身 */
    var headerContextMenu = this.headerContextMenu;/* grid上的列头菜单对象 */
    if (!headerContextMenu) {
        var tmenu = $('<div style="width:100px;"></div>').appendTo('body');
        var asc = $('<div iconCls="icon-asc" field="asc">升序</div>').appendTo(tmenu);
        var desc = $('<div iconCls="icon-desc" field="desc">降序</div>').appendTo(tmenu);
        var filedHTML = $('<div iconCls="icon-columns"></div>');
        var span = $('<span>显示列/隐藏列</span>');
        var spdiv = $('<div></div>');
        var fields = grid.datagrid('getColumnFields');
        for (var i = 0; i < fields.length; i++) {
            var fildOption = grid.datagrid('getColumnOption', fields[i]);
            if (!fildOption.hidden) {
                $('<div iconCls="icon-checked" field="' + fields[i] + '"/>').html(fildOption.title).appendTo(spdiv);
            } else {
                $('<div iconCls="icon-unchecked" field="' + fields[i] + '"/>').html(fildOption.title).appendTo(spdiv);
            }
        }
        span.appendTo(filedHTML);
        spdiv.appendTo(filedHTML);
        filedHTML.appendTo(tmenu);
        headerContextMenu = this.headerContextMenu = tmenu.menu({
            onClick: function(item) {
                var f = $(this).attr('field');
                var fieldProperty = $(item.target).attr('field');
                if (item.iconCls === 'icon-checked') {
                    grid.datagrid('hideColumn', fieldProperty);
                    $(this).menu('setIcon', {
                        target: item.target,
                        iconCls: 'icon-unchecked'
                    });
                }
                if (item.iconCls === 'icon-unchecked') {
                    grid.datagrid('showColumn', fieldProperty);
                    $(this).menu('setIcon', {
                        target: item.target,
                        iconCls: 'icon-checked'
                    });
                }
                if (item.iconCls === 'icon-asc') {
                    var options = grid.datagrid('options');
                    options.sortName = f;
                    options.sortOrder = fieldProperty;
                    grid.datagrid('reload');
                }
                if (item.iconCls === 'icon-desc') {
                    var options = grid.datagrid('options');
                    options.sortName = f;
                    options.sortOrder = fieldProperty;
                    grid.datagrid('reload');
                }
            }
        });
    }
    headerContextMenu.attr('field', field);
    headerContextMenu.menu('show', {
        left: e.pageX,
        top: e.pageY
    });
    var fd = grid.datagrid('getColumnOption', field);
    if (!fd.sortable) {
        var itemAsc = headerContextMenu.menu('findItem', '升序');
        headerContextMenu.menu('disableItem', itemAsc.target);
        var itemDesc = headerContextMenu.menu('findItem', '降序');
        headerContextMenu.menu('disableItem', itemDesc.target);
    }
};
$.fn.datagrid.defaults.onHeaderContextMenu = createGridHeaderContextMenu;
$.fn.treegrid.defaults.onHeaderContextMenu = createGridHeaderContextMenu;
function isCardID(sId) {
    var iSum = 0;
    var info = "";
    var aCity = {11: "北京", 12: "天津", 13: "河北", 14: "山西", 15: "内蒙古", 21: "辽宁", 22: "吉林", 23: "黑龙江", 31: "上海", 32: "江苏", 33: "浙江", 34: "安徽", 35: "福建", 36: "江西", 37: "山东", 41: "河南", 42: "湖北", 43: "湖南", 44: "广东", 45: "广西", 46: "海南", 50: "重庆", 51: "四川", 52: "贵州", 53: "云南", 54: "西藏", 61: "陕西", 62: "甘肃", 63: "青海", 64: "宁夏", 65: "新疆", 71: "台湾", 81: "香港", 82: "澳门", 91: "国外"};
    if (!/^\d{17}(\d|x)$/i.test(sId)) {
        info = "请输入的18位身份证号";
        return info;
    }
    sId = sId.replace(/x$/i, "a");
    var ac = aCity[parseInt(sId.substr(0, 2))];
    if (typeof (ac) === "undefined" || ac === null) {
        info = "你的身份证地区非法";
        return info;
    }
    var sBirthday = sId.substr(6, 4) + "-" + Number(sId.substr(10, 2)) + "-" + Number(sId.substr(12, 2));
    var d = new Date(sBirthday.replace(/-/g, "/"));
    if (sBirthday !== (d.getFullYear() + "-" + (d.getMonth() + 1) + "-" + d.getDate())) {
        info = "身份证上的出生日期非法";
        return info;
    }
    for (var i = 17; i >= 0; i --)
        iSum += (Math.pow(2, i) % 11) * parseInt(sId.charAt(17 - i), 11);
    if (iSum % 11 !== 1) {
        info = "你输入的身份证号非法";
        return info;
    }
    return info;
}
function isValidBusCode(busCode) {
    var info = "";
    var ret = false;
    if (busCode.length === 15) {
        var s = [];
        var p = [];
        var a = [];
        var m = 10;
        p[0] = m;
        for (var i = 0; i < busCode.length; i++) {
            a[i] = parseInt(busCode.substring(i, i + 1), m);
            s[i] = (p[i] % (m + 1)) + a[i];
            if (0 === s[i] % m) {
                p[i + 1] = 10 * 2;
            } else {
                p[i + 1] = (s[i] % m) * 2;
            }
        }
        if (1 === (s[14] % m)) {
            //营业执照编号正确!
            ret = true;
        } else {
            //营业执照编号错误!
            ret = false;
            info = "营业执照编号错误！";
            return info;
        }
    }//如果营业执照为空
    else if ("" === busCode) {
        ret = true;
    } else {
        ret = false;
        info = "营业执照格式不对，必须是15位数的！";
        return info;
    }
    return info;
}

$.extend($.fn.validatebox.defaults.rules, {
    minLength: {
        validator: function(value, param) {
            return value.length >= param[0];
        },
        message: '最少输入 {0} 个字符'
    },
    maxLength: {
        validator: function(value, param) {
            return value.length <= param[0];
        },
        message: '最多输入 {0} 个字符'
    },
    length: {
        validator: function(value, param) {
            var len = $.trim(value).length;
            return len >= param[0] && len <= param[1];
        },
        message: "内容长度介于{0}和{1}之间"
    },
    mobile: {
        validator: function(value, param) {
            return /^1[3|4|5|8][0-9]\d{4,8}$/.test(value);
        },
        message: '手机号码不正确'
    },
    phone: {
        validator: function(value) {
            return /^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(\-\d{1,4})?$/i.test(value);
        },
        message: '格式不正确,请使用下面格式:0713-88888888'
    },
    isTelphone: {
        validator: function(value) {
            var mobile = /^1[3|4|5|8][0-9]\d{4,8}$/;
            var tel = /^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(\-\d{1,4})?$/i;
            return (tel.test(value) || mobile.test(value));
        },
        message: '格式不正确,请输入正确的手机或电话号码'
    },
    currency: {
        validator: function(value) {
            return /^d{0,}(\.\d+)?$/i.test(value);
        },
        message: '货币格式不正确'
    },
    integer: {
        validator: function(value) {
            return /^[+]?[0-9]+\d*$/i.test(value);
        },
        message: '请输入正确的整数格式'
    },
    number: {
        validator: function(value, param) {
            return /^[0-9]*([.][0-9]{1,2})?$/i.test(value);
        },
        message: '请输入正确的数字格式'
    },
    todouble: {
        validator: function(value) {
            var a = /^[0-9]{0,12}([.][0-9]{1,2})?$/i.test(value);
            if (!a) {
                $.fn.validatebox.defaults.rules.todouble.message = "小数位只能为两位";
                return false;
            }
            var b = /^0(\.0*)?$/i.test(value);
            if (b) {
                $.fn.validatebox.defaults.rules.todouble.message = "不能为零";
                return false;
            }
            return true;
        },
        message: ''
    },
    minNumber: {
        validator: function(value, param) {
            return value <= param[0];
        },
        message: '数值应该大于 {0}'
    },
    maxNumber: {
        validator: function(value, param) {
            return value >= param[0];
        },
        message: '数值应该小于 {0}'
    },
    numberRange: {
        validator: function(value, param) {
            return value >= param[0] && value <= param[1];
        },
        message: '数值超出范围{0}-{1}'
    },
    dateValidate: {
        validator: function(value, param) {
            if (value === null || value === '')
                return true;
            var reg = /^(\d{1,4})(-|\/)(\d{1,2})\2(\d{1,2})$/; //短日期格式的正则表达式
            var r = value.match(reg);
            if (r === null)
                return false;
            return true;
        },
        message: '日期格式不正确！格式:0000-00-00'
    },
    datetimeValidate: {
        validator: function(value, param) {
            if (value === null || value === '')
                return true;
            var reg = reg = /^(\d{1,4})(-|\/)(\d{1,2})\2(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$/;
            var r = value.match(reg);
            if (r === null)
                return false;
            return true;
        },
        message: '时间格式不正确！格式:0000-00-00 00:00:00'
    },
    chinese: {
        validator: function(value) {
            return /^[\u0391-\uFFE5]+$/i.test(value);
        },
        message: '请输入中文'
    },
    english: {
        validator: function(value) {
            return /^[A-Za-z]+$/i.test(value);
        },
        message: '请输入英文'
    },
    powerenglish: {
        validator: function(value) {
            return /^[A-Z]+$/i.test(value);
        },
        message: '请输入大写英文'
    },
    isSpace: {
        validator: function(value) {
            var b = /^\s*$/i.test(value) || /[\u3000]/g.test(value);
            if (!b) {
                return true;
            }
            return false;
        },
        message: '请勿输入空格'
    },
    illegal: {
        validator: function(value) {
            return /.+/i.test(value);
        },
        message: '输入值不能为空和包含其他非法字符'
    },
    faxno: {
        validator: function(value) {
            return /^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(\-\d{1,4})?$/i.test(value);
        },
        message: '传真号码不正确'
    },
    userName: {
        validator: function(value) {
            return /^[\u0391-\uFFE5]+$/i.test(value) | /^\w+[\w\s]+\w+$/i.test(value);
        },
        message: '名称只允许汉字、英文字母、数字及下划线。'
    },
    chineseName: {
        validator: function(value) {
            var a = /^\s*$/i.test(value) || /[\u3000]/g.test(value);
            if (a) {
                $.fn.validatebox.defaults.rules.chineseName.message = "请勿输入空格";
                return false;
            }
            var b = /^[\u0391-\uFFE5]+$/i.test(value);
            if (!b) {
                $.fn.validatebox.defaults.rules.chineseName.message = "请输入中文";
                return false;
            }
            if ((value.length) > 5 && (value.length < 2)) {
                $.fn.validatebox.defaults.rules.chineseName.message = "姓名长度应在2至5位之间";
                return false;
            }
            return true;
        },
        message: ''
    },
    equalTo: {
        validator: function(value, param) {
            return value === $(param[0]).val();
        },
        message: '两次输入不一至'
    },
    idCardNo: {
        validator: function(value, param) {
            var flag = isCardID(value);
            if (flag !== '') {
                $.fn.validatebox.defaults.rules.idCardNo.message = flag;
            }
            else {
                return true;
            }
        },
        message: ''
    },
    isValidBusCode: {
        validator: function(value, param) {
            var flag = isValidBusCode(value);
            if (flag !== '') {
                $.fn.validatebox.defaults.rules.isValidBusCode.message = flag;
            }
            else {
                return true;
            }
        },
        message: ''
    },
    isAfter: {
        validator: function(value, param) {
            var dateA = $.fn.datebox.defaults.parser(value);
            var dateB = $.fn.datebox.defaults.parser($(param[0]).datebox('getValue'));
            return dateA >= dateB;
        },
        message: '日期不能早于前输入框'
    },
    isLaterThanToday: {
        validator: function(value, param) {
            var date = $.fn.datebox.defaults.parser(value);
            return date > new Date();
        },
        message: '日期不能迟于今天'
    },
    //正则表达式验证  
    custRegExp: {
        validator: function(value, param) {
            var regExp = new RegExp(eval(param[0]));
            return regExp.test(value);
        },
        message: ""
    },
    //空值验证  
    emptyValid: {
        validator: function(value, param) {
            return ($.trim(value).length === 0) ? false : true;
        },
        message: "不能为空"
    },
    nonzero: {
        validator: function(value, param) {
            return (/^0(\.0*)?$/i.test(value))? false : true;
        },
        message: "数字不能为零"
    },
    //整数判断  
    //事例：  
    //intValid[9],intValid[9,]  表示最小值为9  
    //intValid[0,9] 表示取值范围为0-9  
    //intValid[,9] 表示最大值为9  
    intValid: {
        validator: function(value, param) {
            //先验证是否为整数  
            var regExp = new RegExp(/^-?\d+$/);
            if (!regExp.test(value)) {
                $.fn.validatebox.defaults.rules.intValid.message = "只能输入整数";
                return false;
            }
            var isValueCorrect = true; //判断指定值是否在某一范围内  
            if (param !== null) {
                switch (param.length) {
                    case 1:     //intValid[9] 表示最小值为9  
                        isValueCorrect = (value >= param[0]);
                        $.fn.validatebox.defaults.rules.intValid.message = "最小值为{0}";
                        break;
                    case 2:
                        if (typeof (param[0]) === "undefined") {    //intValid[,9] 表示最大值为9  
                            isValueCorrect = (value <= param[1]);
                            $.fn.validatebox.defaults.rules.intValid.message = "最大值为{1}";
                        }
                        else if (typeof (param[1]) === "undefined") {   //intValid[9,] 表示最小值为9  
                            isValueCorrect = (value >= param[0]);
                            $.fn.validatebox.defaults.rules.intValid.message = "最小值为{0}";
                        }
                        else {       //intValid[0,9] 表示取值范围为0-9  
                            isValueCorrect = ((value >= param[0]) && (value <= param[1]));
                            $.fn.validatebox.defaults.rules.intValid.message = "输入范围为{0}到{1}";
                        }
                        break;
                        defalut:
                                isValueCorrect = true;
                }
            }

            return isValueCorrect;
        },
        message: ""
    },
    doubleValid: {
        validator: function(value, param) {
            //先验证是否为浮点数  
            var a = /^[-]?[0-9]*([.][0-9]{1,2})?$/i.test(value);
            if (!a) {
                $.fn.validatebox.defaults.rules.doubleValid.message = "小数位为两位小数";
                return false;
            }
            var isValueCorrect = true; //判断指定值是否在某一范围内  
            if (param !== null) {
                switch (param.length) {
                    case 1:     //intValid[9] 表示最小值为9  
                        isValueCorrect = (value >= param[0]);
                        $.fn.validatebox.defaults.rules.doubleValid.message = "最小值为{0}";
                        break;
                    case 2:
                        if (typeof (param[0]) === "undefined") {    //intValid[,9] 表示最大值为9  
                            isValueCorrect = (value <= parseFloat(param[1]));
                            $.fn.validatebox.defaults.rules.doubleValid.message = "最大值为{1}";
                        }
                        else if (typeof (param[1]) === "undefined") {   //intValid[9,] 表示最小值为9  
                            isValueCorrect = (value >= parseFloat(param[0]));
                            $.fn.validatebox.defaults.rules.doubleValid.message = "最小值为{0}";
                        }
                        else {       //intValid[0,9] 表示取值范围为0-9  
                            isValueCorrect = ((value >= parseFloat(param[0])) && (value <= parseFloat(param[1])));
                            $.fn.validatebox.defaults.rules.doubleValid.message = "输入范围为{0}到{1}";
                        }
                        break;
                        defalut:
                                isValueCorrect = true;
                }
            }

            return isValueCorrect;
        },
        message: ""
    },
    //combo框比较
    isNotEqual: {
        validator: function(value, param) {
            if (param.length < 1) {
                return false;
            }
            var temaA = param.length !== 1 ? $(param[0]).val() : value;
            if ($(param[0]).val() === 'undefined ')
                return true;
            var temaB = $(param[1]).combo('getValue');
            return String(temaA) !== String(temaB);
        },
        message: '不能与前输入框的选择相同'
    },
    contrastNumberbox: {
        validator: function(value, param) {
            if (param.length < 1) {
                return false;
            }
            var temaA = param.length !== 1 ? $(param[0]).val() : value;
            if ($(param[0]).val() === 'undefined ')
                return true;
            var temaB = $(param[1]).numberbox('getValue');
            return parseFloat(temaA) > parseFloat(temaB);
        },
        message: '不能比前输入框数值大'
    },
    //使用方式:
//validType:'customValidate[custom]
//
//定义函数
//function custom(value){       	//写自己的逻辑代码
//if(true) return null;
//else return '校验不成功';
//}
    customValidate: {
        validator: function(value, param) {
            var rt = param[0](value); //调用函数
            param[1] = rt; //设置显示的信息
            return rt === undefined; //如果无返回信息,说明校验通过
        },
        message: '{1}' //显示校验错误信息
    },
    startWith: {
        validator: function(value, param) {
            return value.indexOf(param[0]) === 0;
        },
        message: '{1}'
    },
//    <input class="easyui-validatebox"  validType="startWith['ROLE_','编码必须以[ROLE_]开始']">
    ajax: {
        validator: function(value, param) {
            if (param instanceof Array) {
                if (param.length < 1) {
                    return false;
                }
                var url = param[0];
                var postData = {};
                if (param.length !== 1) {
                    postData = eval('(' + param[1] + ')');
                }
                postData["q"] = value;
                var m_result;
                $.ajax({
                    type: "POST",
                    url: url,
                    data: postData,
                    dataType: "json",
                    async: false,
                    success: function(rs) {
                        m_result = rs;
                    }
                });
                if (m_result.errorMsg) {
                    $.fn.validatebox.defaults.rules.ajax.message = m_result.errorMsg;
                    return false;
                }
                return true;
            }
            return false;
        },
        message: 'error'
    }
});
$.fn.datebox.defaults.validType = 'dateValidate';
$.fn.datetimebox.defaults.validType = 'datetimeValidate';
$.extend($.fn.combogrid.methods, {
    validate: function(jq) {
        return jq.each(function() {
            $(this).combo('validate');
        });
    },
    isValid: function(jq) {
        return jq.each(function() {
            $(this).combo('isValid');
        });
    },
    enableValidation: function(jq) {
        return jq.each(function() {
            $(this).combo('enableValidation');
        });
    },
    disableValidation: function(jq) {
        return jq.each(function() {
            $(this).combo('disableValidation');
        });
    }
});
//设置datagrid默认属性
$.extend($.fn.datagrid.defaults, {
    fit: true,
    rownumbers: true,
    pagination: true,
    autoRowHeight: false,
    singleSelect: true,
    selectOnCheck: false,
    checkOnSelect: false,
    striped: true,
    pageNumber: 1,
    pageSize: 20,
    pageList: [20, 40, 80, 100],
    onLoadSuccess: function(data) {
        if (isEmpty(data.rows)) {
            if ($(this).data().datagrid.options.rownumbers) {
                var body1 = $(this).data().datagrid.dc.body1;
                body1.find('table tbody').append('<tr class="datagrid-row"><td class=datagrid-td-rownumber><div class=datagrid-cell-rownumber></div></td></tr>');
            }
            var body2 = $(this).data().datagrid.dc.body2;
            body2.find('table tbody').append('<tr><td width="' + body2.width() + '" style="height: 25px; text-align: center;">暂无数据</td></tr>');
        }
    }, onBeforeLoad: function(param) {
        param['qo.rows'] = param.rows;
        param['qo.page'] = param.page;
    }
});
//获取实心节点
$.extend($.fn.tree.methods, {
    getCheckedExt: function(jq) {
        var checked = $(jq).tree("getChecked");
        var checkbox2 = $(jq).find("span.tree-checkbox2").parent();
        $.each(checkbox2, function() {
            var node = $.extend({}, $.data(this, "tree-node"), {
                target: this
            });
            checked.push(node);
        });
        return checked;
    },
    getSolidExt: function(jq) {
        var checked = [];
        var checkbox2 = $(jq).find("span.tree-checkbox2").parent();
        $.each(checkbox2, function() {
            var node = $.extend({}, $.data(this, "tree-node"), {
                target: this
            });
            checked.push(node);
        });
        return checked;
    }
});
/**
 * linkbutton方法扩展
 * @param {Object} jq
 */
$.extend($.fn.linkbutton.methods, {
    /**
     * 激活选项（覆盖重写）
     * @param {Object} jq
     */
    enable: function(jq) {
        return jq.each(function() {
            var state = $.data(this, 'linkbutton');
            if ($(this).hasClass('l-btn-disabled')) {
                var itemData = state._eventsStore;
                //恢复超链接
                if (itemData.href) {
                    $(this).attr("href", itemData.href);
                }
                //回复点击事件
                if (itemData.onclicks) {
                    for (var j = 0; j < itemData.onclicks.length; j++) {
                        $(this).bind('click', itemData.onclicks[j]);
                    }
                }
                //设置target为null，清空存储的事件处理程序
                itemData.target = null;
                itemData.onclicks = [];
                $(this).removeClass('l-btn-disabled');
            }
        });
    },
    /**
     * 禁用选项（覆盖重写）
     * @param {Object} jq
     */
    disable: function(jq) {
        return jq.each(function() {
            var state = $.data(this, 'linkbutton');

            if (!state._eventsStore)
                state._eventsStore = {};
            if (!$(this).hasClass('l-btn-disabled')) {
                var eventsStore = {};
                eventsStore.target = this;
                eventsStore.onclicks = [];
                //处理超链接
                var strHref = $(this).attr("href");
                if (strHref) {
                    eventsStore.href = strHref;
                    $(this).attr("href", "javascript:void(0)");
                }
                //处理直接耦合绑定到onclick属性上的事件
                var onclickStr = $(this).attr("onclick");
                if (onclickStr && onclickStr !== "") {
                    eventsStore.onclicks[eventsStore.onclicks.length] = new Function(onclickStr);
                    $(this).attr("onclick", "");
                }
                //处理使用jquery绑定的事件
                var eventDatas = $(this).data("events") || $._data(this, 'events');
                if (eventDatas["click"]) {
                    var eventData = eventDatas["click"];
                    for (var i = 0; i < eventData.length; i++) {
                        if (eventData[i].namespace !== "menu") {
                            eventsStore.onclicks[eventsStore.onclicks.length] = eventData[i]["handler"];
                            $(this).unbind('click', eventData[i]["handler"]);
                            i--;
                        }
                    }
                }
                state._eventsStore = eventsStore;
                $(this).addClass('l-btn-disabled');
            }
        });
    }
});
// easyui表格编辑方法
(function($) {
    function getSelectedIndex(target) {
        return $(target).prev('div.datagrid-view2').find('.datagrid-body table tbody tr.datagrid-row-selected').index();
    }
    function getEditIndex(target) {
        var editIndex = $.data(target, 'datagrid').editIndex;
        if (editIndex === undefined) {
            $.data(target, 'datagrid').editIndex = -1;
            return -1;
        }
        return editIndex;
    }
    function appendEmptyRow(target, emptyRow) {
        if (endEditA(target) === false) {
            return;
        }
        var maxRowCount = $.data(target, 'datagrid').options.maxRowCount;
        if (maxRowCount !== undefined && $(target).datagrid('getRows').length >= maxRowCount) {
            return;
        }
        var row = emptyRow || $.extend({}, $.data(target, 'datagrid').options.emptyRow);
        if (!row) {
            row = {};
            var fields = $(target).datagrid('getColumnFields');
            for (var i = 0; i < fields.length; i++) {
                row[fields[i]] = '';
            }
        }
        $(target).datagrid('appendRow', row);
        beginEditA(target, $(target).datagrid('getRows').length - 1);
    }
    function deleteRowA(target, rowIndex) {
        if (getEditIndex(target) === rowIndex) {
            $(document).unbind('.editor' + target.id);
            $.data(target, 'datagrid').editIndex = -1;
        }
        $(target).datagrid('deleteRow', rowIndex);
    }
    function editor_mousedown(e) {
        var target = e.data.target;
        if (target && $(target).length) {
            if ($.data(target, 'datagrid') === undefined) {
                $(document).unbind('.editor' + target.id);
                return;
            }
            if ($(e.target).closest("div.datagrid-editable", $(target).datagrid('getPanel')).length) {
                return;
            }
            endEditA(target);
        }
    }
    function beginEditA(target, rowIndex, field) {
        if (endEditA(target) === false) {
            return;
        }
        if (rowIndex < 0) {
            return;
        }
        if (rowIndex > $(target).datagrid('getRows').length - 1) {
            var isAutoAppend = $.data(target, 'datagrid').options.isAutoAppend;
            if (isAutoAppend) {
                appendEmptyRow(target);
            }
            return;
        }
        $(target).datagrid('beginEdit', rowIndex);
        var editors = $(target).datagrid("getEditors", rowIndex);
        if (editors.length > 0) {
            $(document).unbind('.editor' + target.id)
                    .bind('mousedown.editor' + target.id, {
                        target: target
                    }, editor_mousedown);

            $.data(target, 'datagrid').editIndex = rowIndex;
            var focusIndex = 0;
            $.each(editors, function(i, editor) {
                $(editor.target).keydown(function(e) {
                    if (e.keyCode === 13) {
                        e.preventDefault();
                        var idx = $(this).attr('idx') * 1;
                        var td = $(this).closest('td');
                        var vbox = td.find(".validatebox-text");
                        vbox.validatebox("validate");
                        vbox.trigger("mouseleave");
                        if (td.find(".validatebox-invalid").length === 0) {
                            if (idx < editors.length - 1) {
                                $(editors[idx + 1].target).focus().select();
                            } else if (idx === editors.length - 1) {
                                beginEditA(target, rowIndex + 1);
                            }
                        } else {
                            vbox.validatebox('showTip');
                        }
                    }
                }).focus(function() {
                    $(target).datagrid('selectRow', rowIndex);
                }).attr('idx', i).attr('editorType', editor.type);
                if (editor.type === 'numberbox') {
                    $(editor.target).blur(function() {
                        $(this).numberbox('fix');
                    }).focus(function() {
                        this.style.imeMode = 'disabled'; // 禁用输入法,禁止输入中文字符
                    });
                }

                if (field !== undefined && field === editor.field) {
                    focusIndex = i;
                }
            });
            $(editors[focusIndex].target).focus().select();
        }
    }
    function setEditorValue(editor, value) {
        if (editor) {
            if (editor.type === 'numberbox') {
                $(editor.target).numberbox('setValue', value);
            } else if (editor.type === 'string') {
                $(editor.target).val(value);
            }
        }
    }
    function setItemData(target, rowIndex, field, value) {
        var tr = $(target).datagrid('getPanel').find('.datagrid-view2 .datagrid-body>table>tbody').find('tr:eq(' + rowIndex + ')');
        if (tr.length) {
            var newRowData = $.extend({}, $(target).datagrid('getRows')[rowIndex]);
            newRowData[field] = value;
            var cell = tr.find("td[field='" + field + "'] div");
            if (cell.length) {
                $(target).datagrid('getRows')[rowIndex][field] = value;
                if (cell.find('table').length) { // 单元格处于编辑状态
                    var editor = $(target).datagrid('getEditor', {
                        index: rowIndex,
                        field: field
                    });
                    setEditorValue(editor, value);
                } else {
                    var text = value;
                    var formatter = $(target).datagrid('getColumnOption', field).formatter;
                    if (formatter !== undefined && typeof formatter === 'function') {
                        value = formatter(value, newRowData, rowIndex);
                    }
                    var styler = $(target).datagrid('getColumnOption', field).styler;
                    var style = "";
                    if (styler !== undefined && typeof styler === 'function') {
                        style = styler(value, newRowData, rowIndex);
                    }
                    cell.text(text);
                    if (style) {
                        cell.parent('td').attr('style', style);
                    } else {
                        cell.parent('td').attr('style', '');
                    }
                }
            }
        }
    }
    function setRowData(target, rowIndex, rowData) {
        var tr = $(target).datagrid('getPanel').find('.datagrid-view2 .datagrid-body>table>tbody').find('tr:eq(' + rowIndex + ')');
        if (tr.length) {
            if (rowData) {
                var newRowData = $.extend({}, $(target).datagrid('getRows')[rowIndex], rowData);
                for (var field in rowData) {
                    var value = rowData[field];
                    var cell = tr.find("td[field='" + field + "'] div");
                    if (cell.length) {
                        $(target).datagrid('getRows')[rowIndex][field] = value;
                        if (cell.find('table').length) { // editing
                            var editor = $(target).datagrid('getEditor', {
                                index: rowIndex,
                                field: field
                            });
                            setEditorValue(editor, value);
                        } else {
                            var text = value;
                            var formatter = $(target).datagrid('getColumnOption', field).formatter;
                            if (formatter !== undefined && typeof formatter === 'function') {
                                text = formatter(value, newRowData, rowIndex);
                            }
                            var styler = $(target).datagrid('getColumnOption', field).styler;
                            var style = "";
                            if (styler !== undefined && typeof styler === 'function') {
                                style = styler(value, newRowData, rowIndex);
                            }
                            cell.text(text);
                            if (style) {
                                cell.parent('td').attr('style', style);
                            } else {
                                cell.parent('td').attr('style', '');
                            }
                        }
                    }
                }
            }
        }
    }
    function endEditA(target) {
        if (getEditIndex(target) !== -1) {
            $(target).datagrid('getPanel').find('input[editorType="numberbox"]').numberbox('fix');
            if (!$(target).datagrid('validateRow', getEditIndex(target))) {
                return false;
            }
            $(target).datagrid('endEdit', getEditIndex(target));
            $.data(target, 'datagrid').editIndex = -1;
            $(document).unbind('.editor' + target.id);
        }
        return true;
    }
    function clearBinder(target) {
        $(document).unbind('.editor' + target.id);
    }
    $.extend($.fn.datagrid.methods, {
        //$("#dt").datagrid("setColumnTitle",{field:'productid',text:'newTitle
        setColumnTitle: function(jq, option) {
            if (option.field) {
                return jq.each(function() {
                    var $panel = $(this).datagrid("getPanel");
                    var $field = $('td[field=' + option.field + ']', $panel);
                    if ($field.length) {
                        var $span = $("span", $field).eq(0);
                        $span.html(option.text);
                    }
                });
            }
            return jq;
        },
        addEditor: function(jq, param) {
            if (param instanceof Array) {
                $.each(param,
                        function(index, item) {
                            var e = $(jq).datagrid('getColumnOption', item.field);
                            e.editor = item.editor;
                        });
            } else {
                var e = $(jq).datagrid('getColumnOption', param.field);
                e.editor = param.editor;
            }
        },
        removeEditor: function(jq, param) {
            if (param instanceof Array) {
                $.each(param,
                        function(index, item) {
                            var e = $(jq).datagrid('getColumnOption', item);
                            e.editor = {};
                        });
            } else {
                var e = $(jq).datagrid('getColumnOption', param);
                e.editor = {};
            }
        },
        getSelectedIndex: function(jq) {
            return getSelectedIndex(jq[0]);
        },
        getEditIndex: function(jq) {
            return getEditIndex(jq[0]);
        },
        appendEmptyRow: function(jq, emptyRow) {
            return jq.each(function() {
                appendEmptyRow(this, emptyRow);
            });
        },
        deleteRowA: function(jq, rowIndex) {
            return jq.each(function() {
                deleteRowA(this, rowIndex);
            });
        },
        beginEditA: function(jq, param) {
            var rowIndex = param.rowIndex;
            var field = param.field;
            return jq.each(function() {
                beginEditA(this, rowIndex, field);
            });
        },
        endEditA: function(jq) {
            return endEditA(jq[0]);
        },
        setItemData: function(jq, param) {
            var rowIndex = param.rowIndex;
            var field = param.field;
            var value = param.value;
            return jq.each(function() {
                setItemData(this, rowIndex, field, value);
            });
        },
        setRowData: function(jq, param) {
            var rowIndex = param.rowIndex;
            var rowData = param.rowData;
            return jq.each(function() {
                setRowData(this, rowIndex, rowData);
            });
        },
        clearBinder: function(jq) {
            return jq.each(function() {
                clearBinder(this);
            });
        }
    });
})(jQuery);
var fmt4EasyuiGrid = {
    Double0: function(val, row) {
        if (val === undefined || val === null || val === "") {
            return '';
        }
        return fdouble(val, 0);
    },
    Double1: function(val, row) {
        if (val === undefined || val === null || val === "") {
            return '';
        }
        return fdouble(val, 1);
    },
    Double2: function(val, row) {
        if (val === undefined || val === null || val === "") {
            return '';
        }
        return fdouble(val, 2);
    },
    Double4: function(val, row) {
        if (val === undefined || val === null || val === "") {
            return '';
        }
        return fdouble(val, 4);
    },
    Money0: function(val, row) {
        if (val === undefined || val === null || val === "") {
            return '';
        }
        return fmoney(val, 0);
    },
    Money1: function(val, row) {
        if (val === undefined || val === null || val === "") {
            return '';
        }
        return fmoney(val, 1);
    },
    Money2: function(val, row) {
        if (val === undefined || val === null || val === "") {
            return '';
        }
        return fmoney(val, 2);
    },
    Money4: function(val, row) {
        if (val === undefined || val === null || val === "") {
            return '';
        }
        return fmoney(val, 4);
    },
    AbsMoney0: function(val, row) {
        if (val === undefined || val === null || val === "") {
            return '';
        }
        var f = fmoney(val, 0);
        if (f.charAt(0) === '-') {
            f = f.substring(1);
        }
        return f;
    },
    AbsMoney1: function(val, row) {
        if (val === undefined || val === null || val === "") {
            return '';
        }
        var f = fmoney(val, 1);
        if (f.charAt(0) === '-') {
            f = f.substring(1);
        }
        return f;
    },
    AbsMoney2: function(val, row) {
        if (val === undefined || val === null || val === "") {
            return '';
        }
        var f = fmoney(val, 2);
        if (f.charAt(0) === '-') {
            f = f.substring(1);
        }
        return f;
    },
    AbsMoney4: function(val, row) {
        if (val === undefined || val === null || val === "") {
            return '';
        }
        var f = fmoney(val, 4);
        if (f.charAt(0) === '-') {
            f = f.substring(1);
        }
        return f;
    },
    longToDate: function(val, row) {
        if (val === null)
            return '';
        var d = new Date(val);
        return d.Format('yyyy-MM-dd');
    },
    longToDateTime: function(val, row) {
        if (val === null)
            return '';
        var d = new Date(val);
        return d.Format('yyyy-MM-dd HH:mm:ss');
    },
    secondToDate: function(val, row) {
        if (val === null)
            return '';
        var d = new Date(val * 1000);
        return d.Format('yyyy-MM-dd');
    },
    secondToDateTime: function(val, row) {
        if (val === null)
            return '';
        var d = new Date(val * 1000);
        return d.Format('yyyy-MM-dd HH:mm:ss');
    },
    timestampToDate: function(val, row) {
        if (val !== undefined && val !== null)
        {
            var d = new Date(val);
            return d.Format('yyyy-MM-dd');
        } else {
            return '';
        }
    },
    StringToDate: function(val, row) {
        if (val !== undefined && val !== null)
        {
            var str = val.toString();
            str = str.replace(/-/g, "/");
            var d = new Date(str);
            return d.Format('yyyy-MM-dd');
        } else {
            return '';
        }
    },
    StringToDateTime: function(val, row) {
        if (val !== undefined && val !== null)
        {
            var str = val.toString();
            str = str.replace(/-/g, "/");
            var d = new Date(str);
            return d.Format('yyyy-MM-dd HH:mm');
        } else {
            return '';
        }
    }
};
/*
 * $(function(){   
 *    $(selector).focuschange(function(e,oldValue,newValue){   
 *       alert('旧值：' + oldValue + ";新值：" + newValue);   
 *    });   
 *});
 */
$.fn.focuschange = function(callback) {
    return this.each(function() {
        var state = $.data(this, "focusblurlistener", {
            validating: true,
            value: undefined
        });
        var box = $(this);
        box.unbind(".focusblurlistener").bind("focus.focusblurlistener", function(event) {
            state.validating = true;
            state.value = state.value || "";
            (function() {
                if (state.validating) {
                    if (state.value !== box.val()) {
                        callback.call(this, event, state.value, box.val());
                        state.value = box.val();
                    }
                    setTimeout(arguments.callee, 200);
                } else {
                    return;
                }
            })();
        }).bind("blur.focusblurlistener", function() {
            state.validating = false;
        });
    });
};