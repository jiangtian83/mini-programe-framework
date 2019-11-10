function postData(url, data) {
    $.post(url, data, function (data) {
        layer.closeAll();
        data = JSON.parse(data);
        if (!data.type) {
            layer.msg('服务器错误!');
            return false;
        }
        if (data.type == 'success') {
            layer.load();
            setTimeout(function () {
                var url_jump = data.redirect;
                window.location.href = url_jump;
            }, 1000)
        }
        layer.msg(data.message);
    });
}

function postData2(url, data) {
    $.post(url, data, function (data) {
        layer.closeAll();
        data = JSON.parse(data);
        if (!data.type) {
            layer.msg('服务器错误!');
            return false;
        }
        if (data.type == 'success') {
        }
        layer.msg(data.message);
    });
}

function postDataReturn(url, data) {
    $.ajaxSettings.async = false;
    $.post(url, data, function (data) {
        layer.closeAll();
        data = JSON.parse(data);
        if (!data.type) {
            layer.msg('服务器错误!');
            return false;
        }
        if (data.type == 'success') {
        }
        good_id_edit = data.message;
    });

    $.ajaxSettings.async = true;
}

function postDataGoBack(url, data) {
    $.post(url, data, function (data) {
        layer.closeAll();
        data = JSON.parse(data);
        if (!data.type) {
            layer.msg('服务器错误!');
            return false;
        }
        if (data.type == 'success') {
            layer.load();
            setTimeout(function () {
                window.location.href = document.referrer;
            }, 1000)
        }
        layer.msg(data.message);
    });
}

function postDataHistoryBack(url, data) {
    $.post(url, data, function (data) {
        layer.closeAll();
        data = JSON.parse(data);
        if (!data.type) {
            layer.msg('服务器错误!');
            return false;
        }
        if (data.type == 'success') {
            layer.load();
            setTimeout(function () {
                window.history.back();
            }, 1000)
        }
        layer.msg(data.message);
    });
}


//  禁用回车时间
$(window).keydown( function(e) {
    var key = window.event?e.keyCode:e.which;
    if(key.toString() == "13"){
        return false;
    }
});


function postDataReload(url, data) {
    $.post(url, data, function (data) {
        layer.closeAll();
        data = JSON.parse(data);
        if (!data.type) {
            layer.msg('服务器错误!');
            return false;
        }
        if (data.type == 'success') {
            layer.load();
            setTimeout(function () {
                window.location.reload();
            }, 1000)
        }
        layer.msg(data.message);
    });
}