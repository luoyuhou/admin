<?php


namespace app\common\controller;

use fast\Random;
use think\Db;
use think\Exception;
use think\Log;


class Order
{
    public function create($user_id, $money, $amount, $price, $discount, $event, $coupon = []): array
    {
        $order_id = Random::uuid();

        $order_data = [
            'user_id' => $user_id,
            'order_id' => $order_id,
            'amount' => $amount,
            'money' => $money,
            'price' => $price,
            'discount' => $discount,
            'createtime' => time()
        ];

        $order_detail_data = [
            'o_id' => $order_id,
            'coupon' => json_encode($coupon),
        ];

        if ($event) {
            $event_data = Db::name('event')->where(['id' => $event, 'status' => 1, 'prop_type' => 0])->find();
            if (empty($event_data)) {
                return ['result' => false, 'message' => '活动不存在或者已结束'];
            }
            array_push($order_detail_data, ['event' => $event, 'additional' => $event_data['prop_amount']]);
        }

        // 启动事务
        Db::startTrans();
        try {
            Db::name('order')->insert($order_data);
            Db::name('order_detail')->insert($order_detail_data);
            Db::commit();
        } catch (\Exception $e) {
            Log::error('[create order] error: '.$e);
            Db::rollback();
            return ['result' => false, 'message' => $e];
        }
       return ['result' => true, 'message' => ''];
    }

    public function cancel($order_id): array {
        Db::startTrans();
        try {
            $order = Db::name('order')->where(['order_id' => $order_id, 'is_delete' => 0, 'state' => 0])->find();

            if (empty($order)) {
                return ['result' => false, 'message' => '订单不存在'];
            }

            $res = Db::order('order')->where(['id' => $order['id']])->update(['state' => -1, 'updatetime' => time()]);

            if (empty($res)) {
                return ['result' => false, 'message' => '取消订单失败'];
            }
            Db::commit();
        } catch (Exception $e) {
            Log::error('[cancel order] ['. $order_id .']error: '.$e);
            Db::rollback();
            return ['result' => false, 'message' => $e];
        }
        return ['result' => true, 'message' => ''];
    }

    public function finish($user_id, $order_id, $recharge_type, $recharge_money): array {
        Db::startTrans();
        try {
            $order = Db::name('order')->where(['order_id', $order_id, 'state' => 0, 'is_delete' => 0])->find();
            if (empty($order)) {
                throw new Exception('订单不存在');
            }
            Db::name('order')->where(['order_id', $order_id])->update(['state' => 1, 'finishtime' => time()]);
            Db::name('order_recharge')->insert([
                'o_id' => $order_id,
                'recharge_type' => $recharge_type,
                'recharge_money' => $recharge_money,
                'createtime' => time()
            ]);
            Db::name('user')->where(['id' => $user_id])->setInc('money', $order['amount']);
            Db::commit();
        } catch (Exception $e) {
            Log::error('[finish order] ['. $order_id .'] ['. $recharge_type .'] ['. $recharge_money .'] error: '.$e);
            Db::rollback();
            return ['result' => false, 'message' => $e];
        }
        return ['result' => true, 'message' => ''];
    }

    public function delete($order_id): array
    {
        Db::startTrans();
        try {
            $order = Db::name('order')->where(['order_id' => $order_id])->find();
            if (empty($order)) {
                throw new Exception('订单不存在');
            }
            Db::name('order')->where(['o_id' => $order_id])->update(['is_delete' => 1, 'deletetime' => time()]);
            Db::commit();
        } catch (Exception $e) {
            Log::error('[delete order] ['. $order_id .'] error: '.$e);
            Db::rollback();
            return ['result' => false, 'message' => $e];
        }
        return ['result' => true, 'message' => ''];
    }
}