<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title><?= $title ?>-<?= $settings['SITE_NAME'] ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="/css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" type="text/css" href="/css/ui-style.css" />
    <link rel="stylesheet" type="text/css" href="/css/jquery.gritter.css" />
    <link rel="stylesheet" type="text/css" href="/css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="/css/font-glyphicons.css" />
    <link rel="stylesheet" type="text/css" href="/css/ligerUI/css/all.css" />
    <link rel="stylesheet" type="text/css" href="/css/colorpicker.css" />
<?= $this->assets->outputCss() ?>
    <script type="text/javascript" src="/javascript/jquery.min.js"></script>
    <!--[if lte IE 9]>
    <script src="/javascript/respond.min.js"></script>
    <script src="/javascript/html5shiv.min.js"></script>
    <![endif]-->
    <script type="text/javascript" src="/javascript/bootstrap.min.js"></script>
    <script type="text/javascript" src="/javascript/jquery.validate.js"></script>
    <script type="text/javascript" src="/javascript/jquery.plugin.js"></script>
    <script type="text/javascript" src="/javascript/jquery.gritter.min.js"></script>
    <script type="text/javascript" src="/javascript/jquery.popover.js"></script>
    <script type="text/javascript" src="/javascript/ligerui.min.js"></script>
    <script type="text/javascript" src="/javascript/ligerui.extend.js"></script>
    <script type="text/javascript" src="/javascript/webuploader.min.js"></script>
    <script type="text/javascript" src="/javascript/jquery.raty.js"></script>
    <script type="text/javascript" src="/javascript/ueditor/ueditor.config.js"></script>
    <script type="text/javascript" src="/javascript/ueditor/ueditor.all.min.js"></script>
    <script type="text/javascript" src="/javascript/bootstrap-colorpicker.js"></script>
    <script type="text/javascript" src="/javascript/template.js"></script>
<?= $this->assets->outputJs() ?>
</head>
<body>
<div id="loginbox" class="nopadding">
    <form id="loginform" class="form-vertical" onsubmit="return $('#loginform').validate();">
        <div class="control-group normal_text"> <h3><img src="/images/logo.png" alt="Logo" /></h3></div>
        <div class="control-group ">
            <div class="controls clearfix">
                <div class="label-icon bg_lg">
                    <i class="icon-user"></i>
                </div>
                <div class="control-ipt">
                    <input name="username" type="text" required placeholder="帐号" />
                </div>
            </div>
        </div>
        <div class="control-group ">
            <div class="controls clearfix">
                <div class="label-icon bg_ly">
                    <i class="icon-lock"></i>
                </div>
                <div class="control-ipt">
                    <input name="password" type="password" minlength="6" minlength="6" required placeholder="密码" />
                </div>
            </div>
        </div>
        <div class="control-group ">
            <div class="controls clearfix">
                <div class="label-icon bg_lb">
                    <i class="icon-bolt"></i>
                </div>
                <div class="control-ipt ipt-code">
                    <input type="text" name="code" placeholder="验证码" required minlength="4" maxlength="4" style="text-transform:uppercase;" />
                    <div class="box-code">
                        <img id="code_img" src="<?= $this->url->get('login/validCode') ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="form-actions">
            <button id="confirm" class="btn icon-off btn-large btn-success"> 登 录 </button>
        </div>
    </form>
</div>
<script>
    $(document).ready(function() {
        if (window!=top){
            top.location.href =window.location.href;
        }
        $('#code_img').on('click', function () {
            this.src = '<?= $this->url->get('login/validCode') ?>';
        });
        $("#loginform").validate({
            submitHandler: function (form) {
                var data = $('#loginform').serializeArray();
                $.ajax({
                    type: 'POST',
                    url: '<?= $this->url->get('login/check') ?>',
                    data: data,
                    dataType: 'json',
                    success: function (result) {
                        if (result.status == 0) {
                            $('#code_img').click();
                            $.ligerDialog.error(result.message);
                            return false;
                        } else {
                            window.location.href = '<?= $this->url->get('index/index') ?>';
                        }
                    }
                });
            }
        });
    });
</script>
</body>

</html>
