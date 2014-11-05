# 又拍云视频预处理 PHP-SDK
![build](https://api.travis-ci.org/upyun/av-pretreatment-php-sdk.svg)
## 目录
- [安装说明](#install)
  - [要求](#require)
  - [通过composer安装](#composer install)
  - [github下载压缩包安装](#download zip and install)
- [示例](#usage)
  - [`AvPretreatment` 类提交视频预处理请求](#avpretreatment)
  - [`Tasks` 类 批量处理同一个空间的多个视频](#tasks)
  - [回调地址验证示例](#validate)

<a name="install"></a>
## 安装说明

<a name="require"></a>
### 要求
  php 5.3+

<a name="composer install"></a>
### 通过[composer](https://getcomposer.org/)安装
1.安装composer
```
curl -s https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

2.在你的项目根目录下创建`composer.json`，并添加如下内容
```
{
    "repositories": [
        {   
            "type": "vcs",
            "url": "https://github.com/upyun/av-pretreatment-php-sdk.git"
        }   
    ],  
    "require":{
        "upyun/sugar-php-sdk":"dev-master"
    }   
}
```

3.运行 `composer install`

4.在项目中添加如下代码
```php
//注意修改项目根目录
include '/your/project/root/path/vendor/autoload.php'
```

<a name="download zip and install"></a>
### github下载压缩包安装
通过github直接下载最新稳定版，在项目中添加以下代码
```
include "Sugar/AvPretreatment.php";
include "Sugar/CallbackValidation.php";
include "Sugar/Tasks.php";
```
<a name="usage"></a>
## 示例

<a name="avpretreatment"></a>
### `AvPretreatment` 类提交视频预处理请求
```php
use Sugar\AvPretreatment;
use Sugar\CallbackValidation;

$sugar = new AvPretreatment('operator_name', 'operator_password');//操作员的帐号密码
$data = array(
    'bucket_name' => 'your_bucket_name',        //空间名
    'source' => '/video/20130514_190031.mp4',   //空间视频地址
    'notify_url' => 'http://callback/',         //回调通知地址
    'tasks' => array(                           //针对一个视频，可以有多种处理任务
        array(
            'type' => 'hls',
            'hls_time' => 6,
            'bitrate' => '500',
            'rotate' =>  'auto',
            'format' => 'mp4',
        ),
        array(
            'type' => 'thumbnail',
            'thumb_single' => false,
            'thumb_amount' => 100,
            'format' => 'png'
        ),
    )
);
try {
    //返回对应的任务ids
    $ids = $sugar->request($data);
} catch(\Exception $e) {
    echo "request failed:", $e->getMessage();
}
```

<a name="tasks"></a>
### `Tasks` 类 批量处理同一个空间的多个视频
当需要对同一个空间多个视频做相同的任务处理时，可以使用`Tasks`类
```php
use Sugar\AvPretreatment;
use Sugar\Tasks;
use Sugar\CallbackValidation;

//需要将 operator_name opeartor_pwd your_bucket_name替换成自己的操作员帐号密码和空间名
$avPretreatment = new AvPretreatment('operator_name', 'operator_pwd');
$tasks = new Tasks('your_bucket_name', 'http://callback/', $avPretreatment);
$data =array(
    array(
        'type' => 'hls',
        'hls_time' => 6,
        'bitrate' => '500',
        'rotate' =>  'auto',
        'format' => 'mp4',
    ),
    array(
        'type' => 'thumbnail',
        'thumb_single' => false,
        'thumb_amount' => 100,
        'format' => 'png'
    ),
);
$tasks->addTasks($data);
//待处理的多个视频
$videoFiles = array('/video/path1', '/video/path2', '/video/path3')
foreach($videoFiles as $url) {
    $tasks->setSource($url);
    $ids[$url] = $tasks->run();
}
```

<a name="validate"></a>
### 回调地址验证示例:
在回调代码中，添加如下验证
```php
use Sugar\AvPretreatment;
use Sugar\CallbackValidation;
//需要将 operator_name opeartor_pwd 替换成自己的操作员帐号密码
$av = new AvPretreatment('operator_name', 'operator_pwd');
$validation = new CallbackValidation($av);
if($validation->verifySign()) {
    echo '验证成功';
} else {
    echo '验证失败';
}
```

