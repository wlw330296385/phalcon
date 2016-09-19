<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2015-12-29 21:16
 */
namespace Oupula\Backend\Controllers;
use Phalcon\image\adapter\Gd as Image;
use Oupula\Library\Uploader;
use Oupula\Backend\Library\ControllerBase;

/**
 * 上传管理
 */
class UploaderController extends ControllerBase
{
    private $base_path = '';//站点根目录
    private $ueditor_path = ''; //编辑器目录
    private $upload_path = ''; //上传目录
    private $upload_domain = '';//访问域名
    private $uploader_config_filename = '';//配置文件
    private $uploader_config = [];
    private $base64 = 'upload';

    public function initialize(){}
    public function init(){
        $this->base_path = realpath(SITE_PATH);
        $this->ueditor_path = realpath($this->base_path . '/javascript/ueditor/');
        $this->upload_path = realpath($this->config->application->uploadDir);
        $this->upload_domain = sprintf('http://www.%s',$this->settings['DOMAIN_ROOT']);
        $this->uploader_config_filename = realpath(APP_PATH . '../common/config/uploader.json');
        $this->uploader_config = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents($this->uploader_config_filename)), true);
        $domainArray = ['imageUrlPrefix'=>$this->upload_domain,'scrawlUrlPrefix'=>$this->upload_domain,'snapscreenUrlPrefix'=>$this->upload_domain,'catcherUrlPrefix'=>$this->upload_domain,'videoUrlPrefix'=>$this->upload_domain,'fileUrlPrefix'=>$this->upload_domain,'imageManagerUrlPrefix'=>$this->upload_domain,'fileManagerUrlPrefix'=>$this->upload_domain];
        $this->uploader_config = array_merge($this->uploader_config,$domainArray);
    }

    /**
     * 上传控制器
     */
    public function indexAction()
    {
        $this->init();
        $action = $this->request->get('action');
        $allowAction = ['config', 'uploadimage', 'uploadscrawl', 'uploadvideo', 'uploadfile', 'listimage', 'listfile', 'catchimage'];
        if (in_array(strtolower($action), $allowAction)) {
            call_user_func([$this, $action]);
        } else {
            $result = [
                'state' => '请求地址出错'
            ];
            $this->output($result);
        }
    }

    protected function config()
    {
        $this->output($this->uploader_config);
    }

    /**
     * 上传图片
     */
    protected function uploadImage()
    {
        $config = [
            "pathFormat" => $this->uploader_config['imagePathFormat'],
            "maxSize" => $this->uploader_config['imageMaxSize'],
            "allowFiles" => $this->uploader_config['imageAllowFiles']
        ];
        $fieldName = $this->uploader_config['imageFieldName'];
        /* 生成上传实例对象并完成上传 */
        $up = new Uploader($this->upload_path,$fieldName, $config, $this->base64);
        /* 返回数据 */
        $fileInfo = $up->getFileInfo();
        $handle = $this->request->get('handle','striptags','');

        if(!empty($handle) && $fileInfo['state'] == 'SUCCESS'){
            $filename = $this->upload_path.$fileInfo['url'];
            $imageHandle = new Image($filename);
            switch($handle){
                case 'thumb':
                    $width = $this->request->get('width','int',0);
                    $height = $this->request->get('height','int',0);
                    $imageHandle->resize($width,$height);
                    break;
                case 'crop':
                    $width = $this->request->get('width','int',0);
                    $height = $this->request->get('height','int',0);
                    $offsetX = $this->request->get('offsetX','int',0);
                    $offsetY = $this->request->get('offsetY','int',0);
                    $imageHandle->crop($width,$height,$offsetX,$offsetY);
                    break;
                default:
                    $this->output($fileInfo);
                    break;
            }

            $imageHandle->save();
        }
        $this->output($fileInfo);
    }

    /**
     * 上传涂鸦
     */
    protected function uploadScrawl()
    {
        $config = [
            "pathFormat" => $this->uploader_config['scrawlPathFormat'],
            "maxSize" => $this->uploader_config['scrawlMaxSize'],
            "allowFiles" => $this->uploader_config['scrawlAllowFiles'],
            "oriName" => "scrawl.png"
        ];
        $fieldName = $this->uploader_config['scrawlFieldName'];
        /* 生成上传实例对象并完成上传 */
        $up = new Uploader($this->upload_path,$fieldName, $config, $this->base64);
        /* 返回数据 */
        $this->output($up->getFileInfo());
    }

    /**
     * 上传视频
     */
    protected function uploadVideo()
    {
        $config = [
            "pathFormat" => $this->uploader_config['videoPathFormat'],
            "maxSize" => $this->uploader_config['videoMaxSize'],
            "allowFiles" => $this->uploader_config['videoAllowFiles']
        ];
        $fieldName = $this->uploader_config['videoFieldName'];
        /* 生成上传实例对象并完成上传 */
        $up = new Uploader($this->upload_path,$fieldName, $config, $this->base64);
        /* 返回数据 */
        $this->output($up->getFileInfo());
    }

    /**
     * 上传文件
     */
    protected function uploadFile()
    {
        $config = [
            "pathFormat" => $this->uploader_config['filePathFormat'],
            "maxSize" => $this->uploader_config['fileMaxSize'],
            "allowFiles" => $this->uploader_config['fileAllowFiles']
        ];
        $fieldName = $this->uploader_config['fileFieldName'];
        /* 生成上传实例对象并完成上传 */
        $up = new Uploader($this->upload_path,$fieldName, $config, $this->base64);
        /* 返回数据 */
        $this->output($up->getFileInfo());
    }


    /**
     * 列出图片
     */
    protected function listImage()
    {
        $allowFiles = $this->uploader_config['imageManagerAllowFiles'];
        $listSize = $this->uploader_config['imageManagerListSize'];
        $path = $this->uploader_config['imageManagerListPath'];
        $allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);

        /* 获取参数 */
        $size = !empty($this->request->get('size','int')) ? htmlspecialchars($this->request->get('size','int')) : $listSize;
        $start = !empty($this->request->get('start','int')) ? htmlspecialchars($this->request->get('start','int')) : 0;
        $end = intval($start) + intval($size);

        /* 获取文件列表 */
        $path = $this->upload_path . (substr($path, 0, 1) == "/" ? "":"/") . $path;
        $files = $this->getfiles($path, $allowFiles);
        if (!count($files)) {
            $this->output([
                "state" => "no match file",
                "list" => [],
                "start" => $start,
                "total" => count($files)
            ]);
        }

        /* 获取指定范围的列表 */
        $len = count($files);
        for ($i = min($end, $len) - 1, $list = []; $i < $len && $i >= 0 && $i >= $start; $i--){
            $list[] = $files[$i];
        }

        /* 返回数据 */
        $result = [
            "state" => "SUCCESS",
            "list" => $list,
            "start" => $start,
            "total" => count($files)
        ];
        $this->output($result);
    }

    /**
     * 列出文件
     */
    protected function listFile()
    {
        $allowFiles = $this->uploader_config['fileManagerAllowFiles'];
        $listSize = $this->uploader_config['fileManagerListSize'];
        $path = $this->uploader_config['fileManagerListPath'];
        $allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);
        /* 获取参数 */
        $size = !empty($this->request->get('size','int')) ? htmlspecialchars($this->request->get('size','int')) : $listSize;
        $start = !empty($this->request->get('start','int')) ? htmlspecialchars($this->request->get('start','int')) : 0;
        $end = intval($start) + intval($size);

        /* 获取文件列表 */
        $path = $this->upload_path . (substr($path, 0, 1) == "/" ? "":"/") . $path;
        $files = $this->getfiles($path, $allowFiles);
        if (!count($files)) {
            $this->output([
                "state" => "no match file",
                "list" => [],
                "start" => $start,
                "total" => count($files)
            ]);
        }
        /* 获取指定范围的列表 */
        $len = count($files);
        for ($i = min($end, $len) - 1, $list = []; $i < $len && $i >= 0 && $i >= $start; $i--){
            $list[] = $files[$i];
        }
        /* 返回数据 */
        $result = [
            "state" => "SUCCESS",
            "list" => $list,
            "start" => $start,
            "total" => count($files)
        ];
        $this->output($result);
    }

    /**
     * 抓取远程图片
     */
    protected function catchImage()
    {
        $config = [
            "pathFormat" => $this->uploader_config['catcherPathFormat'],
            "maxSize" => $this->uploader_config['catcherMaxSize'],
            "allowFiles" => $this->uploader_config['catcherAllowFiles'],
            "oriName" => "remote.png"
        ];
        $fieldName = $this->uploader_config['catcherFieldName'];
        /* 抓取远程图片 */
        $list = [];
        $source = $this->request->get($fieldName);
        foreach ($source as $imgUrl) {
            $item = new Uploader($this->upload_path,$imgUrl, $config, "remote");
            $info = $item->getFileInfo();
            array_push($list,[
                "state" => $info["state"],
                "url" => $info["url"],
                "size" => $info["size"],
                "title" => htmlspecialchars($info["title"]),
                "original" => htmlspecialchars($info["original"]),
                "source" => htmlspecialchars($imgUrl)
            ]);
        }
        /* 返回抓取数据 */
        $result = ['state' => count($list) ? 'SUCCESS' : 'ERROR','list' => $list];
        $this->output($result);
    }


    protected function output($result)
    {
        $callback = $this->request->get('callback', 'striptags');
        if (!empty($callback)) {
            if (preg_match("/^[\w_]+$/", $callback)) {
                exit(htmlspecialchars($callback) . '(' . json_encode($result) . ')');
            } else {
                exit(json_encode(['state' => 'callback参数不合法']));
            }
        } else {
            exit(json_encode($result,JSON_HEX_TAG));
        }
    }


    /**
     * 遍历获取目录下的指定类型的文件
     * @param $path
     * @param array $files
     * @return array
     */
    protected function getfiles($path, $allowFiles, &$files = [])
    {
        if (!is_dir($path)) return null;
        if(substr($path, strlen($path) - 1) != '/') $path .= '/';
        $handle = opendir($path);
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                $path2 = $path . $file;
                if (is_dir($path2)) {
                    $this->getfiles($path2, $allowFiles, $files);
                } else {
                    if (preg_match("/\.(".$allowFiles.")$/i", $file)) {
                        $files[] = [
                            'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
                            'mtime'=> filemtime($path2)
                        ];
                    }
                }
            }
        }
        return $files;
    }


}