<?php

namespace app\admin\model\managementgame;

use think\Model;


class GameInfo extends Model
{

    

    

    // 表名
    protected $name = 'game_info';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;
    

    



    public function getGameTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['game_time']) ? $data['game_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setGameTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function getGameInfoList($where, $index)
    {
        return $this->alias('g')->field('g.id, g.game_type, g.game_name, g.people, g.game_time, g.createtime')
            ->join('game_info_detail d', 'g.id = d.game_info_id', 'LEFT')
            ->where($where)->limit($index, 5)->select();
    }

}
