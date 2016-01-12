<?php

namespace app\modules\api\controllers;
use app\models\AccountModel;
//use yii\base\Controller;
//use yii\base\Application;


class AccountController extends BaseController
{
    // ...
    public function actionIndex()
    {
        echo 123;
    }


    /**
     * 用户注册
     * author:zhaobo1023@gmail.com
     * return:json
     * */
    public function actionRegister()
    {
        $request = \Yii::$app->request;
        $phone = $request->post('username');
        $salt = '_user_passwd';
        $passwd = md5($request->post('passwd').$salt);
        $uniqueID = $request->post('uniqueID');

        if(empty($phone) || empty($passwd)){
            $this->ApiReturnFail(array(),'参数错误',300);
        }

        $AccountModel = new AccountModel();
        $ret = $AccountModel->register($phone,$passwd);

        if($ret === true){
            $this->ApiReturnSuccess(array(),'返回成功',200);
        }else if($ret === -1){
            $this->ApiReturnFail(array(),'用户已注册',450);
        }else if($ret === -2){
            $this->ApiReturnFail(array(),'注册失败，请重试',5000);
        }

    }

}
