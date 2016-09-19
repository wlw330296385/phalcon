<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2015-12-29 21:16
 */
namespace Oupula\Backend\Library;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Controller;
use Phalcon\Text;
use Oupula\Models\OperatorLogModel;
use Oupula\Models\SettingModel;
use Oupula\Models\MenuModel;
use Oupula\Models\ModuleModel;
use Oupula\Models\ModuleActionModel;
use Oupula\Models\AdminGroupModel;
use Oupula\Library\Tree;
abstract class ControllerBase extends ControllerPrivlege
{
    /**
     * @var int $memoryUsage 内存使用量
     */
    protected $memoryUsage = 0;
    /**
     * @var float $runTime 页面执行时间
     */
    protected $runTime = 0;
    /**
     * @var int $sqlQueryCount SQL查询次数
     */
    protected $sqlQueryCount = 0;
    /**
     * @var array $settings 配置信息
     */
    protected $settings = [];
    protected $frontDomain = '';
    protected $backendDomain = '';
    protected $staticDomain = '';


    /**
     * 初始化处理
     */
    public function OnConstruct()
    {
       // phpinfo();die;
        $this->view->disable();
        $this->memoryUsage = memory_get_usage(true);
        $this->runTime = microtime(true);
        $this->getSettings();//获取设置配置信息
        parent::OnConstruct();
    }

    /**
     * 获取系统配置信息
     */
    protected function getSettings()
    {
        $this->settings = $this->modelsCache->get('settings');
        if ($this->settings === null) {
            $setting_model = new SettingModel();
            $setting_data = $setting_model->select();
            foreach ($setting_data as $v) {
                $this->settings[$v['name']] = $v['value'];
            }
            $this->modelsCache->save('settings', $this->settings);
        }
        $this->view->setVar('settings', $this->settings);
    }

    /**
     * 格式化文件大小
     * @param int $size 大小
     * @return string
     */
    protected function formatSize($size)
    {
        $unit = ['BYTE', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $i = floor(log($size, 1024));
        return @round($size / pow(1024, $i), 2) . ' ' . $unit[intval($i)];
    }

    /**
     * 获取执行时间
     * @return float
     */
    protected function getRunTime()
    {
        $endTime = microtime(true);
        return number_format($endTime - $this->runTime, 6);
    }

    /**
     * 获取内存占用
     * @return string
     */
    protected function getMemoryUsage()
    {
        $endMemory = memory_get_usage(true);
        $memorySize = $endMemory - $this->memoryUsage;
        return $this->formatSize($memorySize);
    }

    /**
     * 给模板添加获取执行信息的函数
     * @return string
     */
    protected function afterExecuteRoute()
    {
        $runtime = sprintf('Processed in %f second(s),Usage memory in %s', $this->getRunTime(), $this->getMemoryUsage());
        $this->view->setVar('runtime', $runtime);
    }

    abstract public function initialize();
}