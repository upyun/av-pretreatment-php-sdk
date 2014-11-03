<?php
/**
 * 又拍云视频预处理
 */
namespace Sugar;


class AvPretreatment {
    /**
     * @var string: 请求接口地址
     */
    protected $apiUrls;
    /**
     * @var array: 接口返回的任务ID数组
     */
    protected $taskIds;

    /**
     * @var string:操作员用户名
     */
    private $operatorName;
    /**
     * @var string:操作员密码
     */
    private $operatorPassword;


    public function __construct($operatorName, $operatorPassword)
    {
        $this->operatorName = $operatorName;
        $this->operatorPassword = $operatorPassword;
        $this->apiUrls = array(
            //视频预处理接口
            'pretreatment' => 'http://p0.api.upyun.com/pretreatment/',
            //查询视频处理状态
            'status' => 'http://p0.api.upyun.com/status/'
        );
    }

    /**
     * 生成请求接口需要的签名
     * @param $data
     * @return bool|string
     */
    public function createSign($data)
    {
        if(is_array($data)) {
            ksort($data);
            $string = '';
            foreach($data as $k => $v) {
                if(is_array($v)) {
                    $v = implode('', $v);
                }
                $string .= "$k$v";
            }
            $sign = $this->operatorName.$string.md5($this->operatorPassword);
            $sign = md5($sign);
            return $sign;
        }
        return false;
    }

    /**
     * 不使用操作员帐号生成的签名
     * @param $data
     * @return bool|string
     */
    public function createSignWithoutOperator($data)
    {
        if(is_array($data)) {
            ksort($data);
            $string = '';
            foreach($data as $k => $v) {
                $string .= "$k$v";
            }
            $sign = md5($string);
            return $sign;
        }
        return false;
    }

    /**
     * 辅助函数， 请求接口的任务数组需要转化为字符串
     * @param $tasks
     * @return bool|string
     */
    protected function processTasksData($tasks)
    {
        if(is_array($tasks)) {
            return base64_encode(json_encode($tasks));
        }
        return false;
    }

    /**
     * 请求 pretreatment 接口
     * @param array $data
     * <code>
     * $data = array(
     *      'bucket_name' => 'stash',  //空间名
     *      'source' => '/path/yourvideo', //视频路径
     *      'notify_url' => 'http://callback/', //回调地址
     *      'tasks' => $tasks //任务
     * )
     * </code>
     * @return array: 接口返回的每个任务对应的ID
     */
    public function request($data)
    {
        $data['tasks'] = $this->processTasksData($data['tasks']);
        $this->curl($data, $this->apiUrls['pretreatment'], 'POST');
        return $this->getTaskIds();
    }


    protected function curl($data, $url, $method = 'GET', $retryTimes = 3)
    {
        $sign = $this->createSign($data);
        $data = http_build_query($data);
        $ch = curl_init();
        $headers = array(
            "Authorization:UPYUN {$this->operatorName}:$sign"
        );

        $options = array();
        switch(strtoupper($method)) {
            case 'GET':
                $url .= '?' . $data;
                $options = array(
                    CURLOPT_URL => $url,
                    CURLOPT_HTTPHEADER => $headers,
                    CURLOPT_RETURNTRANSFER => true
                );
                break;
            case 'POST':
                $options = array(
                    CURLOPT_URL => $url,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $data,
                    CURLOPT_HTTPHEADER => $headers,
                    CURLOPT_RETURNTRANSFER => true
                );
                break;
        }
        curl_setopt_array($ch, $options);

        $times = 0;
        do {
            $result = curl_exec($ch);
            $times++;
        } while($result === false && $times < $retryTimes);

        $this->parseResult($result, $ch);
        curl_close($ch);
    }

    /**
     * 获取任务状态
     *
     * @param $taskIds : 任务id, 一次最多20个,e.g ebc6b85f55b547e18a07cccd867fb961,bdcabe55f75b547e18a07cccd867fb961
     * @param $bucket_name : 空间名
     * @throws \Exception
     * @return array
     * <code>
     * array(
     * 'tasks' => array(
     *     'ebc6b85f55b547e18a07cccd867fb961' => '100'
     *   ),
     *   'count' => 1
     * )
     * </code>
     */
    public function getTasksStatus($taskIds, $bucket_name)
    {
        if(is_string($taskIds)) {
            $taskIds = explode(',', $taskIds);
        }

        if(is_array($taskIds) && count($taskIds) <= 20) {
            $taskIds = implode(',', $taskIds);
        } else {
            throw new \Exception('一次最多查询20个任务');
        }
        $data['task_ids'] = $taskIds;
        $data['bucket_name'] = $bucket_name;
        $this->curl($data, $this->apiUrls['status'], 'GET');
        return $this->taskIds;
    }

    /**
     * 解析接口返回的数据
     * @param $result
     * @param $ch
     * @throws \Exception
     */
    protected function parseResult($result, $ch)
    {
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($httpCode >= 200 && $httpCode <=299) {
            $this->taskIds = json_decode($result, true);
        } else {
            $this->taskIds = null;
            throw new \Exception(sprintf('request failed!HTTP_CODE:%s, %s', $httpCode, $result));
        }
    }

    /**
     * 获取任务IDS
     * @return array
     */
    public function getTaskIds()
    {
        return is_array($this->taskIds) ? $this->taskIds : array();
    }
}