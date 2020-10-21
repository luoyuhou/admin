<?php

namespace app\admin\controller\course;

use app\common\controller\Backend;
use think\Session;
use think\Db;
use think\Config;

/**
 * 课程列管理
 *
 * @icon fa fa-circle-o
 */
class Courselist extends Backend
{
    
    /**
     * Courselist模型对象
     * @var \app\admin\model\course\Courselist
     */
    protected $model = null;
    protected $api_url = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\course\Courselist;
        $this->api_url = Config::get('fastadmin.api_url');
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

    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);

                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validateFailException(true)->validate($validate);
                    }
                    $result = $this->model->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $admin_id = Session::get()['admin']['id'];
        $res =  Db::name('course')->where('admin_id', $admin_id)->field('id,title')->select();
        $this->assign('course',$res);
        $this->assignconfig('api_url', $this->api_url);
        return $this->view->fetch();
    }

    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        $admin_id = Session::get()['admin']['id'];
        $res =  Db::name('course')->where('admin_id', $admin_id)->field('id,title')->select();
        $this->assign('course',$res);
        return $this->view->fetch();
    }

    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        $course_id = input('ids');
        if (!$course_id) {
            $this->error('请从课程进入');
        }
//        dump($course_id);die;
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $admin_id = Session::get()['admin']['id'];
            $list = $this->model
                ->alias('l')
                ->join('course c', 'l.course_id=c.id')
                ->where('c.admin_id', $admin_id)
                ->where('l.course_id', $course_id)
                ->where($where)
                ->order($sort, $order)
                ->field('l.*,c.title as course_id')
                ->paginate($limit);
//dump(Db::name('course_list')->getLastSql());die;
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        $this->assignconfig('course_id', $course_id); // 返回
        $this->assignconfig('api_url', $this->api_url);
        return $this->view->fetch();
    }

}
