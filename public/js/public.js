var g_isLocked = false;     // 用于锁住弹出的参照表格，不让它自动关闭
var g_dialogIndex = 0;      // modal dialog索引

//弹出提示框
function d_alert(message, func) {
    $.messager.alert('提示', message, '', func);
}
function d_error(message, func) {
    $.messager.alert('错误', message, 'error', func);
}
function d_info(message, func) {
    $.messager.alert('提示', message, 'info', func);
}
function d_question(message, func) {
    $.messager.alert('提示', message, 'question', func);
}
function d_warning(message, func) {
    $.messager.alert('提示', message, 'warning', func);
}
function d_confirm(message, func) {
    $.messager.confirm('提示', message, func);
}
function d_prompt(message, func) {
    $.messager.prompt('', message, func);
}
function d_show(message, timeout) {
    $.messager.show({
        title: '提示',
        msg: message,
        showType: 'show',
        timeout: timeout ? timeout : 5000
    });
}
//处理ajax结果
function handleResult(rs, func) {
    var json;
    if (typeof rs === 'string') {
        if (rs.substring(0, 7) === '<script') {
            eval(rs.substring(8, rs.length - 9));
            return;
        }
        if ($.trim(rs) === '') {
            d_error('服务器没有返回结果！', func);
            return;
        }
        json = eval('(' + rs + ')');
    } else {
        json = rs;
    }

    if (json.rs === 0) {
        if (json.message) {
            d_info(json.message, func);
        } else {
            if (func !== undefined && typeof func === 'function') {
                func(json);
            }
        }
    } else if (json.rs === 1) {
        if (json.message) {
            d_warning(json.message, func);
        } else {
            if (func !== undefined && typeof func === 'function') {
                func(json);
            }
        }
    } else if (json.rs === 2) {
        if (json.message) {
            d_error(json.message, func);
        } else {
            if (func !== undefined && typeof func === 'function') {
                func(json);
            }
        }
    } else if (json.rs === 3) {
        d_confirm(json.message, func);
    }
}

//获取jquery对象
function getJQueryObj(obj) {
    if (typeof obj === 'string') {
        if (obj.substring(0, 1) === '#') {
            return $(obj);
        } else {
            return $('#' + obj);
        }
    } else if (obj instanceof jQuery) {
        return obj;
    } else {
        return $(obj);
    }
}
//格式化日期
function date_formatter(date) {
    var y = date.getFullYear();
    var m = date.getMonth() + 1;
    var d = date.getDate();
    return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
}

//格式化日期 HH:mm:ss
function date_formatterTime(date) {
    var y = date.getFullYear();
    var m = date.getMonth() + 1;
    var d = date.getDate();
    var h = date.getHours();
    var M = date.getMinutes();
    var s = date.getSeconds();
    return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d) + ' ' + (h < 10 ? ('0' + h) : h) + ':' + (M < 10 ? ('0' + M) : M) + ':' + (s < 10 ? ('0' + s) : s);
}

//解析字符串日期
function date_parser(s) {
    if (!s)
        return new Date();
    var ss = s.split('-');
    var y = parseInt(ss[0], 10);
    var m = parseInt(ss[1], 10);
    var d = parseInt(ss[2], 10);
    if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
        return new Date(y, m - 1, d);
    } else {
        return new Date();
    }
}
//表单内所有input值转成json对象
function formToJson(form) {

    var formObj = getJQueryObj(form);
    if (formObj === null) {
        return null;
    }

    var inputs = formObj.find(":input");
    var json = {};
    for (var i = 0; i < inputs.length; i++) {
        var name = $(inputs[i]).attr('name');
        var type = $(inputs[i]).attr('type');
        var val = $(inputs[i]).val();

        if (name !== undefined && name !== null && name !== '') {
            if (type === 'checkbox') {
                json[name] = $(inputs[i]).prop('checked');
            } else if (type === 'radio') {
                if ($(inputs[i]).prop('checked')) {
                    json[name] = val;
                }
            } else {
                json[name] = val;
            }
        }
    }
    return json;
}
//表单内所有input值转成json对象
function formToJson2(form, key) {

    var formObj = getJQueryObj(form);
    if (formObj === null) {
        return null;
    }
    if (key === undefined) {
        key = 'qo';
    }
    var inputs = formObj.find(":input");
    var json = {};
    for (var i = 0; i < inputs.length; i++) {
        var name = $(inputs[i]).attr('name');
        var type = $(inputs[i]).attr('type');
        var val = $(inputs[i]).val();

        if (name !== undefined && name !== null && name !== '') {
            if (type === 'checkbox') {
                json[key + '.' + name] = $(inputs[i]).prop('checked');
            } else if (type === 'radio') {
                if ($(inputs[i]).prop('checked')) {
                    json[key + '.' + name] = val;
                }
            } else {
                json[key + '.' + name] = val;
            }
        }
    }
    return json;
}
//表单内所有input值转成字符串序列
function formToString(form) {
    var json = formToJson(form);
    var s = '';
    for (var key in json) {
        s += key + '=' + json[key] + '&';
    }
    return s;
}
//表单内所有input值转成字符串序列
function formToString2(form, key) {
    var json = formToJson2(form, key);
    var s = '';
    for (var key in json) {
        s += key + '=' + json[key] + '&';
    }
    return s;
}
//重置表单内所有input
function formToDefault(form) {

    var formObj = getJQueryObj(form);
    if (formObj === null) {
        return;
    }
    formObj.find("input[type='radio']").attr("checked", '0');
    formObj.find("input:not([type='checkbox'])").val('');
    formObj.find("input[type='checkbox']").removeAttr('checked');
    formObj.find("select").find("option:eq(0)").attr('selected', 'selected');
}
//重新调整页面内的layout大小
function resizeLayout(pageId) {
    $('#' + pageId).find('div.layout').layout('resize');
}

