<?php
namespace Oupula\Backend\Controllers;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Models\LoginLogModel as LoginLog;
use Oupula\Models\OperatorLogModel as OperatorLog;

/**
 * 日志模块
 */
class LogController extends ControllerBase
{
    public function initialize(){}

    /**
     * 字符串中部分字母用型号代替
     * @param $str
     * @return string
     */
    private function str_mask($str){
        $length = strlen($str);
        $start = intval(floor($length*0.25));
        $end = intval(floor($length*0.75));
        $arr = str_split($str);
        for($i=$start;$i<$end;$i++){
            $arr[$i] = '*';
        }
        return implode('',$arr);
    }

    /**
     * 查看登陆日志列表
     */
    public function loginListAction(){
        if ($this->request->isGet()) {
            $this->view->enable();
            $this->view->setVar('title', '查看登陆日志');
        } else {
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'desc');
            $result = [];
            $condition = [];
            $model = new LoginLog();
            if(($login_username = $this->request->getPost('username','striptags')) != ''){
                $condition['login_username'] = ['like',"%{$login_username}%"];
            }
            if(($login_ip = $this->request->getPost('ip','striptags')) != ''){
                $condition['login_ip'] = ['like',"%{$login_ip}%"];
            }
            if(($login_area = $this->request->getPost('area','striptags')) != ''){
                $condition['login_area'] = ['like',"%{$login_area}%"];
            }
            if(($login_status = $this->request->getPost('status','striptags')) != ''){
                $condition['login_status'] = ['like',"%{$login_status}%"];
            }
            if(($login_usertype = $this->request->getPost('type','striptags')) != ''){
                $condition['login_usertype'] = $login_usertype;
            }
            $rows = $model->where($condition)->limit($offset,$pagesize)->order([$sortname=>$sortorder])->select();
            $result['Total'] = $model->where($condition)->count();
            foreach ($rows as $k => $v) {
                $rows[$k]['login_password'] = $this->str_mask($v['login_password']);
                $rows[$k]['login_time'] = date('Y-m-d H:i:s',$v['login_time']);
            }
            $result['Rows'] =  $rows;
            $this->response->setJsonContent($result);
            $this->response->send();
            exit();
        }
    }

    /**
     * 查看操作日志列表
     */
    public function operationListAction()
    {
        if ($this->request->isGet()) {
            $this->view->enable();
            $this->view->setVar('title', '查看操作日志');
        } else {
            $method = $this->request->get('method','string','');
            if(empty($method)){
                $page = $this->request->getPost('page', 'int', 1);
                $pagesize = $this->request->getPost('pagesize', 'int', 20);
                $offset = ($page - 1) * $pagesize;
                $sortname = $this->request->getPost('sortname', 'string', 'id');
                $sortorder = $this->request->getPost('sortorder', 'string', 'desc');

                $model = new OperatorLog();
                $rows = $model->alias('a')->join('__ADMIN__ b ON a.user_id = b.id')->field('a.*,b.username')->limit($offset,$pagesize)->order(["a.{$sortname}"=>$sortorder])->select();
                $data = [];
                foreach ($rows as $k => $v) {
                    $module = strtolower($v['module']);
                    $action = strtolower($v['action']);
                    $data[$k]['id'] = $v['id'];
                    $data[$k]['user_id'] = $v['user_id'];
                    $data[$k]['username'] = $v['username'];
                    $data[$k]['module'] = $v['module'];
                    $data[$k]['module_name'] = isset($this->moduleNameList[$module]['title']) ? $this->moduleNameList[$module]['title'] : $module;
                    $data[$k]['action'] = $v['action'];
                    $data[$k]['action_name'] = isset($this->actionNameList[$module][$action]['title']) ? $this->actionNameList[$module][$action]['title'] :$action;
                    $data[$k]['operate_ip'] = $v['operate_ip'];
                    $data[$k]['operate_time'] = date("Y-m-d H:i:s", $v['operate_time']);
                }
                $result = ['Rows' => $data, 'Total' => $model->count()];
                $this->response->setJsonContent($result);
                $this->response->send();
                exit();
            }else{
                $model = new OperatorLog();
                $id = $this->request->getPost('id','int',0);
                $result = $model->where(['id'=>$id])->find();
                $data = [];
                $data['content'] = '';
                if($result){
                    $data['content'] = gzuncompress($result['content']);
                }
                $this->response->setJsonContent($data);
                $this->response->send();
            }

        }
    }
}