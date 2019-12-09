<?php

namespace app\admin\model;

use app\admin\model\Config;
use think\Model;
use traits\model\SoftDelete;

class Archives extends Model
{

    use SoftDelete;

    // 表名
    protected $name = 'cms_archives';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';
    // 追加属性
    protected $append = [
        'flag_text',
        'status_text',
        'publishtime_text',
        'url',
    ];

    public function getUrlAttr($value, $data)
    {
        $authurl = \think\Config::get("wechat.authurl");
        return url('archives/view', ['id' => $data['id'], 'channel' => $data['channel_id']], true, $authurl);
    }

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
            Channel::where('id', $row['channel_id'])->setInc('items');
        });
        self::beforeWrite(function ($row) {
            //在更新之前对数组进行处理
            foreach ($row->getData() as $k => $value) {
                if (is_array($value) && isset($value['field'])) {
                    $value = json_encode(Config::getArrayData($value), JSON_UNESCAPED_UNICODE);
                } else {
                    $value = is_array($value) ? implode(',', $value) : $value;
                }
                $row->$k = $value;
            }
        });
        self::afterWrite(function ($row) {
            if (isset($row['tags'])) {
                $tags = array_filter(explode(',', $row['tags']));
                if ($tags) {
                    $tagslist = Tags::where('name', 'in', $tags)->select();
                    foreach ($tagslist as $k => $v) {
                        $archives = explode(',', $v['archives']);
                        if (!in_array($row['id'], $archives)) {
                            $archives[] = $row['id'];
                            $v->archives = implode(',', $archives);
                            $v->nums++;
                            $v->save();
                        }
                        $tags = array_diff($tags, [$v['name']]);
                    }
                    $list = [];
                    foreach ($tags as $k => $v) {
                        $list[] = ['name' => $v, 'archives' => $row['id'], 'nums' => 1];
                    }
                    if ($list) {
                        model('Tags')->saveAll($list);
                    }
                }
            }
        });
    }

    public function getFlagList()
    {
        return ['hot' => __('Hot'), 'new' => __('New'), 'recommend' => __('Recommend')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getFlagTextAttr($value, $data)
    {
        $value = $value ? $value : $data['flag'];
        $valueArr = $value ? explode(',', $value) : [];
        $list = $this->getFlagList();
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getPublishtimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['publishtime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setPublishtimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    public function channel()
    {
        return $this->belongsTo('Channel', 'channel_id', '', [], 'LEFT')->setEagerlyType(0);
    }

}
