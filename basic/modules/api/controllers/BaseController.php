<?php

namespace app\modules\api\controllers;
use app\models\AccountModel;
use yii\base\Controller;
use yii\base\Application;

class BaseController extends \yii\base\Controller
{
    public function ApiReturnSuccess($data, $info = '', $status = 0)
    {
        $result = array();
        $result['status'] = $status;
        $result['info'] = $info;
        //    $result['data'] = $data;
        $result['data'] = empty($data) ? array() : $data;

        header("Content-Type:text/html; charset=utf-8");
        exit(json_encode($result));
    }


    public function ApiReturnFail($info = '', $data = array(), $status = 1)
    {
        $result = array();
        $result['status'] = $status;
        $result['info'] = $info;
        $result['data'] = empty($data) ? array() : $data;
        exit(json_encode($result));
    }



}