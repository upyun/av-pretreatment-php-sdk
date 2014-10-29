<?php
/**
 * 又拍云视频预处理
 */
namespace Sugar;


class AvPretreatment {
    /**
     * @var string: 请求接口地址
     */
    protected $apiUrl;
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


    public function __construct($operatorName, $operatorPassword, $url = 'http://p0.api.upyun.com/pretreatment/')
    {
        $this->operatorName = $operatorName;
        $this->operatorPassword = $operatorPassword;
        $this->apiUrl = $url;
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
     * 请求接口
     * @param $data
     * @return array: 接口返回的每个任务对应的ID
     */
    public function request($data, $retryTimes = 3)
    {
        $data['tasks'] = $this->processTasksData($data['tasks']);
        $sign = $this->createSign($data);

        $ch = curl_init($this->apiUrl);
        $headers = array(
            "Authorization:UPYUN {$this->operatorName}:$sign"
        );
        $options = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
        );
        curl_setopt_array($ch, $options);

        $times = 0;
        do {
            $result = curl_exec($ch);
            $times++;
        } while($result === false && $times < $retryTimes);

        $this->parseResult($result, $ch);
        curl_close($ch);
        return $this->getTaskIds();
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