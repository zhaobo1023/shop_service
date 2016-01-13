<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Account is the model behind the register,login,and token model.
 */
class AccountModel extends Model
{
    public $userName;
    public $passwd;
    public $uniqueID;
    public $token;

    public function register($userName,$passwd)
    {
        $salt = '_user_passwd';
        $passwd_save = md5($passwd.$salt);

        $connection = \Yii::$app->db;

        $command = $connection->createCommand('SELECT * FROM user_account WHERE phone_number='.$userName);
        $find = $command->queryOne();

        if(!$find){
            $user_account_data = array(
                'nick_name' => $userName,
                'head_image_url' => '',
                'email' => '',
                'ctime' => time(),
                'phone_number' => $userName,
                'gender' => 1,
                'passwd' => $passwd_save,
            );


            $ret = $connection->createCommand()->insert('user_account', $user_account_data)->execute();
            if($ret){
                return true;
            }else{
                return -1;
            }
        }else{
            return -2;
        }

    }



}