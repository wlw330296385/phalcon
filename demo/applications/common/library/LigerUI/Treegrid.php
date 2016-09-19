<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-25 21:46
 */

namespace Oupula\Library\LigerUI;


class Treegrid extends Grid
{
    /**@var $tree Tree */
    public $tree;

    public function __construct()
    {
        $this->tree = new Tree();
    }

}