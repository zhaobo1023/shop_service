<?php

namespace app\modules\api\controllers;
use app\models\AccountModel;
use app\models\UploadImg;
//use app\modules\api\controllers\BaseController;
//use yii\base\Controller;
//use yii\base\Application;


class AccountController extends BaseController
{
    // ...
    public function actionIndex()
    {
        echo 1234;
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
        $passwd = $this->getSavePasswd($passwd);
        $ret = $AccountModel->register($phone,$passwd);

        if($ret === true){
            $this->ApiReturnJson(200,'返回成功',array());
        }else if($ret === -1){
            $this->ApiReturnJson(460,'注册失败',array());
        }else if($ret === -2){
            $this->ApiReturnJson(450,'用户已注册',array());
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
//        $deviceToken = $parameters['deviceToken'];

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

        $this->addTokenToCache($loginToken,$userId);

        $AccountModel = new AccountModel();
        $where['phone_number'] = $userName;
        $userInfo = $AccountModel->getUserInfoByWhere($where);
        if(!empty($userInfo)){
            $data = array(
                'username'                  => $userInfo[0]['phone_number'],
                'nickname'                  => $userInfo[0]['nick_name'],
                'gender'                    =>$userInfo[0]['gender'],
                'avatarImageThumbnailsUrl'  =>  '',
                'avatarImageOriginalUrl'    =>  '',
                'company'                   =>$userInfo[0]['company'],
                'companyVerification'       => 0,
            );
        }else{
            $data = array();
        }

        $this->ApiReturnJson(200,'登陆成功',array('loginToken'=>$loginToken,'userInfo' => $data));

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

            $where['phone_number'] = $userName;
            $userInfo = $AccountModel->getUserInfoByWhere($where);
            if(!empty($userInfo)){
                $data = array(
                        'username'                  => $userInfo[0]['phone_number'],
                        'nickname'                  => $userInfo[0]['nick_name'],
                        'gender'                    =>$userInfo[0]['gender'],
                        'avatarImageThumbnailsUrl'  =>  '',
                        'avatarImageOriginalUrl'    =>  '',
                        'company'                   =>$userInfo[0]['company'],
                        'companyVerification'       => 0,
                );
            }else{
                $data = array();
            }

            $this->ApiReturnJson(200,'登陆成功',array('loginToken'=>$newToken,'userInfo' => $data));

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
     * 修改密码
     * */
    public function actionChangepasswd()
    {
        $parameters = $this->getPostParameters();
        $userName = $parameters['username'];
        $oldPasswd = $parameters['oldPasswd'];
        $newPasswd = $parameters['newPasswd'];

        $return = $this->changePasswd($userName,$oldPasswd,$newPasswd);
        if($return === true){
            $this->ApiReturnJson(200,'修改密码成功',array());
        }else{
            $this->ApiReturnJson(300,'修改密码失败',array());
        }
    }


    /**
     * 修改个人信息
     * */
    public function actionChangeuserinfo()
    {
        $parameters = $this->getPostParameters();
        $loginToken = $parameters['loginToken'];

        if(isset($parameters['nickname'])){
            $userInfo['nick_name'] = $parameters['nickname'];
        }
        if(isset($parameters['gender'])){
            $userInfo['gender'] = $parameters['gender'];
        }
        if(isset($parameters['company'])){
            $userInfo['company'] = $parameters['company'];
        }

        if(empty($userInfo)){
            $this->ApiReturnJson(560,'更新失败',array());
        }

        if(empty($loginToken)){
            $this->ApiReturnJson(550,'token无效',array());
        }

        $AccountModel = new AccountModel();
        $userId = $AccountModel->getUserIdByToken($loginToken);
        if(!isset($userId) && ($userId <= 0)){
            $this->ApiReturnJson(550,'token无效',array());
        }

        $where['id'] = $userId;
        $ret = $AccountModel->updateUserInfo($where,$userInfo);
        if($ret == true){
            $this->ApiReturnJson(200,'更新成功',array());
        }else{
            $this->ApiReturnJson(560,'更新a失败',array());
            //$this->ApiReturnJson(560,'更新a失败',array('where' => var_export($where,true),'data'=>var_export($userInfo,true)));
        }

    }

    /**
     * 上传头像
     * */
    public function actionUploadheadimg()
    {
        $parameters = $this->getFormParameters();
        $loginToken = $parameters['loginToken'];
        $imgData = $parameters['avatarImageFile'];
        var_dump($loginToken);
        var_dump($imgData);

        if(empty($loginToken)){
            $this->ApiReturnJson(550,'token无效',array());
        }

        $AccountModel = new AccountModel();
        $userId = $AccountModel->getUserIdByToken($loginToken);

        if(empty($userId) || intval($userId) <= 0){
            $this->ApiReturnJson(550,'token无效',array());
        }

        $dir = 'upload/'.date('Y').'/'.date('m').'/';
        $path = $dir.md5(time().$userId);

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                $this->ApiReturnJson(560,'创建目录失败',array());
            }
        }
        if (!is_writable($dir)) {
            $this->ApiReturnJson(561,'系统不可写',array());
        }
        if (!file_put_contents($path, $imgData)) {
            $this->ApiReturnJson(562,'文件写入失败',array());
        }

        /**
         * 上传到七牛
         * */
        $uploadRet = uploadImg::getInstance()->uploadImag($path,$dir);
        if($uploadRet['code'] == true){
            $this->ApiReturnJson(200,'图片上传成功',array('ret' => $uploadRet['content']['key']));
        }else{
            $this->ApiReturnJson(570,'图片上传七牛失败',array());
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
        $passwd = $this->getSavePasswd($passwd);

        if(empty($passwd)){
            return false;
        }else if($passwd != $userPasswd){
            return false;
        }else{
            return $userInfo['id'];
        }

    }

    private function changePasswd($userName,$oldPasswd,$newPasswd)
    {

        $AccountModel = new AccountModel();
        $where['username'] = $userName;
        $where['passwd'] = $this->getSavePasswd($oldPasswd);
        $userInfo = $AccountModel->getUserInfoByWhere($userName);
        if(!empty($userInfo)){
            $updateCondition['id'] = $userInfo[0]['id'];
            $data['passwd'] = $this->getSavePasswd($newPasswd);
            $ret = $AccountModel->updateUserInfoByWhere($updateCondition,$data);
            return $ret;
        }else{
            return false;
        }

    }

    private function addTokenToCache($token,$userId)
    {
        $AccountModel = new AccountModel();
        $AccountModel->setAccessTokenAndUserId($token,$userId);
        $AccountModel->addToLiveTokenList($token,$userId);
    }

    private function getSavePasswd($rawPasswd)
    {
        $salt = \Yii::$app->params['passwdSalt'];

        return md5($rawPasswd.$salt);
    }


}