function adjustDialogPosition(dialog, left, top) {
    var isAdjust = false;
    var leftx = left;
    var topx = top;
    var bodyWidth = dialog.dialog('panel').parent().width();
    var bodyHeight = dialog.dialog('panel').parent().height();
    var dialogWidth = dialog.parent('div').width();
    if (top < 0) {
        topx = 0;
        isAdjust = true;
    } else if (top > bodyHeight - 30) {
        topx = bodyHeight - 30;
        isAdjust = true;
    }
    if (left < 100 - dialogWidth) {
        leftx = 100 - dialogWidth;
        isAdjust = true;
    } else if (left > bodyWidth - 100) {
        leftx = bodyWidth - 100;
        isAdjust = true;
    }
    if (isAdjust) {
        dialog.dialog('move', {
            left: leftx,
            top: topx
        });
    }
}

//打开一个模态对话框
// title:窗口名称 url:地址 width:宽度 height:高度 resizable:可以调整大小 maximizable:可以最大化
function openModalDialog(options) {
    //    title,url,onClose,rs
    if (typeof options !== 'object') {
        return;
    }
    if (options.url === undefined || options.url === '') {
        return;
    }
    var index = g_dialogIndex++;
    var modalDialog = $('#modalDialog' + index);
    if (modalDialog.length === 0) {
        modalDialog = $("<div id='modalDialog" + index + "'></div>").appendTo('body');
        modalDialog.dialog({
            href: options.url,
            title: options.title ? options.title : '',
            modal: true,
            width: options.width ? options.width : 800,
            height: options.height ? options.height : 600,
            resizable: options.resizable === undefined ? true : options.resizable,
            maximizable: options.maximizable === undefined ? true : options.maximizable,
            buttons: options.buttons ? options.buttons : undefined,
            onMove: function(left, top) {
                adjustDialogPosition(modalDialog, left, top);
            },
            onClose: function() {
                modalDialog.dialog('destroy');
                if (options.pageId) {
                    clearPage(options.pageId);
                }
                if (typeof options.onClose === 'function') {
                    options.onClose(options.rs);
                }
            },
            onResize: function() {
                if (options.pageId) {
                    $('#' + options.pageId).find('div.layout').layout('resize');
                }
            }
        });
        modalDialog.dialog('panel').find('div.panel-header').dblclick(function(e) {
            if ($(this).find('a.panel-tool-restore').length > 0) {
                modalDialog.dialog('restore');
            } else {
                modalDialog.dialog('maximize');
            }
        });
    } else {
        modalDialog.dialog('open');
    }
}
//清理页面无用标签元素
function clearPage(pageId) {
    $('div[id^=' + pageId + '_][id$=_popup]').remove();
    var menus = $('div[id^=' + pageId + '_][id$=_menu]');
    menus.next('div.menu-shadow').remove();
    menus.remove();
}
//通过一个元素对象获取这个对象所在的对话框索引，与openModalDialog配套使用
function getMyDialogIndex(obj) {
    obj = getJQueryObj(obj);
    var dialog = obj.closest('.window-body');
    if (dialog.length) {
        var id = dialog[0].id;
        if (id.substring(0, 11) === 'modalDialog') {
            return parseInt(id.substring(11));
        }
    }
    return -1;
}
//通过一个元素对象获取这个对象所在的对话框并关闭，与openModalDialog配套使用
function closeMyDialog(obj) {
    obj = getJQueryObj(obj);
    var dialog = obj.closest('.window-body');
    if (dialog.length) {
        dialog.dialog('close');
    }
}
//关闭指定索引的对话框,若不指定index,则关闭最后一个对话框,与openModalDialog配套使用
function closeModalDialog(index) {
    var modalDialogs;
    if (index === undefined) {
        modalDialogs = $('[id^=modalDialog]');
        if (modalDialogs.length > 0) {
            $(modalDialogs[modalDialogs.length - 1]).dialog('close');
        }
    } else {
        modalDialogs = $('#modalDialog' + index);
        if (modalDialogs.length > 0) {
            modalDialogs.dialog('close');
        }
    }
}
//创建combogrid,comboOptions为combo的参数(相见下面注释),gridOptions为datagrid的参数(可以参照easyui的datagrid)
function createComboGrid(comboOptions, gridOptions) {
    var isReloadOnChanged = comboOptions.isReloadOnChanged;         // 是否在input值发生改变时立即刷新参照表格
    var isRemoveOnHide = comboOptions.isRemoveOnHide === undefined ? true : false;               // 是否在隐藏时删除掉参照层
    var delay = comboOptions.delay ? comboOptions.delay : 1000;     // 当input值发生改变时延时加载参照表格的时间 单位毫秒
    var pageId = comboOptions.pageId ? comboOptions.pageId : '';    // 当前页面id
    var inputId = comboOptions.inputId;                             // 关联的input id
    var onChange = comboOptions.onChange;                           // 当input值发生改变时的响应函数
    var onAccept = comboOptions.onAccept;                           // 当用户确定选择一条记录时的响应函数
    var onBeforeReload = comboOptions.onBeforeReload;               // 在叫在数据前的相应函数
    var gridWidth = comboOptions.gridWidth ? comboOptions.gridWidth : 500;  // 数据表格的宽度，默认500
    var comboWidth = (comboOptions.comboWidth ? comboOptions.comboWidth : 250) - 2;  // input的宽度
    var inputWidth = comboWidth - 22;

    var grid = null;
    var popup = null;
    var gridId = pageId + '_' + inputId + '_grid';
    var popupId = pageId + '_' + inputId + '_popup';
    var comboId = inputId + '_combo';
    var btnId = inputId + '_btn';

    var selected = null;
    var selectedIndex = -1;
    var mouseIn = false;
    var focusIn = false;

    function $obj(id) {
        if (pageId !== '') {
            return $('#' + pageId).find('#' + id);
        } else {
            return $('#' + id);
        }
    }

    function getSelectedIndex() {
        if (grid === null) {
            return -1;
        } else {
            return grid.prev('div.datagrid-view2').find('.datagrid-body table tbody tr.datagrid-row-selected').index();
        }
    }

    function getColumWidthSum() {
        var columns = gridOptions.columns[0];
        var sum = 0;
        for (var i = 0; i < columns.length; i++) {
            if (columns[i].hidden === undefined || columns[i].hidden === false) {
                sum += columns[i].width;
            }
        }
        return sum;
    }

    function createDataGrid() {
        var params = {};
        if (typeof onBeforeReload === 'function') {
            if (!onBeforeReload(params)) {
                return;
            }
        }

        var columnWidthSum = getColumWidthSum();
        if (gridWidth > columnWidthSum + 30) {
            gridWidth = columnWidthSum + 30;
            if (gridOptions.fit === undefined || gridOptions.fit === false) {
                gridOptions["width"] = columnWidthSum + 30;
            }
        }

        popup = $("<div id=\"" + popupId + "\" class=\"diy-combo-panel panel-body panel-body-noheader panel-noscroll\" " +
                "style=\"position:absolute; z-index:" + ($.fn.window.defaults.zIndex++) + "; display:none; left:0px; top:0px; width:" + gridWidth + "px;height:230px;\"></div>").appendTo('body');
        grid = $("<table id=\"" + gridId + "\"></table>").appendTo(popup);
        popup.hover(function() {
            mouseIn = true;
        }, function() {
            mouseIn = false;
        });
        $('#' + gridId).datagrid($.extend(gridOptions, {
            onDblClickRow: function(rowIndex, rowData) {
                if (typeof onAccept === 'function')
                    onAccept(rowData);
                hidePopup(true);
            },
            queryParams: $.extend(params, {
                q: $obj(inputId).val()
            })
        }));
        $('#' + gridId).datagrid('getPanel').find('div.datagrid-header div.datagrid-cell').css("text-align", "center");
    }

    function showPopup() {
        if (popup === null) {
            return;
        }
        var inputObj = $obj(inputId);
        if (inputObj.attr('readonly') === 'readonly') {
            return;
        }
        if (!focusIn) {
            return;
        }
        popup.show();
        var x = inputObj.offset().left;
        var y = inputObj.offset().top + inputObj.height() + 2;
        var popupWidth = popup.width();
        var bodyWidth = $('body').width();
        if (x > bodyWidth - popupWidth) {
            x = bodyWidth - popupWidth;
        }
        if (x < 0) {
            x = 0;
        }
        popup.css('z-index', $.fn.window.defaults.zIndex++);
        popup.css('top', y + 'px');
        popup.css('left', x + 'px');
        $(document).unbind('.' + popupId).bind('mousedown.' + popupId, p_mousedown);
    }

    function hidePopup(force) {
        if (popup === null) {
            return;
        }
        if (force || (!mouseIn && g_isLocked !== true)) {
            if (isRemoveOnHide) {
                popup.remove();
                popup = null;
                grid = null;
            } else {
                popup.hide();
            }
            $(document).unbind('.' + popupId);
        }
    }

    function reload(q) {
        grid.datagrid('clearSelections');
        var params = {};
        if (typeof onBeforeReload === 'function') {
            if (!onBeforeReload(params)) {
                return;
            }
        }
        grid.datagrid('reload', $.extend(params, {
            q: q
        }));
        showPopup();
    }

    var timer = null;
    function reloadLater(delay) {
        if (timer !== null) {
            clearTimeout(timer);
        }
        timer = setTimeout(function() {
            reload($obj(inputId).val());
        }, delay ? delay : 50);
    }

    var timerA = null;
    function checkChanged() {
        if (timerA !== null) {
            clearTimeout(timerA);
        }
        timerA = setTimeout(function() {
            var inputObj = $obj(inputId);
            var oldval = inputObj.attr('oldval');
            var curval = inputObj.val();
            if (oldval !== curval) {
                if (typeof onChange === 'function')
                    onChange();
                inputObj.attr('oldval', curval);
                if (isReloadOnChanged) {
                    if (popup === null) {
                        createDataGrid();
                        showPopup();
                    } else {
                        reloadLater(delay);
                    }
                }
            }
        }, 50);
    }

    function p_mousedown(e) {
        var p = $(e.target).closest("div.diy-combo-panel", popup);
        if (p.length) {
            return;
        }
        hidePopup();
    }

    $obj(inputId).removeClass().css('width', inputWidth + 'px').addClass('combo-text validatebox-text').after(
            "<span id=\"" + comboId + "\" class=\"combo\" style=\"width: " + comboWidth + "px;\">" +
            "<span>" +
            "<span id=\"" + btnId + "\" class=\"combo-arrow\">" +
            "</span>" +
            "</span>").prependTo($obj(comboId));

    $obj(btnId).hover(function() {
        $(this).addClass("combo-arrow-hover");
    }, function() {
        $(this).removeClass("combo-arrow-hover");
    }).click(function() {
        $obj(inputId).focus();
        if (grid === null) {
            createDataGrid();
            showPopup();
        } else {
            reload($obj(inputId).val());
        }
        return false;
    });

    $obj(inputId)
            .hover(function() {
                mouseIn = true;
            }, function() {
                mouseIn = false;
            }).focus(function() {
        $(this).attr('oldval', $(this).val());
        focusIn = true;
    }).blur(function() {
        hidePopup();
        focusIn = false;
    })
            .keydown(function(e) {
                if (e.keyCode === 40) { // 下
                    e.preventDefault();
                    if (grid !== null) {
                        selected = grid.datagrid('getSelected');
                        if (selected === null) {
                            grid.datagrid('selectRow', 0);
                        } else {
                            var rowCount = grid.datagrid('getData').rows.length;
                            if (rowCount > 0) {
                                selectedIndex = getSelectedIndex();
                                if (selectedIndex < rowCount - 1) {
                                    grid.datagrid('selectRow', selectedIndex + 1);
                                }
                            }
                        }
                    }
                } else if (e.keyCode === 38) { // 上
                    e.preventDefault();
                    if (grid !== null) {
                        selected = grid.datagrid('getSelected');
                        if (selected === null) {

                        } else {
                            selectedIndex = getSelectedIndex();
                            if (selectedIndex > 0) {
                                grid.datagrid('selectRow', selectedIndex - 1);
                            }
                        }
                    }
                } else if (e.keyCode === 13) {
                    e.preventDefault();
                    if (grid === null) {
                        createDataGrid();
                        showPopup();
                    } else {
                        selected = grid.datagrid('getSelected');
                        if (selected !== null && popup.is(":visible")) {
                            if (typeof onAccept === 'function')
                                onAccept(selected);
                            hidePopup(true);
                        } else {
                            reloadLater();
                        }
                    }
                } else if (e.keyCode === 9 || e.keyCode === 27) {

                } else {
                    checkChanged();
                }
            });
}
//焦点聚焦到下一个input,container为input范围,isSelect为是否全选焦点值
function focusNext(container, isSelect) {
    var containerObj = getJQueryObj(container);
    if (containerObj === null) {
        return;
    }

    var inputs = containerObj.find(':input');
    var isFind = false;
    for (var i = 0; i < inputs.length; i++) {
        if (!isFind) {
            if (inputs.get(i) === document.activeElement) {
                isFind = true;
            }
        } else {
            if ($(inputs.get(i)).css('display') !== 'none' && $(inputs.get(i)).attr('type') !== 'hidden' && $(inputs.get(i)).attr('readonly') !== 'readonly') {
                if (isSelect) {
                    $(inputs.get(i)).focus().select();
                } else {
                    $(inputs.get(i)).focus();
                }
                break;
            }
        }
    }
}
//绑定回车时间,input为input对象,onEnter为回车响应函数
function bindEnter(input, onEnter) {
    if (typeof onEnter !== 'function') {
        return;
    }
    var inputObj = getJQueryObj(input);
    if (inputObj === null) {
        return;
    }
    inputObj.keydown(function(e) {
        if (e.which === 13) {
            onEnter();
        }
    });
}
//获取一个指定字体颜色的span标签
function getColorSpan(msg, color) {
    if (color === undefined) {
        return "<span style='color:blue;'>" + msg + "</span>";
    } else {
        return "<span style='color:" + color + ";'>" + msg + "</span>";
    }
}
//打开遮罩层,id为层的id名,obj为遮盖的元素对象
function showLoading(id, obj) {
    var x = obj.offset().left;
    var y = obj.offset().top;
    var width = obj.width();
    var height = obj.height();
    $("<div id='" + id + "' class='panel-loading' style=\"background-color:white;position:absolute; z-index:9999;top:" + y + "px;left:" + x + "px;width:" + (width - 30) + "px;height:" + (height - 21) + "px;\">加载中...</div>").appendTo('body').show();
}
//关闭指定id的遮罩层,与showLoading配套使用
function hideLoading(id) {
    $('div#' + id).remove();
}

