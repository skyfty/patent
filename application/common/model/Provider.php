<?php

namespace app\common\model;

use think\Db;
use think\Hook;
use Endroid\QrCode\QrCode;

class Provider extends Cosmetic
{
    protected $name = 'provider';

    public $keywordsFields = ["idcode"];

    public function getSelectField($name, $value) {
        $list= Fields::get(['name'=>$name,'model_table'=>$this->name],[],true)->content_list;
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected static function upstat($row) {
        $quantity = self::where(['branch_model_id'=>$row->branch_model_id])->count();
        $where = ["table"=>"provider","field"=>'quantity', 'branch_model_id'=>$row->branch_model_id];
        $stat = new Statistics();
        if (($ns = $stat->where($where)->find()) == null) {
            $stat->data($where, true);
        } else $stat = $ns;
        $stat->save(['value' => $quantity]);
    }

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $row['name'] = $row->promotion->name;
            $row['state'] =1;
        });


        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("PV%06d", $maxid);
        });


        $countProvider = function($row) {
            $ids = self::where("staff_model_id", $row['staff_model_id'])->column("id");
            if ($ids) {
                $ids = implode(",", $ids);
                model("staff")->save(['provider_ids'=>$ids], ["id"=>$row['staff_model_id']]);
            }
            self::upstat($row);
        };
        self::afterInsert($countProvider);self::afterDelete($countProvider);
    }

    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function staff() {
        return $this->hasOne('staff','id','staff_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function promotion() {
        return $this->hasOne('promotion','id','promotion_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function amount() {

    }
}
