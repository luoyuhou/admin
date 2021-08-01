<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use fast\Http;
use fast\Random;
use think\Console;
use think\exception\PDOException;
use think\Log;
use think\Validate;
use think\Env;
use app\common\behavior\Common;
use think\Request;

/**
 * 会员接口
 */
class User extends Api
{
    protected $noNeedLogin = ['login', 'mobilelogin', 'register', 'resetpwd', 'changeemail', 'changemobile', 'third', 'wxlogin', 'binduserprofile'];
    protected $noNeedRight = '*';
    protected $redisClient;
    protected $users_token_map = 'users_token_map';

    public function _initialize()
    {
        parent::_initialize();
        $comm = new Common();
        $this->redisClient = $comm->redisClient();
    }

    /**
     * 会员中心
     */
    public function index()
    {
        $this->success('', ['welcome' => $this->auth->nickname]);
    }

    /**
     * 会员登录
     *
     * @param string $account  账号
     * @param string $password 密码
     */
    public function login()
    {
        $account = $this->request->request('account');
        $password = $this->request->request('password');
        if (!$account || !$password) {
            $this->error(__('Invalid parameters'));
        }
        $ret = $this->auth->login($account, $password);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 手机验证码登录
     *
     * @param string $mobile  手机号
     * @param string $captcha 验证码
     */
    public function mobilelogin()
    {
        $mobile = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (!Sms::check($mobile, $captcha, 'mobilelogin')) {
            $this->error(__('Captcha is incorrect'));
        }
        $user = \app\common\model\User::getByMobile($mobile);
        if ($user) {
            if ($user->status != 'normal') {
                $this->error(__('Account is locked'));
            }
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        } else {
            $ret = $this->auth->register($mobile, Random::alnum(), '', $mobile, []);
        }
        if ($ret) {
            Sms::flush($mobile, 'mobilelogin');
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 注册会员
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $email    邮箱
     * @param string $mobile   手机号
     * @param string $code   验证码
     */
    public function register()
    {
        $username = $this->request->request('username');
        $password = $this->request->request('password');
        $email = $this->request->request('email');
        $mobile = $this->request->request('mobile');
        $code = $this->request->request('code');
        if (!$username || !$password) {
            $this->error(__('Invalid parameters'));
        }
        if ($email && !Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        $ret = Sms::check($mobile, $code, 'register');
        if (!$ret) {
            $this->error(__('Captcha is incorrect'));
        }
        $ret = $this->auth->register($username, $password, $email, $mobile, []);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Sign up successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        $this->auth->logout();
        $this->success(__('Logout successful'));
    }

    /**
     * 修改会员个人信息
     *
     * @param string $avatar   头像地址
     * @param string $username 用户名
     * @param string $nickname 昵称
     * @param string $bio      个人简介
     */
    public function profile()
    {
        $user = $this->auth->getUser();
        $username = $this->request->request('username');
        $nickname = $this->request->request('nickname');
        $bio = $this->request->request('bio');
        $avatar = $this->request->request('avatar', '', 'trim,strip_tags,htmlspecialchars');
        if ($username) {
            $exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Username already exists'));
            }
            $user->username = $username;
        }
        if ($nickname) {
            $exists = \app\common\model\User::where('nickname', $nickname)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Nickname already exists'));
            }
            $user->nickname = $nickname;
        }
        $user->bio = $bio;
        $user->avatar = $avatar;
        $user->save();
        $this->success();
    }

    /**
     * 修改邮箱
     *
     * @param string $email   邮箱
     * @param string $captcha 验证码
     */
    public function changeemail()
    {
        $user = $this->auth->getUser();
        $email = $this->request->post('email');
        $captcha = $this->request->request('captcha');
        if (!$email || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if (\app\common\model\User::where('email', $email)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Email already exists'));
        }
        $result = Ems::check($email, $captcha, 'changeemail');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->email = 1;
        $user->verification = $verification;
        $user->email = $email;
        $user->save();

        Ems::flush($email, 'changeemail');
        $this->success();
    }

    /**
     * 修改手机号
     *
     * @param string $mobile   手机号
     * @param string $captcha 验证码
     */
    public function changemobile()
    {
        $user = $this->auth->getUser();
        $mobile = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (\app\common\model\User::where('mobile', $mobile)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Mobile already exists'));
        }
        $result = Sms::check($mobile, $captcha, 'changemobile');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->mobile = 1;
        $user->verification = $verification;
        $user->mobile = $mobile;
        $user->save();

        Sms::flush($mobile, 'changemobile');
        $this->success();
    }

    /**
     * 第三方登录
     *
     * @param string $platform 平台名称
     * @param string $code     Code码
     */
    public function third()
    {
        $url = url('user/index');
        $platform = $this->request->request("platform");
        $code = $this->request->request("code");
        $config = get_addon_config('third');
        if (!$config || !isset($config[$platform])) {
            $this->error(__('Invalid parameters'));
        }
        $app = new \addons\third\library\Application($config);
        //通过code换access_token和绑定会员
        $result = $app->{$platform}->getUserInfo(['code' => $code]);
        if ($result) {
            $loginret = \addons\third\library\Service::connect($platform, $result);
            if ($loginret) {
                $data = [
                    'userinfo'  => $this->auth->getUserinfo(),
                    'thirdinfo' => $result
                ];
                $this->success(__('Logged in successful'), $data);
            }
        }
        $this->error(__('Operation failed'), $url);
    }

    /**
     * 重置密码
     *
     * @param string $mobile      手机号
     * @param string $newpassword 新密码
     * @param string $captcha     验证码
     */
    public function resetpwd()
    {
        $type = $this->request->request("type");
        $mobile = $this->request->request("mobile");
        $email = $this->request->request("email");
        $newpassword = $this->request->request("newpassword");
        $captcha = $this->request->request("captcha");
        if (!$newpassword || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if ($type == 'mobile') {
            if (!Validate::regex($mobile, "^1\d{10}$")) {
                $this->error(__('Mobile is incorrect'));
            }
            $user = \app\common\model\User::getByMobile($mobile);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Sms::check($mobile, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Sms::flush($mobile, 'resetpwd');
        } else {
            if (!Validate::is($email, "email")) {
                $this->error(__('Email is incorrect'));
            }
            $user = \app\common\model\User::getByEmail($email);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Ems::check($email, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Ems::flush($email, 'resetpwd');
        }
        //模拟一次登录
        $this->auth->direct($user->id);
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret) {
            $this->success(__('Reset password successful'));
        } else {
            $this->error($this->auth->getError());
        }
    }

    public function scanloginweb() {
        $code = $this->request->request('code');
        $user = $this->auth->getUserinfo();
        if (empty($user)) {
            $this->error('请重新登陆小程序');
        }
        $this->redisClient->set('scan-token-'.$code, $user['id'], 30);
        $this->success('扫描成功');
    }

    public function wxlogin() {
        $token = $this->request->server('HTTP_TOKEN', $this->request->request('token', \think\Cookie::get('token')));
        if (!empty($token) || $this->auth->check()) {
            $this->success('', $this->auth->getUserinfo());
        }
        $code = $_GET['code'];
        $domain = Env::get('wx.login_url', '');
        $appId = Env::get('wx.appId', '');
        $secret = Env::get('wx.secret', '');
        $url = $domain . '/sns/jscode2session?appid=' . $appId . "&secret=" .$secret."&js_code=" .$code. "&grant_type=authorization_code";
        $output = Http::sendRequest($url);
        if (empty($output['msg'])) {
            $this->error('登陆失败');
        }
        $sess = json_decode($output['msg'], true);
        if (empty($sess['openid'])) {
            $this->error('登陆失败');
        }
        $wx_id = $sess['openid'];
        try {
            $user = \app\common\model\User::get(['wx_id' => $wx_id]);
            $token = $this->getweixinlogintoken($wx_id);
            if (empty($token)) {
                $this->error('未知用户');
            }
            if (empty($user)) {
                // insert
                $insert = $this->createUser($wx_id);
                if ($insert) {
                    $this->success('绑定用户信息', ['token' => $token], 301);
                }
            } else {
                // login
                if (empty($user->username)) {
                    $this->success('绑定用户信息', ['token' => $token], 301);
                }
                $login = $this->autologin($user->id);
                if ($login) {
                    $this->success('', $this->auth->getUserinfo());
                }
            }
        } catch (PDOException $error) {
            Log::error('[select user] Error: '.$error);
        }
        $this->error('登陆失败');
    }

    private function getweixinlogintoken($wx_id) {
        if(empty($wx_id)) {
            return '';
        }
        $token = $this->redisClient->hget($this->users_token_map, $wx_id);
        if (empty($token)) {
            $token = Random::uuid();
            $this->redisClient->hset($this->users_token_map, $wx_id, $token);
            $this->redisClient->set($token, $wx_id, 5 * 60);
        } else {
            $exist = $this->redisClient->get($token);
            if (empty($exist)) {
                $token = Random::uuid();
                $this->redisClient->hset($this->users_token_map, $wx_id, $token);
                $this->redisClient->set($token, $wx_id, 5 * 60);
            }
        }
        return $token;
    }

    private function createUser($wx_id) {
        try {
            return \app\common\model\User::create(['wx_id' => $wx_id]);
        } catch(PDOException $error) {
            Log::error('[create user] Error: '.$error);
            return false;
        }
    }

    private function autologin($user_id){
        return $this->auth->direct($user_id);
    }

    public function binduserprofile() {
        $token = $this->request->post('token');
        if (empty($token)) {
            $this->error('token required', '', 400);
        }
        $wx_id = $this->redisClient->get($token);
        if (empty($wx_id)) {
            $this->error('已过有效期，请重新登陆!', '', 403);
        }
        $user = \app\common\model\User::get(['wx_id' => $wx_id]);
        if (empty($user)) {
            $this->error('用户不存在，请重新登陆!');
        }
        $id = $user['id'];
        $username = $this->request->post('username');
        $nickname = $this->request->post('nickname');
        $password = $this->request->post('password');
        if (!empty($password)) {
            $salt = Random::alnum();
            $password = $this->auth->getEncryptPassword($password, $salt);
        } else {
            $password = null;
            $salt = null;
        }
        $email = $this->request->post('email');
        $mobile = $this->request->post('mobile');
        $avatar = $this->request->post('avatar');
        $gender = $this->request->post('gender');
        $bio = $this->request->post('bio');
        $birthday = $this->request->post('birthday');
        $status = 'normal';
        $time = time();
        $logintime = $time;
        $prevtime = $time;
        $jointime = $time;
        $ip = Request::instance()->ip();
        $loginip = $ip;
        $joinip = $ip;
        try {
            $res = \app\common\model\User::update([
                'username' => $username,
                'nickname' => $nickname,
                'password' => $password,
                'salt'     => $salt,
                'email'    => $email,
                'mobile'   => $mobile,
                'avatar'  => $avatar,
                'gender'  => $gender,
                'bio'     => $bio,
                'birthday' => $birthday,
                'status'  => $status,
                'logintime' => $logintime,
                'prevtime'  => $prevtime,
                'jointime'  => $jointime,
                'loginip'   => $loginip,
                'joinip'    => $joinip
            ], ['id' => $id]);
            if (empty($res)) {
                $this->error('绑定信息失败');
            }
            $logined = $this->auth->direct($id);
            if ($logined) {
                $this->success('', $this->auth->getUserinfo());
            }
            $this->error('登陆失败');
        } catch (PDOException $error) {
            Log::error('[bind user profile] Error: '.$error);
            $this->error('绑定出错了');
        }
    }
}
