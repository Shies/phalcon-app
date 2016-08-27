var g_isLoading = false;

//获取树节点数
function GetNode(type) {
    var node = $('#treemenu').tree('getChecked');
    var chilenodes = '';
    var parantsnodes = '';
    var prevNode = '';
    for (var i = 0; i < node.length; i++) {

        if ($('#treemenu').tree('isLeaf', node[i].target)) {
            chilenodes += node[i].text + ',';

            var pnode = $('#treemenu').tree('getParent', node[i].target);
            if (prevNode !== pnode.text) {
                parantsnodes += pnode.text + ',';
                prevNode = pnode.text;
            }
        }
    }
    chilenodes = chilenodes.substring(0, chilenodes.length - 1);
    parantsnodes = parantsnodes.substring(0, parantsnodes.length - 1);

    if (type === 'child') {
        return chilenodes;
    }
    else {
        return parantsnodes;
    }
}
function tabClose() {
    /*为选项卡绑定右键*/
    $(".tabs-inner").unbind('contextmenu');
    $(".tabs-inner").bind('contextmenu', function(e) {
        $('#mm').menu('show', {
            left: e.pageX,
            top: e.pageY
        });
        var subtitle = $(this).children(".tabs-closable").text();
        $('#mm').data("currtab", subtitle);
        $('#tt').tabs('select', subtitle);
        return false;
    });
}

//绑定右键菜单事件
function tabCloseEven() {
    $('#mm').menu({
        onClick: function(item) {
            closeTab(item.id);
        }
    });
    return false;
}

function closeTab(action) {
    var alltabs = $('#mainTab').tabs('tabs');
    var currentTab = $('#mainTab').tabs('getSelected');
    var allTabtitle = [];
    $.each(alltabs,
            function(i, n) {
                allTabtitle.push($(n).panel('options').title);
            });
    switch (action) {
        case "refresh":
            //            showLoading('diy-loading', $('div.tabs-panels'));
            if (currentTab && currentTab.panel('options').title !== '首页') {
                currentTab.panel('refresh');
            }
            break;
        case "close":
            var currtab_title = currentTab.panel('options').title;
            if (currtab_title && currtab_title !== '首页') {
                $('#mainTab').tabs('close', currtab_title);
            }
            break;
        case "closeall":
            $.each(allTabtitle,
                    function(i, n) {
                        if (n !== '首页') {
                            $('#mainTab').tabs('close', n);
                        }
                    });
            break;
        case "closeother":
            var currtab_title = currentTab.panel('options').title;
            $.each(allTabtitle,
                    function(i, n) {
                        if (n !== currtab_title && n !== '首页') {
                            $('#mainTab').tabs('close', n);
                        }
                    });
            break;
        case "exit":
            $('#closeMenu').menu('hide');
            break;
    }
}


function addAccrodionContent(obj) {
    return '<div class="innerTree"><ul id="menuTree_' + obj.id + '"></ul></div>';
}

function createNode(obj) {
    var json_tree = [];
    for (var i = 0; i < menudata.length; i++) {
        if (menudata[i].pid === obj.id) {
            var temp = createNode(menudata[i]);
            var state = 'closed';
            if (temp.length === 0) {
                temp = null;
                state = null;
            }
            var child = {
                text: menudata[i].title,
                state: state,
                children: temp};
            json_tree.push(child);
        }
    }
    return json_tree;
}

function findMenuObjByTitle(data, title) {
    var rs = null;
    for (var i = 0; i < data.length; i++) {
        if (title === data[i].title) {
            rs = data[i];
        }
    }
    return rs;
}

function findMenuObjById(data, id) {
    var rs = null;
    for (var i = 0; i < data.length; i++) {
        if (id === data[i].id) {
            rs = data[i];
        }
    }
    return rs;
}

