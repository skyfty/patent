<?php

namespace app\customer\controller;
use app\common\model\Fields;
use think\Request;


/**
 * CMS首页控制器
 * Class Index
 * @package app\wechat\controller
 */
class Principal extends Customer
{
    protected $layout = 'principal/layout';

    // 初始化
    public function __construct()
    {
        parent::__construct();
        $this->model = model('principal');
    }

    public function index() {
        $list = $this->model->with($this->relationSearch)->where("id","in", function($query){
            $query->table("__CLAIM__")->where("customer_model_id", $this->user->customer->id)->field("principal_model_id");
        })->order("principalclass_model_id asc")->select();

        $model_type = ['persion','company'];
        $principallclass = model("Principalclass");
        foreach($list as $v) {
            if ($v['substance_type'] == "persion") {
                unset($model_type[0]);
            }else if ($v['substance_type'] == "company") {
                if ($v->company->signed == "no") {
                    unset($model_type[1]);
                }
            }
        }
        $this->view->assign("principal_class", $principallclass->where("model_type", "in", $model_type)->select());
        $this->view->assign("list", $list);
        return $this->view->fetch();
    }

    public function view() {
        $id =$this->request->param("id", null);
        if ($id === null)
            $this->error(__('Params error!'));

        $row = $this->model->find($id);
        if (!$row)
            $this->error(__('No Results were found'));
        $row->append(["substance","promotions","actualizes"]);
        $this->success("OK","/",$row);
    }
    public function edit($ids = null) {
        $principal = $this->model->get($ids);
        if (!$principal) {
            $this->error(__('No Results were found'));
        }

        if ($this->request->isPost()) {
            $params = $this->request->param("row/a");

            $db = $this->model->getQuery();
            $db->startTrans();
            try {
                $substance = $principal->substance->save($params);
                if ($substance === false) {
                    throw new \think\Exception($this->model->getError());
                }
                $db->commit();
                $this->success("成功", "/principal/index?id=".$principal->id);

            } catch (\think\exception\PDOException $e) {
                $db->rollback();
                $this->error($e->getMessage());
            }catch(\think\Exception $e) {
                $db->rollback();
                $this->error($e->getMessage());
            }
        }
        $this->view->assign("row", $principal['substance']);
        $template = "principal/class/".$principal['substance_type'];
        $this->view->assign('refere_url', Request::instance()->server('HTTP_REFERER'));
        return $this->view->fetch($template);
    }

    public function add() {
        $principalclass_model_id = $this->request->param("principalclass_model_id");
        if (!$principalclass_model_id) {
            $this->error(__('No Results were found'));
        }
        $principalclass = model("Principalclass")->get($principalclass_model_id);
        if (!$principalclass) {
            $this->error(__('No Results were found'));
        }

        if ($this->request->isPost()) {
            $params = $this->request->param("row/a");
            if ($principalclass['id'] ==2) {
                $principal = model("principal")->where("name",$params['name'])->find();
                if ($principal) {
                    $this->error("主体名称重复");
                }
            }

            $db = $this->model->getQuery();
            $db->startTrans();
            try {
                $principal = model("principal")->create([
                    "name"=>$params['name'],
                    "principalclass_model_id"=>$principalclass_model_id
                ]);
                if ($principal === false) {
                    throw new \think\Exception($this->model->getError());
                }
                $substance = $principal->substance->save($params);
                if ($substance === false) {
                    throw new \think\Exception($this->model->getError());
                }
                $claim = model("claim")->create([
                    "customer_model_id"=>$this->user->customer->id,
                    "principal_model_id"=>$principal['id']

                ]);
                if ($claim !== false) {
                    $db->commit();
                    $this->success("主体添加成功", "/principal/index?id=".$principal->id);
                }
            } catch (\think\exception\PDOException $e) {
                $db->rollback();
                $this->error($e->getMessage());
            }catch(\think\Exception $e) {
                $db->rollback();
                $this->error($e->getMessage());
            }
        }
        $this->view->assign("principal_class", $principalclass);
        $template = "principal/class/".$principalclass['model_type'];
        $this->view->assign('refere_url', Request::instance()->server('HTTP_REFERER'));
        $this->view->assign("row", []);
        return $this->view->fetch($template);
    }
}
