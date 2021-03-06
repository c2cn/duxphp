<?php

/**
 * 公共API
 */

namespace dux\kernel;

class Api {

    protected $data = [];

    /**
     * Api constructor.
     */
    public function __construct() {
        $request = request();
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);
        $data = $data ? $data : [];
        $this->data = array_merge($request, $data);
        $_SERVER['HTTP_X_AJAX'] = true;
    }

    /**
     * 返回成功数据
     * @param $msg
     * @param array $data
     */
    public function success($msg = '', array $data = []) {
        $header = [
            'Content-Type' => 'application/json; charset=utf-8;'
        ];
        if (empty($msg)) {
            $msg = \dux\Dux::$codes[200];
        }
        $data = [
            'code' => 200,
            'message' => $msg,
            'result' => $data,
        ];
        \dux\Dux::header(200, function () use ($data) {
            $this->returnData($data);
        }, $header);
        exit;
    }

    /**
     * 返回错误数据
     * @param string $msg
     * @param int $code
     * @param string $url
     */
    public function error($msg = '', int $code = 500, string $url = '') {
        $header = [
            'Content-Type' => 'text/html; charset=UTF-8;'
        ];
        if ($url) {
            $header['Location'] = $url;
        }
        \dux\Dux::header($code, function () use ($msg) {
            return $msg;
        }, $header);
        exit;
    }

    /**
     * 数据不存在
     * @param string $msg
     */
    public function error404(string $msg = 'There is no data') {
        $this->error($msg, 404);
    }

    /**
     * 返回数据
     * @param $data
     * @param string $type
     */
    public function returnData($data, string $type = 'json') {
        $format = request('', 'format');
        if (empty($format)) {
            $format = $type;
        }
        $callback = request('', 'callback');
        $format = strtolower($format);
        $charset = $this->data['charset'] ? $this->data['charset'] : 'utf-8';

        switch ($format) {
            case 'jsonp' :
                call_user_func_array([$this, 'return' . ucfirst($format)], [$data, $callback, $charset]);
                break;
            case 'json':
            default:
                call_user_func_array([$this, 'return' . ucfirst($format)], [$data, $charset]);
        }
    }

    /**
     * 返回JSON数据
     * @param array $data
     * @param string $charset
     */
    public function returnJson(array $data = [], string $charset = "utf-8") {
        header("Content-Type: application/json; charset={$charset};");
        echo json_encode($data);
    }

    /**
     * 返回JSONP数据
     * @param array $data
     * @param string $callback
     */
    public function returnJsonp(array $data = [], string $callback = 'q', string $charset = "utf-8") {
        header("Content-Type: application/javascript; charset={$charset};");
        echo $callback . '(' . json_encode($data) . ');';
    }

}