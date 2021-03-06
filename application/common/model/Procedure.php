<?php

namespace app\common\model;

use think\Model;

class Procedure extends Cosmetic
{
    // 表名
    protected $name = 'procedure';
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("CO%06d", $maxid);
        });


        self::beforeInsert(function($row){
            $row['relevance_model_type'] = $row->species->model;
        });

        $updateSpecies = function($row){
            model("alternating")->where("procedure_model_id", $row['id'])->delete();
            $row->species->updateProcedureCount();
        };
        self::afterDelete($updateSpecies);self::afterInsert($updateSpecies);
    }
    public function alternatings()
    {
        return $this->hasMany('alternating','procedure_model_id');
    }
    public function species()
    {
        return $this->hasOne('species','id','species_cascader_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function auth() {
        return $this->hasOne('AuthGroup','id','auth_model_id')->joinType("LEFT")->field("id,name,auth_department_id")->setEagerlyType(0);
    }

    public function divisions()
    {
        return $this->hasMany('division','procedure_model_id');
    }
}
