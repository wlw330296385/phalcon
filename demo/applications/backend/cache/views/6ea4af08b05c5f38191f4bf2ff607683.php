<div class="container-fluid">
    <div class="widget-box">
        <div class="widget-title"> <span class="icon"> <i class="icon-th"></i> </span>
            <h5>查看菜单列表</h5>
        </div>
        <div class="widget-content">
            <div id="tree_datagrid"></div>
        </div>
    </div>
</div>

<!---菜单表单-->
<div id="menu_dialog" style="display:none;width:458px;height:350px;">
    <form id="menu_form" action="" method="post" class="form-horizontal"></form>
</div>


<script>
    var module_list = <?= $module_list ?>;
    var action_list = <?= $action_list ?>;
    var tree_datagrid;
    var menu_dialog;
    var menu_form;
    var menu;

    $.ligerDefaults.Grid.formatters['icon'] = function (data, column){
        return "<div class='icon "+ data + "'></div>";
    };

    $.ligerDefaults.Grid.formatters['hidden'] = function (data, column){
        return (data == 'off') ? '显示' : '隐藏';
    };
    $.ligerDefaults.Grid.formatters['status'] = function (data, column){
        return (data == 'off') ? '禁用' : '正常';
    };

    $.ligerDefaults.Grid.formatters['get_module_name'] = function (data, column){
        return module_list[data];
    };

    $.ligerDefaults.Grid.formatters['get_action_name'] = function (data, column){
        return action_list[data];
    };

    $.ligerDefaults.Grid.formatters['url_type'] = function (data, column){
        var type_list = {directory:'菜单目录',module:'模块连接',link:'菜单连接'};
        return type_list[data];
    };

    //添加
    function add(){
        menu_dialog._setTitle('添加菜单');
        menu_dialog.show();
        $('#menu_form').clearForm();
        menu_form.setData({status:'on',hidden:'off',sort:'0',type:'directory'});
        if(tree_datagrid.getSelected()){
            var row = tree_datagrid.getSelected();
            menu_form.getEditor('pid').setValue(row.id);
            menu_form.getEditor('pid').setText(menu_form.getEditor('pid').getTree().getSelected().data.title);
        }
        $('#menu_form').attr('action',"<?= $this->url->get('menu/add') ?>");
        $('#icon_show').attr('class','help-inline icon');
    }

    //编辑
    function edit(){
        var row = tree_datagrid.getSelected();
        if(!row){
            $.ligerDialog.error('请选择要编辑的菜单');
            return false;
        }else{
            menu_dialog._setTitle('编辑菜单');
            menu_dialog.show();
            menu_form.setData(row);
            if(menu_form.getEditor('pid').getTree().getSelected()){
                var title = menu_form.getEditor('pid').getTree().getSelected().data.title;
                menu_form.getEditor('pid').setText(title);
            }
            $('#menu_form').attr('action',"<?= $this->url->get('menu/edit') ?>");
            $('#icon_show').attr('class','help-inline icon '+ row.icon);
        }
    }

    //删除
    function del(){
        var row = tree_datagrid.getSelected();
        if(!row){
            $.ligerDialog.error('请选择要删除的菜单');
            return false;
        }else{
            $.ligerDialog.confirm('确定要删除 [ '+ row.title +' ] 菜单?', function (type) {
                if(type){
                    $('#menu_form').ajaxForm("<?= $this->url->get('menu/delete') ?>",{id:row.id},function(response){
                        if(response.status == 0){
                            $.ligerDialog.error(response.message);
                        }else{
                            $.ligerDialog.success(response.message);
                            tree_datagrid.reload();
                        }
                    });
                }
            });
        }
    }

    $(function(){
        menu = $.ligerMenu({width:120,items:[{text:'编辑',click:edit,icon:'icon-edit'},{text:'删除',click:del,icon:'icon-trash'}]});
        menu_dialog = $.ligerDialog({ title:'',width:'470',height:'600',target: $("#menu_dialog"), buttons: [ { text: '确定', onclick:function(item,dialog){
            if(menu_form.valid()){
                var form_data = menu_form.getData();
                $('#menu_form').ajaxForm($('#menu_form').attr('action'), form_data, function (response) {
                    if (response.status == 0) {
                        $.ligerDialog.error(response.message);
                    } else {
                        $.ligerDialog.success(response.message);
                        window.location.reload();
                        menu_dialog.hide();
                    }
                });
            }else{
                $.ligerDialog.error("部分字段没有填写或者填写错误");
            }
        }}]});
        menu_dialog.hide();
        menu_form = $('#menu_form').ligerForm({
            inputwidth: 200,
            labelwidth: 150,
            space: 10,
            validate: true,
            fields: [
                {
                    name:"id",
                    type:"hidden"
                },
                {
                    display:"菜单标题",
                    name:"title",
                    newline:true,
                    type:"textbox",
                    width:200,
                    aftercontent: "",
                    validate:{
                        required:true
                    }
                },
                {
                    display:"菜单类型",
                    name:"type",
                    comboboxname: "type",
                    newline:true,
                    width:200,
                    height:22,
                    type:"combobox",
                    validate:{
                        required:true
                    },
                    editor:{
                        width:200,
                        data:[{id:'directory',text:'菜单目录'},{id:'module',text:'模块连接'},{id:'link',text:'菜单连接'}],
                        onselected:function(value){
                            if(value == 'directory'){
                                menu_form.getEditor('module_id').setDisabled();
                                menu_form.getEditor('action_id').setDisabled();
                                menu_form.getEditor('url').setDisabled();
                                menu_form.getEditor('params').setDisabled();
                                $('#pid,#module_id,#action_id,#url').removeAttr('required');
                            }
                            if(value == 'module'){
                                menu_form.getEditor('pid').setEnabled();
                                menu_form.getEditor('module_id').setEnabled();
                                menu_form.getEditor('action_id').setEnabled();
                                menu_form.getEditor('params').setEnabled();
                                menu_form.getEditor('url').setDisabled();
                                $('#pid,#module_id,#action_id').attr('required',{required:true});
                                $('#url').removeAttr('required');
                            }
                            if(value == 'link'){
                                menu_form.getEditor('pid').setEnabled();
                                menu_form.getEditor('module_id').setDisabled();
                                menu_form.getEditor('action_id').setDisabled();
                                menu_form.getEditor('params').setDisabled();
                                menu_form.getEditor('url').setEnabled();
                                $('#pid,#url').attr('required',{required:true});
                                $('#module_id,#action_id').removeAttr('required');
                            }
                        }
                    }
                },
                {
                    display:"菜单图标",
                    name:"icon",
                    comboboxname:"icon",
                    newline:true,
                    width:200,
                    height:22,
                    type:"combobox",
                    aftercontent:'<span id="icon_show" class="help-inline icon" style="padding-top: 5px;font-size: 25px;line-height: 20px;height: 20px;color:#000;"></span>',
                    validate:{
                        required:false
                    },
                    editor:{
                        width:200,
                        selectboxwidth:184,
                        selectboxheight:300,
                        ismultiselect:false,
                        renderitem:function(icon){
                            return "<span class='icon "+ icon.value +"'>&nbsp;"+ icon.text +"</span>";
                        },
                        onselected:function(value){
                            $('#icon_show').attr('class','help-inline icon '+value);
                        }
                    }
                },
                {
                    display:"上级连接",
                    name:"pid",
                    id:"pid",
                    comboboxname: "pid",
                    newline:true,
                    width:200,
                    height:22,
                    type:"combobox",
                    validate:{
                        required:true
                    },
                    editor:{
                        width:200,
                        selectboxheight:350,
                        textfield:'title',
                        treeleafonly:false,
                        tree:{
                            url:"<?= $this->url->get('menu/getParent') ?>",
                            idfieldname :'id',
                            parentidfieldname :'pid',
                            textfieldname:'title',
                            single:true,
                            checkbox: false
                        }
                    }
                },
                {
                    display:"连接模块",
                    name:"module_id",
                    comboboxname: "module_id",
                    newline:true,
                    width:200,
                    height:22,
                    type:"combobox",
                    validate:{
                        required:false
                    },
                    editor:{
                        width:200,
                        selectboxheight:350,
                        onselected:function(value){
                            liger.get('action_id').setUrl("<?= $this->url->get('menu/getActionList') ?>&pid="+value);
                        }
                    }
                },{
                    display:"连接动作",
                    name:"action_id",
                    comboboxname: "action_id",
                    newline:true,
                    width:200,
                    height:22,
                    type:"combobox",
                    validate:{
                        required:false
                    },
                    editor:{
                        selectboxheight:350,
                        width:200
                    }
                },
                {
                    display:"菜单连接",
                    name:"url",
                    newline:true,
                    type:"textbox",
                    width:200,
                    aftercontent: "",
                    validate:{
                        required:false
                    },
                    editor:{
                        width:200
                    }
                },
                {
                    display:"连接参数",
                    name:"params",
                    newline:true,
                    type:"textbox",
                    width:200,
                    aftercontent: "",
                    validate:{
                        required:false
                    },
                    editor:{
                        width:200
                    }
                },
                {
                    display:"是否隐藏",
                    name:"hidden",
                    comboboxname: "type",
                    newline:true,
                    width:80,
                    height:22,
                    type:"combobox",
                    validate:{
                        required:true
                    },
                    editor:{
                        width:80,
                        data:[{id:'off',text:'显示'},{id:'on',text:'隐藏'}]
                    }
                },
                {
                    display:"菜单状态",
                    name:"status",
                    comboboxname: "type",
                    newline:true,
                    width:80,
                    height:22,
                    type:"combobox",
                    validate:{
                        required:true
                    },
                    editor:{
                        width:80,
                        data:[{id:'on',text:'正常'},{id:'off',text:'禁用'}]
                    }
                },
                {
                    display:"菜单排序",
                    name:"sort",
                    newline:true,
                    width:80,
                    height:22,
                    type:"number",
                    validate:{
                        required:true
                    },
                    editor:{
                        width:80,
                        format:'int',
                        isnegative:false
                    }
                }
            ]
        });//动作表单初始化
        menu_form.getEditor('icon').setUrl("<?= $this->url->get('index/loadIcon') ?>");//载入模块图标
        menu_form.getEditor('module_id').setUrl("<?= $this->url->get('menu/getModule') ?>");//加载模块列表

        tree_datagrid = $("#tree_datagrid").ligerGrid({
                    url:"<?= $this->url->get('menu/list') ?>",
                    toolbar: {
                        items: [
                            {
                                text: "新增",
                                icon: "icon-plus-sign",
                                click: add
                            },{
                                text: "编辑",
                                icon: "icon-edit",
                                click: edit
                            },{
                                text: "删除",
                                icon: "icon-trash",
                                click: del
                            }
                        ]
                    },
                    columns: [
                        { display: '编号', name: 'id',width: 50, type: 'int', align: 'center' },
                        { display: '图标', name: 'icon', width: 40, align: 'center',type:'icon'},
                        { display: '菜单名称', name: 'title', id:'title', width: 160, align: 'left' },
                        { display: '菜单类型', name: 'type', width: 80, align: 'center',type:'url_type' },
                        { display: '模块名称', name: 'module_id', width: 120, align: 'center',type:'get_module_name' },
                        { display: '动作名称', name: 'action_id', width: 120, align: 'center',type:'get_action_name' },
                        { display: '动作参数', name: 'params', width: 80, align: 'center'},
                        { display: '站外连接', name: 'url', width: 200, align: 'center' },
                        { display: '排序', name: 'sort', width: 50, align: 'center' },
                        { display: '是否隐藏', name: 'hidden', width: 80, align:'center',type:'hidden'},
                        { display: '状态', name: 'status', width: 50, align: 'center',type:'status' }
                    ], width: '100%', height:'100%',usepager:false,inwindow:false,alternatingrow: false,whenrclicktoselect:true,ondblclickrow:edit, oncontextmenu : function (parm,e) {actionid = parm.data.id;menu.show({ top: e.pageY, left: e.pageX });return false;},
                    tree:
                    {
                        columnid: 'title',
                        idfield: 'id',
                        parentidfield: 'pid'
                    }
                }
        );
    });
</script>