<?php

namespace Sugar;


class TasksTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var \Sugar\Tasks
     */
    public $tasks;
    protected function setUp() {
        $operatorName = getenv('UPYUN_OPERATOR_NAME');
        $operatorPwd  = getenv('UPYUN_OPERATOR_PWD');
        $bucketName   = getenv('UPYUN_FILE_BUCKET');
        $av = new AvPretreatment($operatorName, $operatorPwd);
        $this->tasks = new Tasks($bucketName, 'http://your.notify.url/', $av);
    }

    public function testRun()
    {
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
        $this->tasks->setSource('/video/20130514_190031.mp4');
        $this->tasks->addTasks($data);
        $ids = $this->tasks->run();
        $this->assertEquals(false, empty($ids));
        $this->assertEquals(2, count($ids));
    }

    public function testGetTasksStatus()
    {
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
        $this->tasks->setSource('/video/20130514_190031.mp4');
        $this->tasks->addTasks($data);
        $this->tasks->run();
        $status = $this->tasks->getTasksStatus();
        $this->assertEquals(2, count($status['tasks']));
    }
} 