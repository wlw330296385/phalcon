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
<div style="width:512px;height:222px;left:50%;top:50%;margin-left:-256px;margin-top:-111px;position:absolute">
    <div class="widget-msgbox">
            <div class="widget-title"> <span class="icon"> <i class="icon-minus-sign"></i> </span>
                <h5>出现错误</h5>
            </div>
            <div class="widget-msginfo">
                <div class="error_ex">
                    <h3><?= $message ?></h3>
                    <p><span id="wait"><?= $timeout ?></span>秒后进行跳转</p>
                    <a id="link" class="btn btn-danger btn-big"  href="<?= $link ?>">立即跳转</a>
                </div>
                <br/>
            </div>
        </div>
</div>
<script type="text/javascript">
    (function(){
        var wait = document.getElementById('wait');
        var interval = setInterval(function(){
            var time = --wait.innerHTML;
            if(time <= 0) {
                location.href = '<?= $url ?>';
                clearInterval(interval);
            }
        }, 1000);
    })();
</script>
</body>
</html>