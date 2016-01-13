<?php

namespace app\modules\api\controllers;
use app\models\AccountModel;
//use app\modules\api\controllers\BaseController;
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
        $parameters = $this->getPostParameters();
        $phone = $parameters['username'];
        $passwd = $parameters['passwd'];
        $uniqueID = $parameters['uniqueID'];

        if(empty($phone) || empty($passwd)){
            $this->ApiReturnJson(300,'参数错误',array());
        }

        $AccountModel = new AccountModel();
        $ret = $AccountModel->register($phone,$passwd);

        if($ret === true){
            $this->ApiReturnJson(200,'返回成功',array());
        }else if($ret === -1){
            $this->ApiReturnJson(450,'用户已注册',array());
        }else if($ret === -2){
            $this->ApiReturnJson(460,'注册失败，请重试',array());
        }

    }

}
