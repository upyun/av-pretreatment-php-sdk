### 视频预处理Sugar php-sdk
#### 请求预处理示例:
1. `AvPretreatment`类使用示例
```php
    $sugar = new \Sugar\AvPretreatment('stash', '123456789');//操作员的帐号密码
    $data = array(
        'bucket_name' => 'stash',                   //空间名
        'source' => '/video/20130514_190031.mp4',   //视频地址
        'notify_url' => 'http://callback/',         //回调通知地址
        'tasks' => array(                           //任务
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

2. 利用`Tasks`类，对同一空间的多个视频作处理
```php
    $tasks = new \Sugar\Tasks('stash', 'http://callback/', new AvPretreatment('stash', '123456789'));
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
    foreach($videoFiles as $url) {
        $tasks->setSource('/video/20130514_190031.mp4');
        $ids[$url] = $tasks->run();
    }
```

#### 回调地址验证示例:
在回调代码中，添加如下验证
```
$validation = new \Sugar\CallbackValidation(new \Sugar\AvPretreatment('stash', '123456789'));
if($validation->verifySign()) {
    echo '验证成功';
} else {
    echo '验证失败';
}
```