function getModalDialog() {
    var modalDialogs = $('[id^=modalDialog]');
    if (modalDialogs.length > 0) {
        return $(modalDialogs[modalDialogs.length - 1]);
    }
    return null;
}

function getPrev(datagrid, idKey, idKeys) {
    var listA = datagrid;
    if (idKey === undefined) {
        idKey = 'id';
    }
    if (idKeys === undefined) {
        idKeys = 'id';
    }
    var curIndex = listA.datagrid('getSelectedIndex');
    if (curIndex < 0) {
        return;
    }
    var pager = listA.datagrid('getPager');
    var total = pager.pagination('options').total;
    var pageSize = pager.pagination('options').pageSize;
    var pageTotal = Math.ceil(total / pageSize) || 1;
    var curPage = pager.pagination('options').pageNumber;
    if (curPage === 1 && curIndex === 0) {
        d_info('已经没有上一张了！！');
        return;
    }
    diy_showMask();
    try {
        if (curIndex === 0) {
            if (curPage > 1) {
                var t_onLoadSuccess = listA.datagrid('options').onLoadSuccess;
                listA.datagrid('options').onLoadSuccess = function(data) {
                    listA.datagrid('options').onLoadSuccess = t_onLoadSuccess;
                    if (t_onLoadSuccess && typeof t_onLoadSuccess === 'function') {
                        t_onLoadSuccess(data);
                    }
                    listA.datagrid('selectRow', pageSize - 1);
                    var selectedRow = listA.datagrid('getSelected');
                    var modalDialog = getModalDialog();
                    var url = modalDialog.dialog('options').href;
                    var newUrl = getNewUrl(url, idKey, idKeys, selectedRow[idKey]);
                    modalDialog.dialog('refresh', newUrl);
                };

                curPage--;
                pager.pagination('select', curPage);
            }
        } else if (curIndex > 0) {
            curIndex--;
            listA.datagrid('selectRow', curIndex);
            var selectedRow = listA.datagrid('getSelected');
            var modalDialog = getModalDialog();
            var url = modalDialog.dialog('options').href;
            var newUrl = getNewUrl(url, idKey, idKeys, selectedRow[idKey]);
            modalDialog.dialog('refresh', newUrl);
        }
    } finally {
        setTimeout(function() {
            diy_hideMask();
        }, 500);
    }
}

