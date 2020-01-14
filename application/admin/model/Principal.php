<?php

namespace app\admin\model;
use app\admin\library\Auth;

use think\App;
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

        self::afterUpdate(function($row){
            $scenery = Scenery::where(["model_table"=>$row['substance_type'],"pos"=>'view'])->cache(!App::$debug)->find();
            $fields = Sight::with('fields')->cache(!App::$debug)->where(['scenery_id'=>$scenery['id']])->column("fields.name");
            $data = [];
            foreach($row->getData() as $k=>$v) {
                if (in_array($k, $fields)) {
                    $data[$k] = $v;
                }
            }
            model($row['substance_type'])->where("id",$row['substance_id'])->find()->allowField($fields)->save($data);
        });

    }


}
