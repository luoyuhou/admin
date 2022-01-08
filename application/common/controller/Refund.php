<?php


namespace app\common\controller;

use think\Db;
use think\Exception;
use think\Log;


class Refund
{
    public function create($user_id, $order_id): array
    {
        // 启动事务
        Db::startTrans();
        try {
            $refund = Db::name('refund')->find(['order_id' => $order_id, 'user_id' => $user_id, 'state' => [0, 1]]);
            if (!empty($refund)) {
                throw new Exception('退款已申请或已退款');
            }
            $order = Db::name('order')->find(['order_id' => $order_id, 'user_id' => $user_id, 'state' => 1]);
            if (empty($order)) {
                throw new Exception('订单不存在');
            }
            $user = Db::name('user')->where(['id' => $user_id])->find();
            if (empty($user)) {
                throw new Exception('用户不存在');
            }
            if ($user['money'] >= 0 && $user['money'] < $order['amount']) {
               throw new Exception('账户余额不足');
            }
            $order_recharge = Db::name('order_recharge')->find(['o_id' => $order_id]);
            if (empty($order_recharge)) {
                throw new Exception('未找到支付记录');
            }
            Db::name('user')->where(['id' => $user_id])->setDec('money', $order['amount']);
            Db::name('refund')->insert([
                'order_id'      => $order_id,
                'user_id'       => $user_id,
                'money'         => $order_recharge['recharge_money'],
                'receiver'      => $order_recharge['recharge_origin'],
                'createtime'    => time()
            ]);
            Db::commit();
        } catch (\Exception $e) {
            Log::error('[create order] error: '.$e);
            Db::rollback();
            return ['result' => false, 'message' => $e];
        }
       return ['result' => true, 'message' => ''];
    }

    public function cancel($user_id, $order_id): array {
        Db::startTrans();
        try {
            $refund = Db::name('refund')->where(['o_id' => $order_id, 'user_id' => $user_id, 'state' => 0])->find();

            if (empty($refund)) {
                throw new Exception('退款单不存在');
            }

            Db::name('refund')->where(['id' => $refund['id']])->update(['state' => -1, 'finishtime' => time()]);
            Db::name('user')->where(['id' => $refund['user_id']])->setInc('money', $refund['money']);
            Db::commit();
        } catch (Exception $e) {
            Log::error('[cancel order] ['. $order_id .']error: '.$e);
            Db::rollback();
            return ['result' => false, 'message' => $e];
        }
        return ['result' => true, 'message' => ''];
    }

    public function reject($user_id, $order_id, $note): array
    {
        Db::startTrans();
        try {
            $refund = Db::name('refund')->where(['o_id' => $order_id, 'user_id' => $user_id, 'state' => 0])->find();

            if (empty($refund)) {
                throw new Exception('退款单不存在');
            }

            Db::name('refund')->where(['id' => $refund['id']])->update(['state' => 2, 'finishtime' => time(), 'note' => $note]);
            Db::name('user')->where(['id' => $user_id])->setInc('money', $refund['money']);

            Db::commit();
        } catch (Exception $e) {
            Log::error('[reject refund] ['. $user_id .'] ['. $order_id .']error: '.$e);
            Db::rollback();
            return ['result' => false, 'message' => $e];
        }
        return ['result' => true, 'message' => ''];
    }

    public function finish($user_id, $order_id, $note = null): array {
        Db::startTrans();
        try {
            $refund = Db::name('order')->where(['order_id' => $order_id, 'user_id' => $user_id, 'stage' => 0, 'is_delete' => 0])->find();
            if (empty($refund)) {
                throw new Exception('订单不存在');
            }

            Db::name('refund')->where(['id' => $refund['id']])->update(['state' => 1, 'finishtime' => time(), 'note' => $note]);
            Db::name('user')->where(['id' => $user_id])->setInc('money', $refund['money']);

            Db::commit();
        } catch (Exception $e) {
            Log::error('[finish refund] ['. $user_id .'] ['. $order_id .'] ['. $note .'] error: '.$e);
            Db::rollback();
            return ['result' => false, 'message' => $e];
        }
        return ['result' => true, 'message' => ''];
    }

    public function delete($user_id, $order_id): array
    {
        Db::startTrans();
        try {
            $refund = Db::name('refund')->where(['order_id' => $order_id, 'user_id' => $user_id])->find();
            if (empty($refund)) {
                throw new Exception('订单不存在');
            }
            if ($refund['state'] == 0) {
                throw new Exception('退款单处理中，可取消后在删除');
            }
            Db::name('refund')->where(['id' => $refund['id']])->update(['deletetime' => time()]);
            Db::commit();
        } catch (Exception $e) {
            Log::error('[delete order] ['. $order_id .'] error: '.$e);
            Db::rollback();
            return ['result' => false, 'message' => $e];
        }
        return ['result' => true, 'message' => ''];
    }
}