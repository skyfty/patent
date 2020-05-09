<?php

namespace app\common\model;
use app\admin\library\Auth;

use think\Model;
use traits\model\SoftDelete;

class Policy extends  Cosmetic
{
    // 表名
    protected $name = 'policy';
    public $keywordsFields = ["name", "idcode"];
    public $append = [
        'industry'
    ];

    protected static function init()
    {
        parent::init();

        $beforeupdate = function($row){
            if (isset($row['name'])) {
                $row['slug'] = \fast\Pinyin::get($row['name']);
            }
        };
        self::beforeInsert($beforeupdate);self::beforeUpdate($beforeupdate);

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("PO%06d", $maxid);
        });

        self::afterDelete(function($row){
            model("actualize")->where("policy_model_id", $row['id'])->delete();
            model("ordinal")->where("policy_model_id", $row['id'])->delete();
            model("project")->where("policy_model_id", $row['id'])->delete();
        });
    }


    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function ordinals()
    {
        return $this->hasMany('Ordinal','id','policy_model_id');
    }

    public function projects()
    {
        return $this->hasMany('Project','id','policy_model_id');
    }

    public function species()
    {
        return $this->hasOne('species','id','species_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function relevance()
    {
        return $this->morphOne('provider', 'provider_model');
    }

    public function industry() {
        return $this->hasManyComma('industry','id','industry_model_id');
    }

    public function condition() {
        $conditions = [];
        $ordinals = $this->ordinals()->select();
        foreach($ordinals as $ordinal) {
            $conditions[] = $ordinal['condition'];
        }
        $conditions = implode(" and ", $conditions);
        return $conditions;
    }

    public function match_principal($where = []) {
        $principal_ids = model("principal")->with($this['principalclass'])
            ->where("substance_type",$this['principalclass'])
            ->where($this->condition())->where($where)->column("principal.id");
        return $principal_ids;
    }

    public function match() {
        model("actualize")->where("policy_model_id", $this['id'])->delete();
        $data = [];
        foreach($this->match_principal() as $v) {
            $data[] = [
                "principal_model_id"=>$v,
                "policy_model_id"=>$this['id'],
            ];
        }
        model("actualize")->saveAll($data);
    }
}

