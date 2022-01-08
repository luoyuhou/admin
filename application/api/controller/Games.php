<?php


namespace app\api\controller;
use app\admin\model\managementgame\GameInfo;
use app\common\controller\Api;

class Games extends Api
{
    public function index($index)
    {
        $index = $this->request->param('index', 0);
        $userInfo = $this->auth->getUserinfo();
        $user_id = $userInfo['id'];
        $where = ['user_id' => $user_id];
        if (!empty($type)) {
            array_push($where, ['game_type' => $type]);
        }
        if (!empty($name)) {
            array_push($where, ['game_name' => $name]);
        }
        $modal = new GameInfo();
        return $modal->getGameInfoList($where, $index);
    }
}