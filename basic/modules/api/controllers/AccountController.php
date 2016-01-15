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

    /**
     * 用户登陆
     * author:zhaobo1023@gmail.com
     * return:json
     * */
    public function actionLogin()
    {
        $parameters = $this->getPostParameters();
        $userName = $parameters['username'];
        $passwd = $parameters['passwd'];
        $deviceToken = $parameters['deviceToken'];

        if(empty($userName) || empty($passwd)){
            $this->ApiReturnJson(300,'参数错误',array());
        }

        $return = $this->checkPasswd($userName,$passwd);
        if($return === false){
            $this->ApiReturnJson(400,'登陆失败',array());
        }else if($return > 0){
            $userId = $return;
        }

        //生成token,写入redis
        $loginToken = $this->create_uuid();

        $ret = $this->addTokenToCache($loginToken,$userId);


        $this->ApiReturnJson(200,'登陆成功',array('loginToken'=>$loginToken));


    }


    /**
     * 自动登录，更新token
     * */
    public function actionAutologin()
    {
        $parameters = $this->getPostParameters();
        $userName = $parameters['username'];
        $loginToken = $parameters['loginToken'];

        if(empty($userName) || empty($loginToken)){
            $this->ApiReturnJson(400,'参数错误',array());
        }
        $AccountModel = new AccountModel();
        $userInfo = $AccountModel->getUserInfo($userName);
        if(empty($userInfo)){
            $this->ApiReturnJson(300,'用户不存在',array());
        }else{
            $newToken = $this->create_uuid();
            $AccountModel->renewToken($loginToken,$userInfo['id'],$newToken);
            $this->ApiReturnJson(200,'登陆成功',array('loginToken'=>$newToken));
        }

    }

   /**
    * 登出系统
    * */
    public function actionLogout()
    {
        $parameters = $this->getPostParameters();
        $loginToken = $parameters['loginToken'];
        if(empty($loginToken)){
            $this->ApiReturnJson(400,'参数错误',array());
        }

        $AccountModel = new AccountModel();
        $userId = $AccountModel->getUserIdByToken($loginToken);
        if($userId === false){
            $this->ApiReturnJson(450,'token已失效',array());
        }else{
            $ret = $AccountModel->destoryToken($loginToken,$userId);
            $this->ApiReturnJson(200,'登出成功',array());
        }

    }



    /**
     * checkpasswd and return userId
     * @return false or userId if success
     * */
    private function checkPasswd($userName,$passwd)
    {
        if(empty($userName) || empty($passwd)){
            return false;
        }
        $AccountModel = new AccountModel();
        $userInfo = $AccountModel->getUserInfo($userName);
        $userPasswd = $userInfo['passwd'];
        $salt = \Yii::$app->params['passwdSalt'];
        $passwd = md5($passwd.$salt);

        if(empty($passwd)){
            return false;
        }else if($passwd != $userPasswd){
            return false;
        }else{
            return $userInfo['id'];
        }

    }

    private function addTokenToCache($token,$userId)
    {
        $AccountModel = new AccountModel();
        $AccountModel->setAccessTokenAndUserId($token,$userId);
        $AccountModel->addToLiveTokenList($token,$userId);
    }



}