function getNext(datagrid, idKey, idKeys) {
    var listA = datagrid;
    if (idKey === undefined) {
        idKey = 'id';
    }
    if (idKeys === undefined) {
        idKeys = 'id';
    }
    var curIndex = listA.datagrid('getSelectedIndex');
    if (curIndex < 0) {
        return;
    }
    var pager = listA.datagrid('getPager');
    var total = pager.pagination('options').total;
    var pageSize = pager.pagination('options').pageSize;
    var pageTotal = Math.ceil(total / pageSize) || 1;
    var curPage = pager.pagination('options').pageNumber;
    if (curPage === pageTotal && curIndex === total - pageSize * (curPage - 1) - 1) {
        d_info('已经没有下一张了！！');
        return;
    }
    diy_showMask();
    try {
        if (curIndex === pageSize - 1) {
            if (curPage < pageTotal) {
                var t_onLoadSuccess = listA.datagrid('options').onLoadSuccess;
                listA.datagrid('options').onLoadSuccess = function(data) {
                    listA.datagrid('options').onLoadSuccess = t_onLoadSuccess;
                    if (t_onLoadSuccess && typeof t_onLoadSuccess === 'function') {
                        t_onLoadSuccess(data);
                    }
                    listA.datagrid('selectRow', 0);
                    var selectedRow = listA.datagrid('getSelected');
                    var modalDialog = getModalDialog();
                    var url = modalDialog.dialog('options').href;
                    var newUrl = getNewUrl(url, idKey, idKeys, selectedRow[idKey]);
                    modalDialog.dialog('refresh', newUrl);
                };

                curPage++;
                pager.pagination('select', curPage);
            }
        } else if (curIndex >= 0 && curIndex < pageSize - 1) {
            curIndex++;
            listA.datagrid('selectRow', curIndex);
            var selectedRow = listA.datagrid('getSelected');
            var modalDialog = getModalDialog();
            var url = modalDialog.dialog('options').href;
            var newUrl = getNewUrl(url, idKey, idKeys, selectedRow[idKey]);
            modalDialog.dialog('refresh', newUrl);
        }
    } finally {
        setTimeout(function() {
            diy_hideMask();
        }, 500);
    }
}

