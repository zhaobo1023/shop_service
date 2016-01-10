<?php

namespace app\modules\api\controllers;
use yii\base\Controller;
use yii\base\Application;


class AccountController extends \yii\base\Controller
{
    // ...
    public function actionIndex()
    {
        echo 123;
    }

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

        $connection = \Yii::$app->db;

//        $phone = 13811858523;
        $command = $connection->createCommand('SELECT * FROM user_account WHERE phone_number='.$phone);
        $find = $command->queryOne();

        if(!$find){
            $user_account_data = array(
                'nick_name' => $phone,
                'head_image_url' => '',
                'email' => '',
                'ctime' => time(),
                'phone_number' => $phone,
                'gender' => 1,
                'passwd' => $passwd,
            );


            $ret = $connection->createCommand()->insert('user_account', $user_account_data)->execute();
            if($ret){
                $this->ApiReturnSuccess(array(),'返回成功',200);
            }
        }else{
            $this->ApiReturnFail(array(),'用户已注册',450);
        }

    }

    public function ApiReturnSuccess($data, $info = '', $status = 0)
    {
        $result = array();
        $result['status'] = $status;
        $result['info'] = $info;
        //    $result['data'] = $data;
        $result['data'] = empty($data) ? new stdClass() : $data;

        header("Content-Type:text/html; charset=utf-8");
        exit(json_encode($result));
    }


    public function ApiReturnFail($info = '', $data = array(), $status = 1)
    {
        $result = array();
        $result['status'] = $status;
        $result['info'] = $info;
        $result['data'] = empty($data) ? new stdClass() : $data;
        exit(json_encode($result));
    }

}
