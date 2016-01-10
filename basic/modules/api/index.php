<?php

// comment out the following two lines when deployed to production
defined('yii_debug') or define('yii_debug', true);
defined('yii_env') or define('yii_env', 'dev');
//
require(__dir__ . '/../vendor/autoload.php');
require(__dir__ . '/../vendor/yiisoft/yii2/yii.php');
//
$config = require(__dir__ . '/../config/web.php');
//
(new yii\web\application($config))->run();


// echo 'hello';
