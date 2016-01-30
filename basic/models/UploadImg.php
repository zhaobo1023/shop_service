<?php
namespace app\models;

require(__DIR__ . '/../vendor/qiniu/php-sdk/autoload.php');

use Yii;
use yii\base\Model;
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

class uploadImg
{
    public static $qiniu_manager;
    private $accessKey;
    private $secretKey;
    private $bucket = 'zhaobo';

    static function getInstance()
    {
        if(!self::$qiniu_manager){
            self::$qiniu_manager = new uploadImg();
        }
        return self::$qiniu_manager;
    }

    public function __construct()
    {
        $this->accessKey = 'Uzo6pE8v4M_CTTh89atXVBarmQWAvaiwYMyeYFn_';
        $this->secretKey = 'ofHzz_QsfgIVvn2uHKOawSARsQ5yQ_dJYx5CkBRI';
    }

    public function uploadImag($fileName,$Path)
    {

        // 构建鉴权对象
        $auth = new Auth($this->accessKey, $this->secretKey);

        // 要上传的空间
        $bucket = $this->bucket;

        // 生成上传 Token
        $token = $auth->uploadToken($bucket);

        // 要上传文件的本地路径
//        $filePath = './php-logo.png';
        $filePath = $Path;

        // 上传到七牛后保存的文件名
//        $key = 'my-php-logo.png';
        $key = $fileName.'.png';

        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();

        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
//        echo "\n====> putFile result: \n";
        if ($err !== null) {
//            var_dump($err);
            $return['code'] = false;
            $return['content'] = $err;
        } else {
            $return['code'] = true;
            $return['content'] = $ret;
        }
        return $return;

    }

}

