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
    var form1_defaults = {operation_area_counter:'0'};
    var win;

    $.ligerDefaults.Grid.formatters['formatStatus'] = function (data, column) {
        var list = {regular: '正常',pending:'待审核',deny:'禁用'};
        return list[data] ? list[data] : data;
    };


    //添加操作
    function add() {
        form1.setData(form1_defaults);
        $('#form1').attr('action', "<?= $this->url->get('Port/savePort') ?>");
        win._setTitle('添加港口');
        win.show();
    }


    //编辑操作
    function edit() {
        var row = datagrid.getSelected();
        if (!row) {
            $.ligerDialog.error('请选择要编辑的港口');
            return false;
        } else {
            form1.setData(row);
        }
        $('#form1').attr('action', "<?= $this->url->get('Port/savePort') ?>");
        win._setTitle('编辑 [' + row.name + '] 港口');
        win.show();
    }

    //删除操作
    function del() {
        var row = datagrid.getSelected();
        if (!row) {
            $.ligerDialog.error('请选择要删除的港口');
            return false;
        } else {
            $.ligerDialog.confirm('确定要删除 [ ' + row.name + ' ] 港口?', function (type) {
                if (type) {
                    $('#form1').ajaxForm("<?= $this->url->get('port/deletePort') ?>", {id: row.id}, function (response) {
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
            url: "<?= $this->url->get('Port/PortList') ?>",
            columns: [{
                display: "编号",
                name: "id",
                align: "center",
                width: 40,
                minwidth: 40,
                frozen: true
            }, {
                display: "港口名称",
                name: "name",
                align: "center",
                width: 150,
                minwidth: 60
            }, {
                display: "是否启用",
                name: "status",
                align: "center",
                width: 80,
                minwidth: 60,
                render:function(rowdata,index,value){
                    var data = {0:'未启用',1:'已启用'};
                    return data[value] ? data[value] : '0';
                }
            }, {
                display: "作业区总数",
                name: "operation_area_counter",
                align: "center",
                width: 80,
                minwidth: 60,
                render:function(rowdata,index,value){
                    var data = {site:'自有港口',league:'广告联盟'};
                    return data[value] ? data[value] : '未知';
                }
            }, {
                display: "创建时间",
                name: "c_time",
                align: "center",
                width: 150,
                minwidth: 60
            }, {
                display: "修改时间",
                name: "u_time",
                align: "center",
                width: 150,
                minwidth: 60
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
                    id: 'name',
                    name: 'name',
                    newline: true,
                    type: 'textbox',
                    display: '港口名',
                    aftercontent: '',
                    width: 300,
                    validate: {required: true},
                    editor: {
                        width: 300
                    }
                }, {
                    id: 'status',
                    name: 'status',
                    newline: true,
                    type: 'combobox',
                    display: '是否启用',
                    aftercontent: '',
                    width: 100,
                    validate: {required: true},
                    editor: {
                        width: 100,
                        cancelable:false,
                        data: [{id: '0', text: '不启用'}, {id: '1', text: '启用'}]
                    }
                },{
                    id: 'order',
                    name: 'order',
                    newline: true,
                    type: 'textbox',
                    display: '排序',
                    aftercontent: '',
                    width: 300,
                    validate: {required: true},
                    editor: {
                        width: 300
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
            title: "港口管理",
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