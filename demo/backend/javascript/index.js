var pwd_form;
var profile_form;
var pwd_dialog;
var profile_dialog;
var _waiting;

$(document).ready(function () {
    $('#user-nav .nav li a').on('click', function (o) {
        var obj = $(this);
        $('#user-nav .nav li').removeClass('active');
        obj.closest('li').addClass('active');
        $.post("/index/getMenu", {pid: obj.attr('data-id')}, function (data) {
            var html=baidu.template('slideTemplate',data);
            $('#sidebar ul').html(html);
            $('.submenu > a').click(function (e) {
                e.preventDefault();
                var submenu = $(this).siblings('ul');
                var li = $(this).parents('li');
                var submenus = $('#sidebar li.submenu ul');
                var submenus_parents = $('#sidebar li.submenu');
                if (li.hasClass('open')) {
                    submenu.slideUp();
                    li.removeClass('open');
                } else {
                    submenus.slideUp();
                    submenu.slideDown();
                    submenus_parents.removeClass('open');
                    li.addClass('open');
                }
            });
            var ul = $('#sidebar > ul');
            $('#sidebar > a').click(function (e) {
                e.preventDefault();
                var sidebar = $('#sidebar');
                if (sidebar.hasClass('open')) {
                    sidebar.removeClass('open');
                    ul.slideUp(250);
                } else {
                    sidebar.addClass('open');
                    ul.slideDown(250);
                }
            });
            $('#sidebar li.submenu ul li a').click(function (e) {
                var menu = $(this);
                $('#sidebar li.submenu ul li').removeClass('active');
                menu.closest('li').addClass('active');
                $('#content_page').attr('src', menu.attr('link'));
                _waiting = $.ligerDialog.waitting('正在努力打开...');
                $('#content_page').on('load', function () {
                    _waiting.close();
                })
            });
        },'json');

    });
    $('#user-nav .nav li a :first').click();
    $("#layout").ligerLayout({
        leftwidth: 220,
        topheight: 130,
        bottomheight: 40,
        allowleftresize: false,
        allowtopresize: false,
        allowbottomresize: false,
        allowleftcollapse: false
    });
    pwd_form = $('#pwd_form').ligerForm({
        inputwidth: 360,
        labelwidth: 100,
        space: 10,
        validate: true,
        righttoken: "&nbsp;",
        labelalign: "right",
        align: "left",
        fields: [
            {
                name: "old_password",
                newline: true,
                type: "password",
                display: "当前密码",
                width: 150,
                aftercontent: '',
                validate: {required: true},
                editor: {width: 150}
            },
            {
                name: "password",
                newline: true,
                type: "password",
                display: "新的密码",
                width: 150,
                aftercontent: '',
                validate: {required: true},
                editor: {width: 150}
            },
            {
                name: "re_password",
                newline: true,
                type: "password",
                display: "重复密码",
                width: 150,
                aftercontent: '',
                validate: {required: true},
                editor: {width: 150}
            }
        ]
    });
    profile_form = $('#profile_form').ligerForm({
        inputwidth: 500,
        labelwidth: 100,
        space: 10,
        validate: true,
        righttoken: "&nbsp;",
        labelalign: "right",
        align: "left",
        fields: [
            {
                name: "realname",
                newline: true,
                type: "textbox",
                display: "姓名",
                width: 250,
                aftercontent: '',
                validate: {required: true},
                editor: {width: 250}
            },
            {
                name: "avatar",
                newline: true,
                type: "image",
                display: "头像",
                width: 250,
                aftercontent: '',
                validate: {required: true},
                editor: {
                    width: 250,
                    height: 50,
                    preview: true,
                    url: "/Uploader/?action=uploadimage&handle=thumb&width=100&height=100"
                }
            },
            {
                name: "comment",
                newline: true,
                type: "textarea",
                display: "备注",
                width: 250,
                aftercontent: '',
                validate: {required: false},
                editor: {width: 250}
            }
        ]
    });
    pwd_dialog = $.ligerDialog({
        title: '修改密码',
        width: '380',
        height: '220',
        target: $("#pwd_dialog"),
        buttons: [{
            text: "确认修改",
            icon: "icon-edit",
            onclick: pwdsubmit
        }]
    });
    profile_dialog = $.ligerDialog({
        title: '修改资料',
        width: '500',
        height: '360',
        target: $("#profile_dialog"),
        buttons: [{
            text: "确认修改",
            icon: "icon-edit",
            onclick: profilesubmit
        }]
    });
    pwd_dialog.hide();
    profile_dialog.hide();
});
//提交表单
function pwdsubmit() {
    if (pwd_form.valid()) {
        var form_data = pwd_form.getData();
        $.post($('#pwd_form').attr('action'), form_data, function (response) {
            if (response.status == 0) {
                $.ligerDialog.error(response.message);
            } else {
                $.ligerDialog.success(response.message);
                pwd_dialog.hide();
            }
        });
    } else {
        $.ligerDialog.error("表单没有通过验证,请检查红色标记的表单项");
    }
}

function profilesubmit() {
    if (profile_form.valid()) {
        var form_data = profile_form.getData();
        $.post($('#profile_form').attr('action'), form_data, function (response) {
            if (response.status == 0) {
                $.ligerDialog.error(response.message);
            } else {
                $.ligerDialog.success(response.message);
                profile_dialog.hide();
            }
        });
    } else {
        $.ligerDialog.error("表单没有通过验证,请检查红色标记的表单项");
    }
}
function open_profile() {
    $.getJSON("/index.php?_url=/index/changeProfile", {}, function (json) {
        profile_form.setData(json);
        $('#avatar_url').css('background-image', 'url(' + json.avatar + ')');
        profile_dialog.show();
    });
}