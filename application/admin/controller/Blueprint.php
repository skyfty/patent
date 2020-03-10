<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 资质管理
 *
 * @icon fa fa-circle-o
 */
class Blueprint extends Cosmetic
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Blueprint;
    }


    protected function spectacle($model) {
        return $model;
    }
}