function addTabs(id, title, url) {
    if (url === undefined || url === null) {
        return;
    }
    if (g_isLoading) {
        d_info('刷新过快,请稍等片刻!');
        return;
    }
    if ($('#mainTab').tabs('exists', title)) {
        $('#mainTab').tabs('select', title);
        var t = $('#mainTab').tabs('getTab', title);
        if (t.panel('options').title !== '基础设置') {
            t.panel('refresh');
        }
        g_isLoading = true;
        setTimeout(function() {
            g_isLoading = false;
        }, 1000);
        return;
    }
    $('#mainTab').tabs('add', {
        title: title,
        closable: true,
        href: url,
        onResize: function() {
            resizeLayout(id);
        }
    });
    g_isLoading = true;
    setTimeout(function() {
        g_isLoading = false;
    }, 1000);

    tabClose();
    tabCloseEven();
}

function closeAll() {
    d_confirm('确认关闭所有标签页?', function(r) {
        if (!r)
            return;
        for (var i = 0; i < menudata.length; i++) {
            if ($('#mainTab').tabs('exists', menudata[i].title)) {
                $('#tt').tabs('select', '模块树');
                clearPage(menudata[i].id);
                $('#mainTab').tabs('close', menudata[i].title);
            }
        }
    });
}

function f_logout() {
    hsjs.messager.confirm('您确定要退出本次登录吗?', function(r) {
        if (r) {
            window.location = '../login/logout.do';
        }
    });
}
function chooseMenu(index) {
    var tempWindow = $('<div id="' + menudata[index].id + '"></div>');
    $('body').append(tempWindow);
    $("#" + menudata[index].id).window({
        title: menudata[index].title,
        width: 400,
        modal: true,
        shadow: false,
        closed: false,
        maximizable: true,
        collapsible: false,
        minimizable: false,
        resizable: true,
        maximized: false,
        height: 160,
        icon: null,
        href: menudata[index].url,
        onClose: function() {
            $("#" + menudata[index].id).window('destroy');
        },
        onResize: function() {
            resizeLayout(menudata[index].id);
        }
    });
}
//日期
function e_datebox(id, type, pageId) {
    var t;
    if (type === 'bg')
        t = "00:00:00";
    if (type === 'ov')
        t = "23:59:59";
    $("#" + pageId).find('#' + id).datebox({
        formatter: function(date) {
            var y = date.getFullYear();
            var m = date.getMonth() + 1;
            if (m < 10)
                m = '0' + m;
            var d = date.getDate();
            if (d < 10)
                d = '0' + d;
            function formatNumber(value) {
                return (value < 10 ? '0' + value : value);
            }
            return y + '-' + m + '-' + d + ' ' + t;
        },
        parser: function(s) {
            var t = Date.parse(s);
            if (!isNaN(t)) {
                return new Date(t);
            } else {
                return new Date();
            }
        },
        width: 140,
        editable: false,
        required: true
    });
}
function d_datebox(id, pageId, T, width) {
    var buttons = $.extend([], $.fn.datebox.defaults.buttons);
    buttons.splice(1, 0, {
        text: '清除',
        handler: function() {
            $("#" + pageId).find('#' + id).datebox('setValue', '').datebox('hidePanel');
        }
    });
    $("#" + pageId).find('#' + id).datebox({
        formatter: function(date) {
            var y = date.getFullYear();
            var m = date.getMonth() + 1;
            if (m < 10)
                m = '0' + m;
            var d = date.getDate();
            if (d < 10)
                d = '0' + d;
            function formatNumber(value) {
                return (value < 10 ? '0' + value : value);
            }
            return y + '-' + m + '-' + d;
        },
        parser: function(s) {
            var t = Date.parse(s);
            if (!isNaN(t)) {
                return new Date(t);
            } else {
                return new Date();
            }
        },
        width: width === undefined || width === null ? 146 : width,
        editable: false,
        required: T === true ? true : false,
        buttons: buttons
    });
}
function changeMenu() {
    if ($('#mu1').attr('checked')) {
        setCookie('menu-style', 0);
        setCookie('jspId', 0);
        mainMenu();
    } else {
        setCookie('menu-style', 1);
        setCookie('jspId', 1);
        mainMenu();
    }
}


