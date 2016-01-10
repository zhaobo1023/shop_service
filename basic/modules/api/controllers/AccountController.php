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
        $phone = $request->post('phone');
        $salt = '_user_passwd';
        $passwd = md5($request->post('passwd').$salt);
        $device_id = $request->post('device_id');

        $connection = \Yii::$app->db;
        var_dump($connection);

        $phone = '13811858523';
        $passwd = 121312;
        $user_account_data = array(
            'nick_name' => $phone,
            'head_image_url' => '',
            'email' => '',
            'ctime' => time(),
            'phone_number' => $phone,
            'gender' => 1,
            'psswd' => $passwd,
        );

        $ret = $connection->createCommand()->insert('user_account', $user_account_data)->execute();
        var_dump($ret);

    }

}
