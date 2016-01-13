<?php

namespace app\modules\api\controllers;
use yii\base\Controller;
use yii\base\Application;

class BaseController extends \yii\base\Controller
{

//    protected function _initialize()
//    {
//
//    }

    /**
     * parameter receive
     * */
    public function getPostParameters(){
        $request = \Yii::$app->request->post();
        return $request;
    }

//    private function ParameterCheck(){
//
//
//    }


    /**
     * API参数返回接口
     * */
    public function ApiReturnJson($code,$desc,$ret_array)
    {
        $result = array();
        $result['retCode'] = $code;
        $result['retDesc'] = $desc;
        $result['ret'] = $ret_array;
        header("Content-Type:application/json; charset=utf-8");
        exit(json_encode($result));
    }




}