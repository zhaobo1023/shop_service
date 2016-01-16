<?php

namespace app\modules\api\controllers;
use yii\base\Controller;
use yii\base\Application;

class BaseController extends \yii\base\Controller
{

    public function _initialize()
    {
        //入口参数存全局变量，方便今后使用
//        $data = \Yii::$app->request->post('data');
//        $request = json_decode($data,true);
//        $request = $this->getPostParameters();
//
//        \Yii::$app->params['uniqueId'] = $request['uniqueId'];
//        \Yii::$app->params['systemName'] = $request['systemName'];
//        \Yii::$app->params['systemVersion'] = $request['systemVersion'];
//        \Yii::$app->params['deviceType'] = $request['deviceType'];
//        \Yii::$app->params['apiLevel'] = $request['apiLevel'];
    }

    /**
     * parameter receive
     * */
    public function getPostParameters(){
        $data = \Yii::$app->getRequest()->getRawBody();
        $request = json_decode($data,true);
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
//        var_dump($result);
        exit(json_encode($result));
    }


    public function create_uuid()
    {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        $uuid =
            substr($charid, 0, 8) .
            substr($charid, 8, 4) .
            substr($charid, 12, 4) .
            substr($charid, 16, 4) .
            substr($charid, 20, 12);
        return $uuid;
    }



}