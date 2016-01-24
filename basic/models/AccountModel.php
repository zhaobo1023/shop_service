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
    private $keyLiveList = 'live_list_';


    public function register($userName, $passwd)
    {
        $connection = \Yii::$app->db;
        $command = $connection->createCommand('SELECT * FROM user_account WHERE phone_number=' . $userName);
        $find = $command->queryOne();

        if (!$find) {
            $user_account_data = array(
                'nick_name' => '',
                'head_image_url' => '',
                'email' => '',
                'ctime' => time(),
                'phone_number' => $userName,
                'gender' => 1,
                'passwd' => $passwd,
                'company' => '',
            );


            $ret = $connection->createCommand()->insert('user_account', $user_account_data)->execute();
            if ($ret) {
                return true;
            } else {
                return -1;
            }
        } else {
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

    public function getUserInfo($userName)
    {
        $rows = (new \yii\db\Query())
            ->select('*')
            ->from('user_account')
            ->where(['phone_number' => $userName])
            ->limit(1)
            ->all();
        return $rows[0];
    }

    public function getUserInfoByWhere($where)
    {
        if(!empty($where)){
            $rows = (new \yii\db\Query())
                ->select('*')
                ->from('user_account')
                ->where($where)
                ->limit(1)
                ->all();
            return $rows;
        }else{
            return array();
        }

    }

    public function updateUserInfoByWhere($where,$data)
    {
        if(!empty($where) && !empty($data)){
            $connection = \Yii::$app->db;
            $ret = $connection->createCommand()->update('user_account', $data,$where)->execute();
            return $ret;
        }else{
            return array();
        }

    }

    public function  setAccessTokenAndUserId($token, $user_id)
    {
//        $tokenTime = 3600 * 24 * 30 * 12;
        $ret = \Yii::$app->redis->set('token_' . $token, $user_id);  //设置redis缓存
        return $ret;
    }

    public function addToLiveTokenList($token, $userId)
    {
        $liveList = array();
        $key = $this->keyLiveList . $userId;
        $liveList[$key] = $token;
        $ret = \Yii::$app->redis->executeCommand('SADD', [$key, $token]);  //设置redis缓存
        return $ret;
    }

    public function renewToken($oldToken, $userId, $newToken)
    {
        \Yii::$app->redis->del('token_' . $oldToken);  //设置redis缓存
        \Yii::$app->redis->set('token_' . $newToken, $userId);  //设置redis缓存

        $key = $this->keyLiveList . $userId;
        $ret = \Yii::$app->redis->executeCommand('SMEMBERS', [$key]);  //设置redis缓存
        if (in_array($oldToken, $ret)) {
            \Yii::$app->redis->executeCommand('SREM', [$key, $oldToken]);  //设置redis缓存
            \Yii::$app->redis->executeCommand('SADD', [$key, $newToken]);  //设置redis缓存
        } else {
            \Yii::$app->redis->executeCommand('SADD', [$key, $newToken]);  //设置redis缓存
        }

    }

    public function destoryToken($token, $userId)
    {
        \Yii::$app->redis->del('token_' . $token);  //设置redis缓存
        $key = $this->keyLiveList . $userId;
        \Yii::$app->redis->executeCommand('SREM', [$key, $token]);  //设置redis缓存

    }

    public function getUserIdByToken($token)
    {
        $userId = \Yii::$app->redis->get('token_' . $token);  //设置redis缓存
        if($userId && ($userId > 0)){
            return 0;
        }else{
            return false;
        }

    }

    /**
     * @param array
     * */
    public function updateUserInfo($where,$userInfo)
    {
        if(empty($where) || empty($userInfo)){
            return false;
        }
        $connection = \Yii::$app->db;
        $ret = $connection->createCommand()->update('user_account', $userInfo,$where)->execute();
        return $ret;

    }


}