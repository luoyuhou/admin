<?php


namespace app\api\controller;
use app\common\controller\Api;


class File extends Api
{
    protected $product = null;


    public function _initialize()
    {
        parent::_initialize();
        $this->product = new Common();
    }

    public function upload()
    {
//        $this->product->upload();
        dump($this->product);
    }

}