### 视频预处理Sugar php-sdk
示例:
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
//返回对应的任务ids
$ids = $sugar->request($data);
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
    $tasks->run();
    $ids[$url] = $tasks->getTaskIds();
}
```