function getNewUrl(url, idKey, idKeys, idValue) {
    var secs = url.split(/[&?]/);
    var newUrl = '';
    if (idKeys !== undefined) {
        idKey = idKeys;
    }
    for (var i = 0; i < secs.length; i++) {

        if (secs[i].indexOf(idKey + '=') === 0) {
            newUrl += idKey + '=' + idValue;
        } else {
            newUrl += secs[i];
        }
        if (i === 0) {
            newUrl += "?";
        } else {
            newUrl += "&";
        }
    }
    return newUrl;
}
function showTooltip(id, func) {
    $('#' + id + '').tooltip({
        position: 'bottom',
        content: '<span style="color:#fff">' + func + '</span>',
        onShow: function() {
            $(this).tooltip('tip').css({backgroundColor: '#666', borderColor: '#666'});
        }});
}
function diy_showMask() {
    var obj = $('body');
    var x = obj.offset().left;
    var y = obj.offset().top;
    var width = obj.width();
    var height = obj.height();

    $("<div id='diy-mask' style=\"background-color:white;position:absolute;z-index:9999;top:" + y + "px;left:" + x + "px;width:" + width + "px;height:" + (height - 21) + "px;filter:Alpha(opacity=30); opacity:0.6;\"></div>").appendTo('body').show();
}

