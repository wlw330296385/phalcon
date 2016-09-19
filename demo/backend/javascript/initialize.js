function get_unix_time(str)
{
    var newstr = str.replace(/-/g,'/');
    var date =  new Date(newstr);
    var time_str = date.getTime().toString();
    return time_str.substr(0, 10);
}


$.fn.loadForm = function(data){
    return this.each(function(){
        var input, name;
        if(data == null){this.reset(); return; }
        for(var i = 0; i < this.length; i++){
            input = this.elements[i];
            //checkbox的name可能是name[]数组形式
            name = (input.type == "checkbox")? input.name.replace(/(.+)\[\]$/, "$1") : input.name;
            if(data[name] == undefined) continue;
            switch(input.type){
                case "checkbox":
                    if(data[name] == ""){
                        liger.get(name).setValue('');
                    }else{
                        liger.get(name).setValue(data[name]);
                    }
                    break;
                case "radio":
                    if(data[name] == ""){
                        liger.get(name).setValue('');
                    }else{
                        liger.get(name).setValue(data[name]);
                    }
                    break;
                case "select-one":
                    liger.get(name).selectValue(data[name]);
                    break;
                case "select-multiple":
                    liger.get(name).selectValue(data[name]);
                    break;
                case "text":
                    $(input).val(data[name]);
                    break;
                case "button": break;
                default:
                    if(typeof liger.get(name) == "undefined"){
                        $(input).val(data[name]);
                    }else{
                        liger.get(name).setValue(data[name]);
                    }
                    break;
            }
        }
    });
};


$.fn.getForm = function(){
    var d = {};
    this.each(function(){
        var input,key,name,value;
        for(var i = 0; i < this.length; i++) {
            input = this.elements[i];
            name = input.name.split('[')[0];
            value = null;
            if(input.name.indexOf('[') <= 0){
                key = undefined;
            }else{
                key = input.name.split('[')[1].split(']')[0];
            }
            var isArray = (input.name.indexOf('[') > 0) ? true : false;
            if(isArray){
                if(!d[name]){
                    d[name] = new Array();
                }
            }
            if(input.name == ''){ continue; }
            switch(input.type){
                case "button":
                    break;
                case "reset":
                    break;
                case "submit":
                    break;
                case "hidden":
                    value = input.value;
                    break;
                case "checkbox":
                    if(input.checked == true){
                        value = $(input).val();
                    }
                    break;
                case "radio":
                    if(input.checked == true){
                        value = input.value;
                    }
                    break;
                case "text":
                    value = ($('#'+input.name+'_val').length == 0) ? $(input).val() : $('#'+input.name+'_val').val();
                    break;
                case "textarea":
                    value = input.value;
                    break;
                case "select-multiple":
                    value = input.value;
                    break;
                case "select-one":
                    value = input.value;
                    break;
                default:
                    value = input.value;
                    break;
            }
            if(isArray){
                if(key == ''){
                    if(input.type == 'checkbox') {
                        if (value) {
                            d[name].push(value);
                        }
                    }else{
                        d[name].push(value);
                    }
                }else{
                    if(input.type == 'checkbox'){
                        if(value){
                            d[name][key] = value;
                        }
                    }else{
                        d[name][key] = value;
                    }
                }
            }else{
                d[name] = value;
            }
        }
    });
    return d;
};

$.fn.ajaxForm = function(url,data,func){
    $.ajax({
        type: 'POST',
        url: url,
        data: data,
        success: func,
        dataType:'json'
    });
};

$.fn.clearForm = function(){
    this.each(function() {
        var input, name, value;
        for (var i = 0; i < this.length; i++) {
            input = this.elements[i];
            name = (input.type == "checkbox") ? input.name.replace(/(.+)\[\]$/, "$1") : input.name;
            if (input.name == "") {
                continue;
            }
            switch (input.type) {
                case "checkbox":
                    var obj = $(input);
                    if(obj.attr('readonly')){
                        return;
                    }
                    if(checkbox_obj = liger.get(obj.attr('id'))){
                        checkbox_obj.setValue(false);
                    }

                    $(input).removeAttr('checked');
                    break;
                case "radio":
                    $(input).removeAttr('checked');
                    break;
                case 'button':
                    break;
                case 'reset':
                    break;
                default:
                    $(input).val('');
            }
        }
    });
    return this;
};

$(document).ready(function() {
    $(document).on('keydown', function (event) {
        var e = event || window.event || arguments.callee.caller.arguments[0];
        if (e && e.keyCode == 27) {
            var top_window_index = Math.max.apply(null, $.map($('.l-dialog,.l-dialog-win'), function (e, n) {
                    if ($(e).css('display') == 'block') {
                        return parseInt($(e).css('z-index')) || 1;
                    }
                })
            );
            $('.l-dialog,.l-dialog-win').each(function () {
                if ($(this).css('display') == 'block' && $(this).css('z-index') == top_window_index) {
                    liger.get($(this).attr('ligeruiid')).hide();
                }
            });
        }
    });
    $('button[data-toggle="droptable"]').each(function () {
        var btn = $(this);
        var table = btn.parent().find('.dropdown-table');
        table.find('button').on('click', function () {
            btn.parent().removeClass('open');
            table.slideUp();
            return false;
        });
        btn.on('click', function () {
            var isActive = btn.parent().hasClass('open');
            var isDisabled = btn.parent().hasClass('disabled');
            if (isActive && !isDisabled) {
                btn.parent().removeClass('open');
                table.slideUp();
            }
            if(!isActive && !isDisabled){
                table.slideDown();
                btn.parent().addClass('open');
            }
        });
    });
    $('.tip').tooltip();
    $('.tip-left').tooltip({placement: 'left'});
    $('.tip-right').tooltip({placement: 'right'});
    $('.tip-top').tooltip({placement: 'top'});
    $('.tip-bottom').tooltip({placement: 'bottom'});
    $('#style-switcher i').click(function () {
        if ($(this).hasClass('open')) {
            $(this).parent().animate({marginRight: '-=190'});
            $(this).removeClass('open');
        } else {
            $(this).parent().animate({marginRight: '+=190'});
            $(this).addClass('open');
        }
        $(this).toggleClass('icon-arrow-left');
        $(this).toggleClass('icon-arrow-right');
    });
    $('.lightbox_trigger').click(function (e) {
        e.preventDefault();
        var image_href = $(this).attr("href");
        if ($('#lightbox').length > 0) {

            $('#imgbox').html('<img src="' + image_href + '" /><p><i class="icon-remove icon-white"></i></p>');

            $('#lightbox').slideDown(500);
        }
        else {
            var lightbox =
                '<div id="lightbox" style="display:none;">' +
                '<div id="imgbox"><img src="' + image_href + '" />' +
                '<p><i class="icon-remove icon-white"></i></p>' +
                '</div>' +
                '</div>';
            $('body').append(lightbox);
            $('#lightbox').slideDown(500);
        }
    });
    if ($('div').hasClass('picker')) {
        $('.picker').farbtastic('#color');
    }
    $('#lightbox').live('click', function () {
        $('#lightbox').hide(200);
    });
});