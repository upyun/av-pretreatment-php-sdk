<?php
class AvPretreatmentTest extends PHPUnit_Framework_TestCase {

    public $taskids = array(
        "acc510e6885e42d366125ab439d3da49","ebc6b85f55b547e18a07cccd867fb961"
    );

    public function testRequest()
    {
        $operatorName = getenv('UPYUN_OPERATOR_NAME');
        $operatorPwd  = getenv('UPYUN_OPERATOR_PWD');
        $bucketName   = getenv('UPYUN_FILE_BUCKET');

        $sugar = new \Sugar\AvPretreatment($operatorName, $operatorPwd);
        $data = array(
            'bucket_name' => $bucketName,
            'source' => '/video/20130514_190031.mp4',
            'notify_url' => 'http://your.notifyurl.com/',
            'tasks' => array(
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
        $sugar->request($data);
        $ids = $sugar->getTaskIds();
        $this->assertEquals(2, count($ids));
    }

    public function testGetTasksStatus()
    {
        $operatorName = getenv('UPYUN_OPERATOR_NAME');
        $operatorPwd  = getenv('UPYUN_OPERATOR_PWD');
        $bucketName   = getenv('UPYUN_FILE_BUCKET');

        $sugar = new \Sugar\AvPretreatment($operatorName, $operatorPwd);
        $status = $sugar->getTasksStatus($this->taskids, $bucketName);

        $this->assertEquals(2, count($status['tasks']));
        $this->assertEquals(true, isset($status['tasks']['acc510e6885e42d366125ab439d3da49'])
            && $status['tasks']['acc510e6885e42d366125ab439d3da49'] == 100);
    }
}
 