<?php

namespace app\wxapp\controller;

use think\Db;

/**
 * CMS首页控制器
 * Class Index
 * @package app\wechat\controller
 */
class Provider extends Wxapp
{
    protected $noNeedLogin = ['evaluate'];

    public function __construct() {
        parent::__construct();
        $this->model = model("provider");
        $this->relationSearch =  ["staff", "customer", "promotion"];
    }

    public function index() {

        return $this->view->fetch();
    }
}
