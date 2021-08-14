<?php

namespace app\admin\controller\devicemanagement;

use app\common\controller\Backend;

/**
 * 设备终端配置管理
 *
 * @icon fa fa-circle-o
 */
class TerminalConfig extends Backend
{
    
    /**
     * TerminalConfig模型对象
     * @var \app\admin\model\devicemanagement\TerminalConfig
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\devicemanagement\TerminalConfig;

    }

    public function import()
    {
        parent::import();
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function edit($ids = null)
    {
        if ($this->request->isGet()) {
            if ($ids) {
                $terminal = \app\admin\model\devicemanagement\Terminal::get($ids);
                if (!empty($terminal)) {
                    $row = $this->model->where(['t_id' => $ids])->find();
                    if (!empty($row)) {
                        $this->assign('row', $row);
                        return $this->view->fetch();
                    } else {
                        $this->redirect(url('add'), ['ids' => $ids]);
                    }
                }
            }
        } else if ($this->request->isPost()) {
            $id = $this->request->param('ids');
            $params = $this->request->post("row/a", [], 'trim');
            $res = $this->model->save($params, ['id' => $id]);
            if ($res) {
                $this->success('success');
            }
            $this->error('failed');
        }
        $this->error('无效访问');
    }

}
