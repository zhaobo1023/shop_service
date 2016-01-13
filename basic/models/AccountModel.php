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
        $salt = \Yii::$app->params['passwdSalt'];

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

    public function getUserPasswd($userName)
    {
        $rows = (new \yii\db\Query())
            ->select(['passwd'])
            ->from('user_account')
            ->where(['phone_number' => $userName])
            ->limit(1)
            ->all();
        return $rows[0]['passwd'];
    }



}