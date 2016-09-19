$.ligerDefaults.Form.editors['button'] = {
    create: function (container, editParm, p)
    {
        var html = $('<div />');
        var id = (p.prefixid || "") + editParm.field.name;
        if ($("#" + id).length)
        {
            html = $("#" + id);
        }
        html.attr({
            id: id,
            name: id
        });
        if (p.readonly){
            html.attr("readonly", true);
        }
        if(editParm.field.onclick == ''){
            editParm.field.onclick = function(){}
        }
        var editor = html.ligerButton({
            click:editParm.field.onclick
        });
        editor.setValue(editParm.field.display);
        container.append(html);
        return editor;
    },
    getValue: function (editor, editParm){},
    setValue: function (editor, value, editParm){}
};

$.ligerDefaults.Form.editors['grid'] = {
    create: function (container, editParm, p)
    {
        var html = $('<div />');
        var id = (p.prefixid || "") + editParm.field.name;
        if ($("#" + id).length)
        {
            html = $("#" + id);
        }
        html.attr({
            id: id,
            name: id
        });
        html.css('border-top','1px solid #CDCDCD');
        container.append(html);
        container.css("width", (editParm.field.width) ? parseInt(editParm.field.width) + 100 : '100%');
        container.css("height", (editParm.field.width) ? parseInt(editParm.field.width) + 100 : '100%');
        container.css({'padding':'0 0 0 0','margin':'0 0 0 0'});
        container.closest('.l-fieldcontainer').closest('ul').css({'padding':'0 0 0 0','margin':'0 0 0 0'});
        container.closest('.l-fieldcontainer').css({'padding':'0 0 0 0','margin':'0 0 0 0'});
        editParm.field.width = '100%';
        editParm.field.height = '100%';
        var editor = html.ligerGrid(editParm.field);
        return editor;
    },
    getValue: function (editor, editParm){
        return editor.data;
    },
    setValue: function (editor, value, editParm){
        editor.loadData(value);
    }
};


$.ligerDefaults.Form.editors['textlink'] = {
    create: function (container, editParm, p)
    {
        var html = $('<a>'+editParm.field.display+'</a>');
        var id = (p.prefixid || "") + editParm.field.name;
        if ($("#" + id).length)
        {
            html = $("#" + id);
        }
        html.attr({
            id: id,
            name: id,
        });
        if(editParm.field.onclick == ''){
            editParm.field.onclick = function(){}
        }
        html.on('click',editParm.field.onclick);
        if (p.readonly){
            html.attr("readonly", true);
        }
        container.append(html);
        return editor;
    },
    getValue: function (editor, editParm){},
    setValue: function (editor, value, editParm){}
};

$.ligerDefaults.Form.editors['editor'] = {
    create: function (container, editParm, p)
    {
        var html = $('<textarea class="l-textarea" style="margin-left:0;" />');
        var id = (p.prefixid || "") + editParm.field.name;
        if ($("#" + id).length)
        {
            html = $("#" + id);
        }
        html.attr({
            id: id,
            name: id,
            editor:'ueditor'
        });
        container.append(html);
        editParm.field.height = (editParm.field.height) ? editParm.field.height : 250;
        editParm.field.width = (editParm.field.width) ? editParm.field.width - 20 : 600;
        var editor = UE.getEditor(id,{initialFrameWidth : editParm.field.width,initialFrameHeight: editParm.field.height,zIndex:100});
        if (p.readonly){
            editor.setDisabled();
        }
        html.parent().css("width", "auto");
        return editor;
    },
    getValue: function (editor, editParm)
    {
        editor.sync();
        return editor.getContent();
    },
    setValue: function (editor, value, editParm)
    {
        if(value != null){
            editor.ready(function () {
                editor.setContent(value);
            });
        }
    },
    getHtml : function(editor,value,editParm){
        return editor.getContent();
    },
    getText : function(editor,value,editParm){
        return editor.getContentTxt();
    },
    getPlainTxt : function(editor,value,editParm){
        return editor.getPlainTxt();
    },
    setDisabled : function(editor,value,editParm){
        editor.setDisabled();
    },
    setEnabled : function(editor,value,editParm){
        editor.setEnabled();
    },
    setHide : function(editor,value,editParm){
        editor.setHide();
    },
    setShow : function(editor,value,editParm){
        editor.setShow();
    },
    insertText: function(editor,value,editParm){
        editor.execCommand('inserthtml', value);
    },
    clearText: function(editor,value,editParm){
        editor.execCommand('cleardoc');
    }
};

$.ligerDefaults.Form.editors['star'] = {
    create: function (container, editParm, p)
    {
        var html = $('<div></div>');
        var editor = $('<input type="hidden" class="ui-hidden" value="">');
        var id = (p.prefixid || "") + editParm.field.name;
        html.attr({
            id:id
        });
        editor.attr({
            name: id
        });
        html.append(editor);
        container.append(html);
        html.raty();
        return editor;
    },
    getValue: function (editor, editParm)
    {
        return editor.val();
    },
    setValue: function (editor, value, editParm)
    {
        $('#'+editor.name).raty('score',value);
        editor.val(value);
    },resize: function (editor, width, height, editParm)
    {
        editor.parent().css("width", "auto");
    }
};

