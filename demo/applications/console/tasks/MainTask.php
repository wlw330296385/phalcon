<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-04-03 00:42
 */
use Oupula\Console\Library\TaskBase;

class MainTask extends TaskBase
{
    public function mainAction() {
        $model = new \Oupula\Models\UserModel();
        $result = $model->where(['id'=>'1'])->find();
        var_dump($result);
        echo "\nThis is the default task and the default action \n";
    }
    /**
     * @param array $params
     */
    public function testAction(array $params) {
        echo sprintf('hello %s', $params[0]) . PHP_EOL;
        echo sprintf('best regards, %s', $params[1]) . PHP_EOL;
    }
}