function diy_hideMask() {
    $('div#diy-mask').remove();
}

function openLoadingDialog() {
    if ($('#loading-dialog').length === 0) {
        $('<div id="loading-dialog" class="easyui-dialog" title="" style="width:300px;height:100px;padding:10px;"></div>').appendTo('body');
        $('#loading-dialog').dialog({
            iconCls: 'icon-load',
            resizable: true,
            modal: true,
            closable: false,
            closed: true,
            content: '<img src="../images/load.gif" style="width: 20px; height: 20px;"/><span style="color: #AAAAAA; font-size: 16px;">  正在执行中，请稍候...</span>'
        });
    }
    $('#loading-dialog').dialog('open');
}

function closeLoadingDialog() {
    $('#loading-dialog').dialog('close');
}

var tree_loader = function(param, success, error) {
    var opts = $(this).tree("options");
    if (!opts.url) {
        return false;
    }
    $.ajax({type: opts.method, url: opts.url, data: param, dataType: "json", success: function(result) {
            if (result.rs === hsjs.serv.result.FATAL) {
                hsjs.messager.error(result.message, function() {
                    window.location = '/gysht';
                });
            } else if (result.rs === hsjs.serv.result.WARN) {
                hsjs.messager.warn(result.message);
            } else if (result.rs === hsjs.serv.result.ERROR) {
                hsjs.messager.error(result.message);
            } else if (result.rs === hsjs.serv.result.SUCCESS) {
                success(result.rows);
            }
        }, error: function() {
            error.apply(this, arguments);
        }});
};
var combo_loader = function(param, success, error) {
    var opts = $(this).combobox("options");
    if (!opts.url) {
        return false;
    }
    $.ajax({type: opts.method, url: opts.url, data: param, dataType: "json", success: function(result) {
            if (result.rs === hsjs.serv.result.FATAL) {
                hsjs.messager.error(result.message, function() {
                    window.location = '/gysht';
                });
            } else if (result.rs === hsjs.serv.result.WARN) {
                hsjs.messager.warn(result.message);
            } else if (result.rs === hsjs.serv.result.ERROR) {
                hsjs.messager.error(result.message);
            } else if (result.rs === hsjs.serv.result.SUCCESS) {
                success(result.rows);
            }
        }, error: function() {
            error.apply(this, arguments);
        }});
};
var datagrid_loader = function(param, success, error) {
    var that = $(this);
    var opts = that.datagrid("options");
    if (!opts.url) {
        return false;
    }
    $.ajax({
        type: opts.method,
        url: opts.url,
        data: param,
        dataType: "json",
        success: function(result) {
            if (result.rs === hsjs.serv.result.FATAL) {
                hsjs.messager.error(result.message, function() {
                    window.location = '/gysht';
                });
            } else if (result.rs === hsjs.serv.result.WARN) {
                hsjs.messager.warn(result.message);
            } else if (result.rs === hsjs.serv.result.ERROR) {
                hsjs.messager.error(result.message);
            } else if (result.rs === hsjs.serv.result.SUCCESS) {
                success(result);
            }
            that.datagrid("loaded");
        },
        error: function() {
            error.apply(this, arguments);
        },
        complete: function(XHR, TS) {
//            closeLoadingPage();
        }
    });
};

