//菜单
var menudata = [], isExsit = [];
var loading = false;
menudata.push({title: '合同模块', icon: 'icon-customers', selected: true, id: 'htgl', pid: null});
menudata.push({title: '合同管理', icon: '', id: 'contract', pid: 'htgl', url: "../contract/toList.do"});

//FWJ增加票折月结功能
menudata.push({title: '票折管理', icon: 'icon-old-versions', selected: false, id: 'pz', pid: null});
menudata.push({title: '启用时间设置', icon: '', id: 'month', pid: 'pz', url: "../month/toMonthly.do"});
menudata.push({title: '月度结算', icon: '', id: 'yj', pid: 'pz', url: "../month/toYj.do"});
menudata.push({title: '月结费用明细表', icon: '', id: 'bbcx', pid: 'pz', url: "../month/toBbcx.do"});

menudata.push({title: '报表管理', icon: 'icon-export', id: 'bbgl', pid: null});
menudata.push({title: '结算费用查询', icon: '', id: 'repcost', pid: 'bbgl', url: "../report/toCost.do"});

menudata.push({title: '基础设置', icon: 'icon-config', id: 'jcsz', pid: null});
menudata.push({title: '系统设置', icon: '', id: 'bzsz', pid: 'jcsz', url: "../basics/toList.do"});
menudata.push({title: '同步信息查看', icon: '', id: 'tbxxck', pid: 'jcsz', url: '../basics/toTBXXCK.do'});
$(function() {
    setInterval('AutoScroll("#scrollDiv")', 5000);

    for (var i = 0; i < menudata.length; i++) {
        if (menudata[i].pid === null) {
            $('#menu').accordion('add', {
                title: menudata[i].title,
                iconCls: menudata[i].icon,
                content: addAccrodionContent(menudata[i]),
                selected: true
            });
        }
    }

    for (var j = 0; j < menudata.length; j++) {
        if (menudata[j].pid === null) {
            $('#menu').accordion('select', menudata[j].title);
        }
    }

    for (var j = 0; j < menudata.length; j++) {
        if (menudata[j].pid === null && menudata[j].selected) {
            $('#menu').accordion('select', menudata[j].title);
        }
    }

    $('#menu').find('ul[id^=menuTree_]').each(function() {
        var id = $(this).attr('id');
        var menuObj = findMenuObjById(menudata, id.split('_')[1]);
        $(this).tree({
            data: createNode(menuObj),
            onClick: function(node) {
                $(this).tree('toggle', node.target);
                for (var j = 0; j < menudata.length; j++) {
                    if (node.text === menudata[j].title) {
                        addTab(j);
                    }
                }
            }
        });
    });

});
function addAccrodionContent(obj) {
    return '<div class="innerTree"><ul id="menuTree_' + obj.id + '"></ul></div>';
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
                children: temp
            };
            json_tree.push(child);
        }
    }
    return json_tree;
}
function AutoScroll(obj) {
    $(obj).find("ul:first").animate({
        marginTop: "-25px"
    }, 500, function() {
        $(this).css({
            marginTop: "10px"
        }).find("li:first").appendTo(this);
    });
}

function addTab(i) {
    if (menudata[i].url === undefined || menudata[i].url === null) {
        return;
    }
    if (loading) {
        d_info('刷新过快,请稍等片刻!');
        return;
    }
    if ($('#mainTab').tabs('exists', menudata[i].title)) {
        $('#mainTab').tabs('select', menudata[i].title);
        var t = $('#mainTab').tabs('getTab', menudata[i].title);
        t.panel('refresh');
        loading = true;
        setTimeout(function() {
            loading = false;
        }, 1000);
        return;
    }

    $('#mainTab').tabs('add', {
        title: menudata[i].title,
        iconCls: 'icon-file',
        closable: true,
        href: menudata[i].url,
        onResize: function() {
            resizeLayout(menudata[i].id);
        }
    });

    loading = true;
    setTimeout(function() {
        loading = false;
    }, 1000);
}
