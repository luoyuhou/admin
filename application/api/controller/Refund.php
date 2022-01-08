<?php


namespace app\api\controller;
use app\common\controller\Api;
use app\common\controller\Refund as CommonRefund;
use app\admin\model\ordermanagement\Refund as RefundModel;


class Refund extends Api
{
    protected $noNeedLogin = ['index', 'create'];
    public function create()
    {
        $userInfo = $this->auth->getUserinfo();
        $user_id = $userInfo['id'];
        $order_id = $this->request->param('order_id');
        $refundRepo = new CommonRefund();
        $result = $refundRepo->create($user_id, $order_id);
        if (!$result['result']) {
            $this->error($result['message']);
        }
        $this->success();
    }

    public function index($ids = null)
    {
        $refundModal = new RefundModel();
        if ($this->request->isGet()) {
            if (!empty($ids)) {
                $this->error('缺少参数');
            }
            $row = $refundModal->where(['id' => $ids])->find();
            $this->result('', json($row));
        }
        if($this->request->isPost()) {
            $pageNum = $this->request->param('pageNum', 0);
            $pageSize = $this->request->param('pageSize', 5);
            $status = $this->request->param('status');
            $userInfo = $this->auth->getUserinfo();
            $where = ['user_id' => $userInfo['id']];
            if (is_numeric($status)) {
                $where['state'] = $status;
            }
            $rows = $refundModal->where($where)->limit($pageNum, $pageSize)->select();
            $this->result('', $rows, 1);
        }
    }
}