var hsjs = {};

hsjs.messager = {
    alert: function(message, func) {
        $.messager.alert('提示', message, '', func);
    },
    error: function(message, func) {
        $.messager.alert('错误', message, 'error', func);
    },
    info: function(message, func) {
        $.messager.alert('提示', message, 'info', func);
    },
    question: function(message, func) {
        $.messager.alert('提示', message, 'question', func);
    },
    warn: function(message, func) {
        $.messager.alert('提示', message, 'warning', func);
    },
    confirm: function(message, func) {
        $.messager.confirm('提示', message, func);
    },
    prompt: function(message, func) {
        $.messager.prompt('', message, func);
    },
    show: function(message, timeout) {
        $.messager.show({
            title: '提示',
            msg: message,
            showType: 'show',
            timeout: timeout ? timeout : 5000
        });
    }
};

hsjs.dialog = {
    open: function(options) {
        // url地址
        var href;
        if (options.url) {
            href = options.url;
        } else {
            href = '../' + options.controller + '/' + options.method + '.do';
        }
        if (options.param) {
            var paramStr = '';
            for (var key in options.param) {
                paramStr += key + '=' + options.param[key] + '&';
            }
            href = href + '?' + paramStr;
        }
        var title = options.title ? options.title : '';
        var modal = options.modal === undefined ? true : options.modal;
        var width = options.width ? options.width : 800;
        var height = options.height ? options.height : 600;
        var resizable = options.resizable === undefined ? true : options.resizable;
        var maximizable = options.maximizable === undefined ? true : options.maximizable;
        var buttons = options.buttons ? options.buttons : undefined;
        var onClose = options.onClose;

        if (href === undefined || href === '') {
            return;
        }
        var index = g_dialogIndex++;
        var modalDialog = $('#modalDialog' + index);
        if (modalDialog.length === 0) {
            modalDialog = $("<div id='modalDialog" + index + "'></div>").appendTo('body');
            modalDialog.dialog({
                href: href,
                title: title,
                modal: modal,
                width: width,
                height: height,
                resizable: resizable,
                maximizable: maximizable,
                buttons: buttons,
                onMove: function(left, top) {
                    adjustDialogPosition(modalDialog, left, top);
                },
                onClose: function() {
                    modalDialog.dialog('destroy');
                    if (options.pageId) {
                        clearPage(options.pageId);
                    }
                    if (onClose) {
                        onClose(options.rs);
                    }
                },
                onResize: function() {
                    modalDialog.find('div.layout').layout('resize');
                }
            });
            modalDialog.dialog('panel').find('div.panel-header').dblclick(function(e) {
                if (maximizable) {
                    if ($(this).find('a.panel-tool-restore').length > 0) {
                        modalDialog.dialog('restore');
                    } else {
                        modalDialog.dialog('maximize');
                    }
                }
            });
        } else {
            modalDialog.dialog('open');
        }
    },
    close: function(pageId) {    //关闭指定索引的对话框,若不指定index,则关闭最后一个对话框,与hsjs.dialog.open配套使用
        var modalDialog;
        if (pageId === undefined) {
            modalDialog = $('[id^=modalDialog]');
            if (modalDialog.length > 0) {
                $(modalDialog[modalDialog.length - 1]).dialog('close');
            }
        } else {
            modalDialog = $('#' + pageId).closest('.window');
            if (modalDialog.length > 0) {
                modalDialog.dialog('close');
            }
        }
    },
    openLoading: function() {
        if ($('#loading-dialog').length === 0) {
            $('<div id="loading-dialog" class="easyui-dialog" title="" style="width:300px;height:100px;padding:10px;"></div>').appendTo('body');
            $('#loading-dialog').dialog({
                iconCls: 'icon-load',
                resizable: true,
                modal: true,
                closable: false,
                closed: true,
                content: '<img src="' + hsjs.serv.urlPrefix + 'images/load.gif" style="width: 20px; height: 20px;"/><span style="color: #AAAAAA; font-size: 16px;">  正在执行中，请稍候...</span>'
            });
        }
        $('#loading-dialog').dialog('open').dialog('center');
    },
    closeLoading: function() {
        $('#loading-dialog').dialog('close');
    }
};

