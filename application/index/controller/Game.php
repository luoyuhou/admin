<?php

namespace app\index\controller;

use addons\wechat\model\WechatCaptcha;
use app\admin\model\managementgame\GameInfo;
use app\common\controller\Frontend;
use app\common\library\Ems;
use app\common\library\Sms;
//use app\common\library\token\driver\Redis;
//use think\cache\driver\Redis;
use app\common\model\Attachment;
use fast\Random;
use think\Config;
use think\Cookie;
use think\Hook;
use think\Log;
use think\Session;
use think\Validate;
use app\common\behavior\Common;
use app\admin\model\managementgame\GameInfo as GameInfoModel;

/**
 * 会员中心
 */
class Game extends Frontend
{
    protected $layout = 'default';
    protected $noNeedLogin = ['login', 'register', 'third', 'getscanloginurl', 'getscanlogin'];
    protected $noNeedRight = ['*'];
    protected $token_expire = 60;

    public function _initialize()
    {
        parent::_initialize();
        $auth = $this->auth;

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }

        //监听注册登录退出的事件
        Hook::add('user_login_successed', function ($user) use ($auth) {
            $expire = input('post.keeplogin') ? 30 * 86400 : 0;
            Cookie::set('uid', $user->id, $expire);
            Cookie::set('token', $auth->getToken(), $expire);
        });
        Hook::add('user_register_successed', function ($user) use ($auth) {
            Cookie::set('uid', $user->id);
            Cookie::set('token', $auth->getToken());
        });
        Hook::add('user_delete_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });
        Hook::add('user_logout_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });
    }

    /**
     * 会员中心
     */
    public function index()
    {
        $_game_models = [['id' => 1, 'title' => '单人模式'], ['id' => 2, 'title' => '生涯模式'], ['id' => 3, 'title' => '店内联机']];
        $this->view->assign('title', __('Game'));
        $model = new GameInfoModel();
        $list = $model->field('id, game_name')->distinct('game_name')->select();
        $this->view->assign('gameName', $list);
        $this->view->assign('gameModel', $_game_models);
        $this->assignconfig("_gameModel", $_game_models);
        $this->view->assign('tableField', ['Id', 'Game Type', 'Game Name', 'People', 'Game Time', 'Create Time', 'Detail']);
        return $this->view->fetch();
    }

    public function getGameInfoList() {
        $user = $this->auth->getUserinfo();
        if (empty($user)) {
            $this->error();
        }
        $uid = $user['id'];
        $type = $this->request->param('type');
        $name = $this->request->param('name');
        $index = $this->request->param('index');
        $model = new GameInfoModel();
        $where = ['user_id' => $uid];
        if (!empty($type)) {
            array_push($where, ['game_type' => $type]);
        }
        if (!empty($name)) {
            array_push($where, ['game_name' => $name]);
        }
        $list = $model->getGameInfoList($where, $index);
        return json($list);
    }




    /**
     * 个人信息
     */
    public function profile()
    {
        $this->view->assign('title', __('Profile'));
        return $this->view->fetch();
    }

    /**
     * 修改密码
     */
    public function changepwd()
    {
        if ($this->request->isPost()) {
            $oldpassword = $this->request->post("oldpassword");
            $newpassword = $this->request->post("newpassword");
            $renewpassword = $this->request->post("renewpassword");
            $token = $this->request->post('__token__');
            $rule = [
                'oldpassword'   => 'require|length:6,30',
                'newpassword'   => 'require|length:6,30',
                'renewpassword' => 'require|length:6,30|confirm:newpassword',
                '__token__'     => 'token',
            ];

            $msg = [
                'renewpassword.confirm' => __('Password and confirm password don\'t match')
            ];
            $data = [
                'oldpassword'   => $oldpassword,
                'newpassword'   => $newpassword,
                'renewpassword' => $renewpassword,
                '__token__'     => $token,
            ];
            $field = [
                'oldpassword'   => __('Old password'),
                'newpassword'   => __('New password'),
                'renewpassword' => __('Renew password')
            ];
            $validate = new Validate($rule, $msg, $field);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null, ['token' => $this->request->token()]);
                return false;
            }

            $ret = $this->auth->changepwd($newpassword, $oldpassword);
            if ($ret) {
                $this->success(__('Reset password successful'), url('user/login'));
            } else {
                $this->error($this->auth->getError(), null, ['token' => $this->request->token()]);
            }
        }
        $this->view->assign('title', __('Change password'));
        return $this->view->fetch();
    }

    public function attachment()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $mimetypeQuery = [];
            $where = [];
            $filter = $this->request->request('filter');
            $filterArr = (array)json_decode($filter, true);
            if (isset($filterArr['mimetype']) && preg_match("/[]\,|\*]/", $filterArr['mimetype'])) {
                $this->request->get(['filter' => json_encode(array_diff_key($filterArr, ['mimetype' => '']))]);
                $mimetypeQuery = function ($query) use ($filterArr) {
                    $mimetypeArr = explode(',', $filterArr['mimetype']);
                    foreach ($mimetypeArr as $index => $item) {
                        if (stripos($item, "/*") !== false) {
                            $query->whereOr('mimetype', 'like', str_replace("/*", "/", $item) . '%');
                        } else {
                            $query->whereOr('mimetype', 'like', '%' . $item . '%');
                        }
                    }
                };
            } elseif (isset($filterArr['mimetype'])) {
                $where['mimetype'] = ['like', '%' . $filterArr['mimetype'] . '%'];
            }

            if (isset($filterArr['filename'])) {
                $where['filename'] = ['like', '%' . $filterArr['filename'] . '%'];
            }

            if (isset($filterArr['createtime'])) {
                $timeArr = explode(' - ', $filterArr['createtime']);
                $where['createtime'] = ['between', [strtotime($timeArr[0]), strtotime($timeArr[1])]];
            }

            $model = new Attachment();
            $offset = $this->request->get("offset", 0);
            $limit = $this->request->get("limit", 0);
            $total = $model
                ->where($where)
                ->where($mimetypeQuery)
                ->where('user_id', $this->auth->id)
                ->order("id", "DESC")
                ->count();

            $list = $model
                ->where($where)
                ->where($mimetypeQuery)
                ->where('user_id', $this->auth->id)
                ->order("id", "DESC")
                ->limit($offset, $limit)
                ->select();
            $cdnurl = preg_replace("/\/(\w+)\.php$/i", '', $this->request->root());
            foreach ($list as $k => &$v) {
                $v['fullurl'] = ($v['storage'] == 'local' ? $cdnurl : $this->view->config['upload']['cdnurl']) . $v['url'];
            }
            unset($v);
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        $this->view->assign("mimetypeList", \app\common\model\Attachment::getMimetypeList());
        return $this->view->fetch();
    }

    public function getscanloginurl() {
        $common = new Common();
        $redis = $common->redisClient();
        $token = Random::uuid();
        $redis->set('scan-token-'.$token, 0, $this->token_expire);
        return ["url" =>$common->qrcode($token), "token" => $token];
    }

    public function getscanlogin() {
        $token = $_GET['token'];
        if (!$token) {
            $this->error('Invalid Token');
        }
        $common = new Common();
        $redis = $common->redisClient();
        $user_id = $redis->get('scan-token-'.$token);
        if (!$user_id) {
            $this->error("Forbidden");
        }
        $result = $this->auth->direct($user_id);
        if ($result) {
            $this->success(__('Logged in successful'), url('user/index'));
        }
        $this->error('登陆失败');
    }
}