$.ligerDefaults.Form.editors['color'] = {
    create: function (container, editParm, p)
    {
        var color = "#000000";
        if(editParm.field.value){
            color = editParm.field.value;
        }
        var html = $('<div class="input-append color" data-color="#000000" style="padding-left:1px;margin-bottom:0" data-color-format="hex">');
        var editor = $('<input type="hidden" class="ui-hidden" value="">');
        var addon = $('<span class="add-on"><i style="background-color:#000000"></i></span>');
        var id = (p.prefixid || "") + editParm.field.name;
        html.attr({
           id:id,
           'data-color':color
        });
        editor.attr({
            name: id,
            value:color
        });
        addon.find('i').css('background-color',color);
        html.append(editor);
        html.append(addon);
        container.append(html);
        html.colorpicker('setValue',color);
        return editor;
    },
    getValue: function (editor, editParm)
    {
        return editor.val();
    },
    setValue: function (editor, value, editParm)
    {
        editor.val(value);
    },resize: function (editor, width, height, editParm)
    {
        editor.parent().css("width", "auto");
    }
};

$.ligerDefaults.Form.editors['image'] = {
    create: function (container, editParm, p)
    {
        var html = $('<input type="hidden" class="ui-hidden" value="">');
        var id = (p.prefixid || "") + editParm.field.name;
        if ($("#" + id).length)
        {
            html = $("#" + id);
        }
        html.attr({
            id: id,
            name: id
        });
        container.append(html);
        editParm.field.height = (editParm.field.height) ? editParm.field.height : 0;
        editParm.field.width = (editParm.field.width) ? editParm.field.width : 0;
        var url = editParm.field.editor.url;
        var extension = (editParm.field.editor.extension) ? editParm.field.editor.extension : 'gif,jpg,jpeg,png';
        var upload_html = $('<div style="width:80px;height:30px;margin-left:0;padding-top:5px;float:left;clear: both;">上传图片</div>');
        upload_html.attr({id:id+'_picker'});
        container.append(upload_html);
        if(editParm.field.editor.preview){
            var preview_html = $('<div class="thumbnail" style="max-width: 110px; max-height: 110px; line-height: 10px;display:none;"></div>');
            var preview_img = $('<img src="about:blank" border="0" width="100px" height="100px" style="display:none;float:right;">');
            preview_img.attr({id:id+'_img'});
            if(editParm.field.value){
                $('#'+id+'_img').attr('src',editParm.field.value).css('display','block');
            }
            preview_html.attr({id:id+'_thumb'});
            preview_html.append(preview_img);
            container.append(preview_html);
        }
        var uploader = new WebUploader.create({
            server: url,
            pick: '#'+id+'_picker',
            auto: true,
            resize: false,
            fileVal:'upfile',
            accept: {
                title: editParm.field.display,
                extensions: extension,
                mimeTypes: 'image/*'
            }
        });
        uploader.on( 'uploadSuccess', function( file , res) {
            $('#'+id).val(res.url);
            if(editParm.field.editor.preview){
                $('#'+id+'_img').attr('src',res.url).css('display','block');
                $('#'+id+'_thumb').css('display','block');
            }
            uploader.reset();
        });
        if (p.readonly){
            uploader.disable();
        }
        return html;
    },
    getValue: function (editor, editParm)
    {
        return editor.val();
    },
    setValue: function (editor, value, editParm)
    {
        var id = editParm.field.name;
        editor.val(value);
        if(value){
            $('#'+id+'_img').attr('src',value).css('display','block');
            $('#'+id+'_thumb').css('display','block');
        }else{
            $('#'+id+'_img').attr('src','about:blank').css('display','none');
            $('#'+id+'_thumb').css('display','none');
        }
    },resize: function (editor, width, height, editParm)
    {
        editor.parent().css("width", "auto");
    }
};

$.ligerDefaults.Form.editors['file'] = {
    create: function (container, editParm, p)
    {
        var allowEdit = (editParm.field.editor.allowEdit) ? editParm.field.editor.allowEdit : true;
        var id = (p.prefixid || "") + editParm.field.name;
        var html = '';
        if(!allowEdit){
            container.append($('<span id="'+ id +'_filename"></span>'));
            html = $('<input type="hidden" class="ui-hidden" value="">');
        }else{
            html = $('<input type="text" class="ui-textbox" value="">');
            var width = (editParm.field.width) ? editParm.field.width - 110 : 310;
            html.width(width);
        }
        if ($("#" + id).length)
        {
            html = $("#" + id);
        }
        html.attr({
            id: id,
            name: id
        });
        container.append(html);
        editParm.field.height = (editParm.field.height) ? editParm.field.height : 0;
        editParm.field.width = (editParm.field.width) ? editParm.field.width : 0;
        var url = editParm.field.editor.url;
        var extension = (editParm.field.editor.extension) ? editParm.field.editor.extension : null;
        var upload_html = $('<div style="width:80px;height:30px;float:right;margin-left:10px;padding-top:5px;">上传文件</div>');
        upload_html.attr({id:id+'_picker'});
        container.append(upload_html);
        var uploader = new WebUploader.create({
            server: url,
            pick: '#'+id+'_picker',
            auto: true,
            resize: false,
            fileVal:'upfile',
            accept: {
                title: editParm.field.display,
                extensions: extension,
                mimeTypes: '*/*'
            }
        });
        uploader.on( 'uploadSuccess', function( file , res) {
            $('#'+id).val(res.url);
            if(!allowEdit){
                $('#'+id+'_filename').val(res.original);
            }
            uploader.reset();
        });
        if (p.readonly){
            uploader.disable();
        }
        return html;
    },
    getValue: function (editor, editParm)
    {
        return editor.val();
    },
    setValue: function (editor, value, editParm)
    {
        editor.val(value);
    },resize: function (editor, width, height, editParm)
    {
        editor.parent().css("width", "auto");
    }
};

