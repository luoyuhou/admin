<?php


namespace app\api\controller;
use app\common\controller\Api;
use app\common\controller\Order as CommonOrder;
use app\admin\model\ordermanagement\Order as OrderModal;


class Order extends Api
{
    protected $noNeedLogin = ['index', 'create'];
    public function create()
    {
        (new Validate)->check_order_create();
        $userInfo = $this->auth->getUserinfo();
        $money = $this->request->param('money');
        $amount = $this->request->param('amount');
        $price = $this->request->param('price');
        $discount = $this->request->param('discount');
        $event = $this->request->param('event', 0);
        $coupon = $this->request->param('coupon', []);
        $user_id = $userInfo['id'];
        $orderRepo = new CommonOrder();
        $result = $orderRepo->create($user_id, intval($money), intval($amount), intval($price), intval($discount), intval($event), $coupon);
        if (!$result['result']) {
            $this->error($result['message']);
        }
        $this->success();
    }

    public function index($ids = null)
    {
        $orderModal = new OrderModal();
        if ($this->request->isGet()) {
            if (!empty($ids)) {
                $this->error('确实参数');
            }
            $row = $orderModal->where(['id' => $ids])->find();
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
            $rows = $orderModal->where($where)->limit($pageNum, $pageSize)->select();
            $this->result('', $rows, 1);
        }
    }
}