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

    /**
     * 手机注册用户接口
     * 已测试[成功]
     * 错误状态码
     * 2  用户已存在
     * 3  注册失败
     * 4  更新用户信息失败
     */
    public function reg_user_by_phone()
    {
        $reqArray = json_decode($_POST['data'], true);
        $phone_number = $reqArray['phone_number'];
        if (default_sdk()->AccountSwooleService()->UserExists($phone_number, USER_VERIFY_TYPE_PHONE_PASSWORD)) {
            Monitor::reportCount(REGISTER_BY_PHONE_ERROR);
            LogItil::write('reg_user_by_phone,user existed,phone_number:' .$phone_number,LogItil::LEVEL_ERROR);
            ApiReturnFail('用户已存在', array(), 2);
        }
        $sms_code = $reqArray['sms_code'];
        $password = $reqArray['password'];
        $nick_name = $reqArray['user_info']['nick_name'];
        $head_image_url = $reqArray['user_info']['head_image_url'];
        $gender = $reqArray['user_info']['gender'];
        //职业属性
        $occupation = $reqArray['user_info']['occupation'];
        if (empty($password)) {
            $password = $reqArray['user_info']['password'];
        }
        if (empty($password)) {
            Monitor::reportCount(REGISTER_BY_PHONE_ERROR);
            LogItil::write('reg_user_by_phone,empty password,phone_number:' .$phone_number,LogItil::LEVEL_ERROR);
            ApiReturnFail('密码空!', array(), 3);
        }


        $success = default_sdk()->AccountSwooleService()->addNewUserByPhone($phone_number, $password, $sms_code);
        if ($success) {
            //优惠劵发放限制 for 孙维 20150922
            if( ! isset($reqArray['channel']) || $reqArray['channel'] != 'cpa_zhy'){
                default_sdk()->CouponService()->giveCouponToNewUser($success); // 新注册用户发放优惠券
            }
            $accessToken = default_sdk()->AccountSwooleService()->getAccessTokenByPhoneAndPassword($phone_number, $password);
            if (!$accessToken) {
                ApiReturnFail('登陆失败,token过期', array(), 8);
            } else {
                $UserModel = default_sdk()->ApiAccountSwooleService()->getUserMesByAccessToken($accessToken, $nick_name, $head_image_url, $gender, $occupation);
                if ($UserModel['status']) {
                    exit(json_encode($UserModel));
                } else {
                    ApiReturnSuccess(array('token' => $accessToken, 'userId' => $UserModel['userId'], 'user_info' => $UserModel));
                }
            }
        } else {
            Monitor::reportCount(REGISTER_BY_PHONE_ERROR);
            LogItil::write('reg_user_by_phone,register error,phone_number:' .$phone_number,LogItil::LEVEL_ERROR);
            ApiReturnFail('注册失败', array(), 3);
        }

    }
}
