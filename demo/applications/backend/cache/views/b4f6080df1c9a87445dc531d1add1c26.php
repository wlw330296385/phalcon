<div class="container-fluid">
    <div class="widget-box">
        <div class="widget-title"><span class="icon"> <i class="icon-th"></i> </span>
            <h5><?= $title ?></h5>
        </div>
        <div class="widget-content">
            <div id='datagrid'></div>
            <div id='win'>
                <form id='form1' action="" method="post"></form>
            </div>

        </div>
    </div>
</div>

<script>
    var datagrid;
    var form1;
    var form1_defaults = {username:'',usertype:'person',login_type:'normal',birthday:'',origin:'site',password:'',mobile:'',email:'',status:'regular'};
    var win;

    $.ligerDefaults.Grid.formatters['formatStatus'] = function (data, column) {
        var list = {regular: '正常',pending:'待审核',deny:'禁用'};
        return list[data] ? list[data] : data;
    };


    //添加操作
    function add() {
        form1.setData(form1_defaults);
        $('#form1').attr('action', "<?= $this->url->get('User/add') ?>");
        win._setTitle('添加用户');
        win.show();
    }


    //编辑操作
    function edit() {
        var row = datagrid.getSelected();
        if (!row) {
            $.ligerDialog.error('请选择要编辑的用户');
            return false;
        } else {
            console.info(form1);
            form1.setData(row);
        }
        $('#form1').attr('action', "<?= $this->url->get('User/edit') ?>");
        win._setTitle('编辑 [' + row.username + '] 用户');
        win.show();
    }

    //删除操作
    function del() {
        var row = datagrid.getSelected();
        if (!row) {
            $.ligerDialog.error('请选择要删除的用户');
            return false;
        } else {
            $.ligerDialog.confirm('确定要删除 [ ' + row.username + ' ] 用户?', function (type) {
                if (type) {
                    $('#form1').ajaxForm("<?= $this->url->get('port/PortList') ?>", {id: row.id}, function (response) {
                        if (response.status == 0) {
                            $.ligerDialog.error(response.message);
                        } else {
                            $.ligerDialog.success(response.message);
                            datagrid.reload();
                        }
                    });
                }
            });
        }
    }



    //提交表单
    function submitform() {
        if (form1.valid()) {
            var form_data = form1.getData();
            $.post($('#form1').attr('action'), form_data, function (response) {
                if (response.status == 0) {
                    $.ligerDialog.error(response.message);
                } else {
                    $.ligerDialog.success(response.message);
                    datagrid.reload();
                    win.hide();
                }
            });
        } else {
            $.ligerDialog.error("表单没有通过验证,请检查红色标记的表单项");
        }
    }

    $(function () {
        datagrid = $('#datagrid').ligerGrid({
            width: "100%",
            height: "100%",
            url: "<?= $this->url->get('User/index') ?>",
            columns: [{
                display: "编号",
                name: "id",
                align: "center",
                width: 40,
                minwidth: 40,
                frozen: true
            }, {
                display: "用户名",
                name: "username",
                align: "center",
                width: 150,
                minwidth: 60
            }, {
                display: "账号类型",
                name: "usertype",
                align: "center",
                width: 80,
                minwidth: 60,
                render:function(rowdata,index,value){
                    var data = {person:'个人',enterprise:'企业'};
                    return data[value] ? data[value] : '未知';
                }
            }, {
                display: "用户来源",
                name: "origin",
                align: "center",
                width: 80,
                minwidth: 60,
                render:function(rowdata,index,value){
                    var data = {site:'自有用户',league:'广告联盟'};
                    return data[value] ? data[value] : '未知';
                }
            }, {
                display: "手机号",
                name: "mobile",
                align: "center",
                width: 150,
                minwidth: 60
            }, {
                display: "邮箱",
                name: "email",
                align: "center",
                width: 150
            }, {
                display: "可用余额",
                name: "money",
                align: "center",
                width: 80
            }, {
                display: "锁定金额",
                name: "money_freeze",
                align: "center",
                width: 80
            }, {
                display: "会员积分",
                name: "point",
                align: "center",
                width: 80
            },{
                display: "注册时间",
                name: "register_time",
                align: "center",
                type:'date',
                format:'Y-m-d H:i',
                width: 150
            },{
                display: "登陆时间",
                name: "login_time",
                align: "center",
                type:'date',
                format:'Y-m-d H:i',
                width: 150
            }, {
                display: "状态",
                name: "status",
                align: "center",
                type:'formatStatus',
                width: 80
            }],
            frozen: true,
            toolbar: {
                items: [
                    {
                        text: "添加",
                        icon: "icon-plus",
                        click: add
                    },
                    {
                        text: "编辑",
                        icon: "icon-edit",
                        click: edit
                    },
                    {
                        text: "删除",
                        icon: "icon-remove",
                        click: del
                    }
                ]
            }
        });

        form1 = $('#form1').ligerForm({
            inputwidth: 350,
            labelwidth: 120,
            space: 10,
            validate: true,
            righttoken: "&nbsp;",
            labelalign: "right",
            align: "left",
            fields: [
                {
                    id: "id", name: "id", newline: false, type: "hidden", display: "id"
                }, {
                    id: 'username',
                    name: 'username',
                    newline: true,
                    type: 'textbox',
                    display: '用户名',
                    aftercontent: '',
                    width: 300,
                    validate: {required: true},
                    editor: {
                        width: 300
                    }
                }, {
                    id: 'usertype',
                    name: 'usertype',
                    newline: true,
                    type: 'combobox',
                    display: '账号类型',
                    aftercontent: '',
                    width: 100,
                    validate: {required: true},
                    editor: {
                        width: 100,
                        cancelable:false,
                        data: [{id: 'person', text: '个人'}, {id: 'enterprise', text: '企业'}]
                    }
                }, {
                    id: 'login_type',
                    name: 'login_type',
                    newline: true,
                    type: 'combobox',
                    display: '登陆保护类型',
                    aftercontent: '',
                    width: 200,
                    validate: {required: true},
                    editor: {
                        width: 200,
                        cancelable:false,
                        data: [{id: 'normal', text: '关闭保护'}, {id: 'mobile', text: '验证手机'}, {id: 'ip', text: 'IP变更验证手机'}, {id: 'tryerror', text: '连续3次登陆失败验证手机'}]
                    }
                }, {
                    id: "birthday",
                    name: "birthday",
                    newline: true,
                    type: "date",
                    display: "会员生日",
                    aftercontent: "",
                    width: 120,
                    validate: {required: false},
                    editor: {
                        format:'yyyy-MM-dd',
                        width: 120
                    }
                }, {
                    id: "origin",
                    name: "origin",
                    newline: true,
                    type: "combobox",
                    display: "用户来源",
                    aftercontent: "",
                    width: 100,
                    validate: {required: true},
                    editor: {
                        width: 100,
                        initvalue: 'site',
                        cancelable: false,
                        data: [{id: 'site', text: '自有用户'}, {id: 'league', text: '广告联盟'}]
                    }
                }, {
                    id: 'password',
                    name: 'password',
                    newline: true,
                    type: 'password',
                    display: '密码',
                    aftercontent: '留空不修改',
                    width: 200,
                    validate: {required: false},
                    editor: {
                        width: 200
                    }
                }, {
                    id: 'mobile',
                    name: 'mobile',
                    newline: true,
                    type: 'textbox',
                    display: '手机号',
                    aftercontent: '',
                    width: 200,
                    validate: {required: true},
                    editor: {
                        width: 200
                    }
                }, {
                    id: 'email',
                    name: 'email',
                    newline: true,
                    type: 'textbox',
                    display: '邮箱地址',
                    aftercontent: '',
                    width: 200,
                    validate: {required: true},
                    editor: {
                        width: 200
                    }
                }, {
                    id: "status",
                    name: "status",
                    newline: true,
                    type: "combobox",
                    display: "状态",
                    aftercontent: "",
                    width: 80,
                    validate: {required: true},
                    editor: {
                        width: 150,
                        initvalue: 'pending',
                        cancelable: false,
                        data: [{id: 'regular', text: '正常'}, {id: 'pending', text: '待审'}, {id: 'deny', text: '禁用'}]
                    }
                }
            ]
        });

        win = $.ligerDialog({
            width: 500,
            height: 500,
            target: $('#win'),
            modal: true,
            show: false,
            title: "用户管理",
            slide: true,
            buttons: [
                {
                    text: "确认提交",
                    icon: "icon-edit",
                    onclick: submitform
                }
            ]
        });
        win.hide();
    });
</script>