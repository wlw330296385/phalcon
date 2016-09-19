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
