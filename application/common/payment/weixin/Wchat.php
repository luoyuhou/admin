<?php
namespace payment\weixin;
use Db;
class Wchat
{
    public function __construct()
    {
        require __DIR__."/lib/WxPay.Api.php";
        require __DIR__."/index/WxPay.NativePay.php";
        require __DIR__."/lib/WxPay.Exception.php";
        require __DIR__."/lib/WxPay.Config.Interface.php";
        require __DIR__."/lib/WxPay.Data.php";
        require __DIR__."/index/WxPay.Config.php";
        require __DIR__."/index/phpqrcode/phpqrcode.php";
    }

    public function wchatpay($orderId)
    {
        $order = Db::name("order")->where("id",$orderId)->find();
        $notify = new \NativePay();
        $input = new \WxPayUnifiedOrder();
        $input->SetBody("测试微信pay");
        $input->SetOut_trade_no($order["order_id"]);
        $input->SetTotal_fee($order["money"]);
        $input->SetNotify_url("http://localhost:8000/home/pay/notify");
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id($order["id"]);

        $result = $notify->GetPayUrl($input);
        $url= $result["code_url"];

        $filename = md5(time()).".png";
        $path = "./weixincode/".$filename;
        \QRcode::png("http://www.baidu.com",$path);
        return "/weixincode/".$filename;
    }
}