hsjs.serv = {
    urlPrefix: "../",
    result: {
        FATAL: -1,
        SUCCESS: 0,
        WARN: 1,
        ERROR: 2,
        QUESTION: 3
    },
    call: function(options) {
        // url地址
        var url;
        if (options.url) {
            url = options.url;
        } else {
            url = hsjs.serv.urlPrefix + options.controller + '/' + options.method + '.do';
        }
        // 请求参数
        var data = options.data;
        // 请求方式， POST/GET
        var type = options.type === undefined ? 'POST' : options.type;
        // 是否异步
        var async = options.async === undefined ? 'true' : options.async;
        // 返回数据类型 "xml" "html" "script" "json" "jsonp" "text"
        var dataType = options.dataType === undefined ? 'text' : options.dataType;
        // 请求返回后的回调函数
        var callback = options.callback;
        // 是否在得到结果时预先处理
        var isHandleResult = options.isHandleResult === undefined ? true : options.isHandleResult;
        // 是否打开遮罩层
        var isOpenLoadingDialog = options.isOpenLoadingDialog === undefined ? true : options.isOpenLoadingDialog;

        if (url === undefined || url === '') {
            return;
        }
        if (isOpenLoadingDialog) {
            hsjs.dialog.openLoading();
        }
        $.ajax({
            url: url,
            type: type,
            async: async,
            cache: false,
            data: data,
            dataType: dataType,
            success: function(result) {
                if (isOpenLoadingDialog) {
                    hsjs.dialog.closeLoading();
                }
                try {
                    if (isHandleResult) {
                        if (dataType === 'html' || dataType === 'script') {
                            if (callback) {
                                callback(result);
                            }
                        } else if (dataType === 'json' || dataType === 'text') {
                            if (dataType === 'text') {
                                result = eval('(' + result + ')');
                            }
                            if (result.rs === hsjs.serv.result.FATAL) {
                                hsjs.messager.error(result.message, function() {
                                    window.location = '/gysht';
                                });
                            } else if (result.rs === hsjs.serv.result.WARN) {
                                hsjs.messager.warn(result.message, function() {
                                    if (callback) {
                                        callback(result);
                                    }
                                });
                            } else if (result.rs === hsjs.serv.result.ERROR) {
                                hsjs.messager.error(result.message, function() {
                                    if (callback) {
                                        callback(result);
                                    }
                                });
                            } else {
                                if (callback) {
                                    callback(result);
                                }
                            }
                        } else {
                            if (callback) {
                                callback(result);
                            }
                        }
                    } else {
                        if (callback) {
                            callback(result);
                        }
                    }
                } catch (e) {
                    hsjs.messager.error(e.message);
                }
            },
            error: function() {
                if (isOpenLoadingDialog) {
                    hsjs.dialog.closeLoading();
                }
                hsjs.messager.error('错误');
            }
        });
    },
    getHtml: function(controller, method, data, callback, isHandleResult) {
        hsjs.serv.call({
            controller: controller,
            method: method,
            data: data,
            callback: callback,
            isHandleResult: isHandleResult,
            dataType: 'html'
        });
    },
    getScript: function(controller, method, data, callback, isHandleResult) {
        hsjs.serv.call({
            controller: controller,
            method: method,
            data: data,
            callback: callback,
            isHandleResult: isHandleResult,
            dataType: 'script'
        });

    },
    getJSON: function(controller, method, data, callback, async, isHandleResult) {
        hsjs.serv.call({
            controller: controller,
            method: method,
            data: data,
            callback: callback,
            async: async,
            isHandleResult: isHandleResult,
            dataType: 'json'
        });
    },
    getText: function(controller, method, data, callback, isHandleResult) {
        hsjs.serv.call({
            controller: controller,
            method: method,
            data: data,
            callback: callback,
            isHandleResult: isHandleResult,
            dataType: 'text'
        });
    }
};