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
                'passwd' => $passwd,
            );


            $ret = $connection->createCommand()->insert('user_account', $user_account_data)->execute();
            if($ret){
            }
        }else{
            return -2;
        }

    }



}