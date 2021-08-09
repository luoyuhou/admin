<?php

namespace app\admin\model\managementgame;

use think\Model;


class GameInfoDetail extends Model
{

    

    

    // 表名
    protected $name = 'game_info_detail';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;
    

    public function getListByGameId($id)
    {
         $rows = $this->where(['game_info_id' => $id])->find();
         $count = $this->where(['game_info_id' => $id])->count();
         return array('total' => $count, 'rows' => [$rows]);
    }
    



    public function getTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['time']) ? $data['time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
