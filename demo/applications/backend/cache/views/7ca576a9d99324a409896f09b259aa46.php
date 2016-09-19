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
    <script type="text/javascript" src="/javascript/index.js"></script>
</head>
<body>
<script id='slideTemplate' type="text/template">
    <!if(menu.length>0){!>
    <!for(var i=0;i<menu.length;i++){
    var item = menu[i];
    !>
    <li class="submenu <!if(item.first){!> open <!}!>"> <a href="<!=item.link!>"><i class="icon <!=item.icon!>"></i> <span><!=item.title!></span></a>
        <ul>
            <!for(var y=0;y<item.sub.length;y++){
            var sub = item.sub[y];
            !>
            <li><a link="<!=sub.link!>" target="<!=sub.target!>"><i class="icon <!=sub.icon!>"></i> <span><!=sub.title!></span></a></li>
            <!}!>
        </ul>
    </li>
    <!}!>
    <!}!>
</script>
<div id="layout">
    <div position="left">
        <div id="sidebar"><ul></ul></div>
    </div>
    <div position="center">
        <iframe frameborder="0" id="content_page" name="content_page" src="/Index/main" width="100%" height="100%" scrolling="no"></iframe>
    </div>
    <div position="top">
        <div id="header">
    <h1><?= $settings['SITE_NAME'] ?></h1>
</div>

<div id="top-right" class="navbar navbar-inverse">
    <ul class="nav">
        <li class="dropdown" id="profile-messages" ><a title="" href="#" data-toggle="dropdown" data-target="#profile-messages" class="dropdown-toggle"><i class="icon icon-user"></i>  <span class="text">我的信息</span><b class="caret"></b></a>
            <ul class="dropdown-menu">
                <li><a onclick="open_profile();">
                    <i class="icon-user"></i>个人资料</a>
                </li>
                <li class="divider"></li>
                <li><a onclick="pwd_dialog.show();"><i class="icon-check"></i>修改密码</a></li>
            </ul>
        </li>
        <li><a title="退出后台" href="<?= $this->url->get('index/logout') ?>"><i class="icon icon-signout"></i> <span class="text">退出后台</span></a></li>
    </ul>
</div>

<div id="user-nav" class="navbar navbar-inverse">
    <ul class="nav">
        <?php foreach ($menu as $data) { ?>
        <li><a title="<?= $data['title'] ?>" data-id="<?= $data['id'] ?>"><i class="icon<?php if ($data['icon'] != '') { ?> <?= $data['icon'] ?><?php } ?>"></i> <span class="text"><?= $data['title'] ?></span></a></li>
        <?php } ?>
    </ul>
</div>

    </div>
    <div position="bottom">
        <div class="row-fluid">
            <div id="footer" class="span12">Copyright&copy; 2009-<?= $year ?> Oupula Tech Co.Ltd <div id="runtime"><?= $runtime ?></div></div>
        </div>
    </div>
</div>

<div id="pwd_dialog">
    <form id="pwd_form" action="/index/changePwd" method="post" autocomplete="off" class="form-horizontal"></form>
</div>

<div id="profile_dialog">
    <form id="profile_form" action="/index/changeProfile" method="post" autocomplete="off" class="form-horizontal"></form>
</div>

</body>
</html>