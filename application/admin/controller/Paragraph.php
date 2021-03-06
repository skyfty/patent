<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 段落管理
 *
 * @icon fa fa-paragraph
 */
class Paragraph extends Cosmetic
{
    
    /**
     * Paragraph模型对象
     * @var \app\admin\model\Paragraph
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Paragraph;
    }

    /**
     * 添加
     */
    public function edit($ids = NULL) {
        $row = $this->model->get($ids);
        if (!$row) {
            $row = [
                'promotion_model_id'=>$this->request->param("promotion_model_id"),
                'exlecture_model_id'=>$this->request->param("exlecture_model_id"),
            ];
        }
        $this->view->assign("row", $row);

        return $this->view->fetch();
    }

    protected function spectacle($model) {
        return $model;
    }
}
