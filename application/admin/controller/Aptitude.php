<?php

namespace app\admin\controller;
ini_set("error_reporting","E_ALL & ~E_NOTICE");

use app\admin\model\Modelx;
use app\common\controller\Backend;
use PclZip;
use think\App;
use think\Log;

/**
 * 资质管理
 *
 * @icon fa fa-circle-o
 */
class Aptitude extends Cosmetic
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Aptitude;
    }

    use \app\admin\library\traits\Produce;

    public function download($id) {
        $row = $this->model->where("id",$id)->find();
        if (!$row)
            $this->error(__('No Results were found'));

        $tempPath = TEMP_PATH .\fast\Random::build("unique");
        mkdir($tempPath);

        $procedures = model("procedure")->where([
            "relevance_model_type"=>strtolower($this->model->raw_name),
        ])->select();
        foreach($procedures as $procedure) {
            $procedurePath = $tempPath."/".$procedure['name'];
            mkdir($procedurePath);

            $procshutters = model("procshutter")->where([
                "procedure_model_id"=> $procedure['id'],
                "relevance_model_type"=>strtolower($this->model->raw_name),
                "relevance_model_id"=> $row['id'],
                "status"=> "normal",
            ])->select();

            foreach($procshutters as $procshutter) {
                $file = $procshutter['file'];
                if ($file == null)
                    continue;
                $srcfile = ROOT_PATH . 'public' .$file;
                if (!file_exists($srcfile)) {
                    continue;
                }
                $pi = pathinfo($file);
                $newfile = $procedurePath."//".$procshutter['name'].".".$pi['extension'];
                $newfile = iconv('utf-8','gb2312',$newfile);
                copy($srcfile,$newfile);
            }
        }

        $procshutterdir = '/procshutter/'.\fast\Random::build("unique").".zip";
        $destFileDir =ROOT_PATH . '/public' . $procshutterdir;
        system(sprintf("zip -rj %s %s", $destFileDir, $tempPath), $status);
        rmdirs($tempPath, true);
        $this->redirect($procshutterdir);
    }

    protected function spectacle($model) {
        $branch_model_id = $this->request->param("branch_model_id");
        if ($branch_model_id == null) {
            if ($this->auth->isSuperAdmin() || !$this->admin || !$this->admin['staff_id']) {
                return $model;
            }
        }
        $branch_model_id = $branch_model_id != null ?$branch_model_id: $this->staff->branch_model_id;

        $model->where("aptitude.branch_model_id", $branch_model_id);

        return $model;
    }
}
