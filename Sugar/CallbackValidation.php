<?php
/**
 * UpYun视频预处理 PHP-SDK
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace Sugar;
/**
 * 验证回调参数签名是否合法
 * Class CallbackValidation
 * @package Sugar
 */

class CallbackValidation {

    public $params = array();
    /**
     * @var \Sugar\AvPretreatment
     */
    public $sugar;

    public function __construct($avPretreatment)
    {
         $this->setParamsByPost(array(
            'bucket_name',
            'status_code',
            'path',
            'description',
            'task_id',
            'info',
            'signature',
        ));

        if($avPretreatment instanceof AvPretreatment) {
            $this->sugar = $avPretreatment;
        } else {
            throw new \Exception('需要一个 AvPretreatment 实例');
        }
    }

    protected function getParamFromPost($key)
    {
        return isset($_POST[$key]) ? $this->trim($_POST[$key]) : null;
    }

    protected function setParamsByPost($keys)
    {
        $this->params = array();
        foreach($keys as $key) {
            $value = $this->getParamFromPost($key);
            if($value !== null) {
                $this->params[$key] = $value;
            }
        }
    }

    /**
     * 验证回调参数的签名是否合法
     * @return bool
     */
    public function verifySign()
    {
        $data = $this->params;
        if(isset($data['signature'])) {
            unset($data['signature']);
            return $this->params['signature'] === $this->sugar->createSign($data);
        }

        if(isset($data['non_signature'])) {
            unset($data['non_signature']);
            return $this->params['non_signature'] === $this->sugar->createSignWithoutOperator($data);
        }

        return false;
    }

    protected function trim($data) {
       if(is_array($data)) {
           return array_map(array($this, 'trim'), $data);
       } else {
           return trim($data);
       }
    }
}