function mainMenu() {
    var s = getCookie('menu-style');
    if ((s === null || s === 0)) {
        window.location.href = 'main.do?type=0';
        return;
    }
    if (s === 1) {
        window.location.href = 'main.do?type=1';
        return;
    }
}
function exit() {
    $.messager.confirm('退出系统', '确认退出系统?', function(rs) {
        if (rs) {
            window.location.href = '../login/loginOut.do';
        }
    });
}
function setCookie(name, value) {//两个参数，一个是cookie的名子，一个是值
    var Days = 30; //此 cookie 将被保存 30 天
    var exp = new Date();    //new Date("December 31, 9998");
    exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
    document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString();
}

function getCookie(name) {//取cookies函数        
    var arr = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
    if (arr !== null)
        return unescape(arr[2]);
    return null;
}
function isEmpty(str) {
    if (typeof (str) === "undefined")
        return true;
    else
        return ((str === null) || (str.length === 0) || (str === ''));
}
function isNotEmpty(str) {
    if (typeof (str) === "undefined")
        return false;
    else
        return ((str !== null) || (str.length !== 0) || (str !== ''));
}

function f_open(pageId, id, url, title, icon, width, heigh, isDrag) {
    if (isDrag === undefined || isDrag === null) {
        isDrag = true;
    }
    var div = '<div id="' + id + '"></div>';
    $("#" + pageId).append(div);
    $("#" + pageId).find('#' + id).window({
        title: title,
        width: width,
        modal: true,
        shadow: true,
        closed: false,
        maximizable: false,
        collapsible: false,
        minimizable: false,
        resizable: false,
        draggable: isDrag,
        height: heigh,
        icon: icon,
        href: url,
        onClose: function() {
            $(this).window('destroy');
        },
        onResize: function() {
            resizeLayout(id);
        }
    }).data().window.shadow.append('<iframe width="100%" height="100%" frameborder="0" scrolling="no"></iframe>');
}


function f_open_p(pageId, id, url, title, icon, width, heigh, isDrag) {
    if (isDrag === undefined || isDrag === null) {
        isDrag = true;
    }
    var div = '<div id="' + id + '"></div>';
    $("#" + pageId).append(div);
    $("#" + pageId).find('#' + id).window({
        title: title,
        width: width,
        modal: true,
        shadow: true,
        closed: false,
        maximizable: false,
        collapsible: false,
        minimizable: false,
        resizable: false,
        draggable: isDrag,
        height: heigh,
        icon: icon,
        href: url,
        onClose: function() {
            var str = url.split('=');
            var uuid = str[str.length - 1];
            $.post('../month/dev.do?uuid=' + uuid);
            $(this).window('destroy');
        },
        onResize: function() {
            resizeLayout(id);
        }
    }).data().window.shadow.append('<iframe width="100%" height="100%" frameborder="0" scrolling="no"></iframe>');
}

function f_open_t(pageId, id, url, title, icon, width, heigh, uuid, isDrag) {
    if (isDrag === undefined || isDrag === null) {
        isDrag = true;
    }
    var div = '<div id="' + id + '"></div>';
    $("#" + pageId).append(div);
    $("#" + pageId).find('#' + id).window({
        title: title,
        width: width,
        modal: true,
        shadow: true,
        closed: false,
        maximizable: false,
        collapsible: false,
        minimizable: false,
        resizable: false,
        draggable: isDrag,
        height: heigh,
        icon: icon,
        href: url,
        onClose: function() {
            $.post('../month/dev.do?uuid=' + uuid);
            $(this).window('destroy');
        },
        onResize: function() {
            resizeLayout(id);
        }
    }).data().window.shadow.append('<iframe width="100%" height="100%" frameborder="0" scrolling="no"></iframe>');
}

function formatJSON(src) {
    if (src === null || src === undefined) {
        return null;
    }
    return eval('(' + src + ')');
}

function openLoadindPage() {
    $('#loading').dialog('open');
}

function closeLoadingPage() {
    $('#loading').dialog('close');
}

function getnum(f, c) {
    var t = Math.pow(10, c);
    return Math.round(f * t) / t;
}
