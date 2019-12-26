<?php

namespace app\admin\model;
use app\admin\library\Auth;

use think\Model;
use traits\model\SoftDelete;

class Principal extends  \app\common\model\Principal
{
    // 追加属性

    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();
    }


}