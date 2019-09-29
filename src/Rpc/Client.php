<?php
/**
 * Created by PhpStorm.
 * User: Xingshun <250915790@qq.com>
 * Date: 2019/9/27
 * Time: 20:05
 */

namespace Xskit\Swoft\Rpc;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

/**
 * RPC 服务客户端
 * Class Client
 * @package App\Services\Rpc
 * @see swoft rpc
 * @since 2.0
 */
class Client
{
    private $config;

    private $connection = 'default';

    private static $instance = null;

    private $name;

    private $method;

    private $param;

    private $id = '';

    private $ext = [];

    const RPC_EOL = "\r\n\r\n";

    private $poolConnection = [];

    private $errorCode = 0;
    private $errorMessage;

    private $error = false;

    private $result;

    private $resultRaw;

    public function __construct()
    {
        $this->config = Config::get('swoft_rpc_client');
    }

    /**
     * 使用 RPC 服务
     * @param $name
     * @param $method
     * @param array $param
     * @param array $ext
     * @return $this
     * @throws \Exception
     */
    public static function usage($name, $method, $param = [], $ext = [])
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance->setName($name)->setMethod($method)->setParam($param)->setExt($ext)->setId('');
    }

    /**
     * 执行调用
     * @return $this
     */
    public function call()
    {
        try {
            $this->resultRaw = $this->request();
            $this->error = Arr::has($this->resultRaw, 'error');
            $this->errorMessage = Arr::get($this->resultRaw, 'error.message');
            $this->errorCode = Arr::get($this->resultRaw, 'error.code');
            $this->result = Arr::get($this->resultRaw, 'result');
        } catch (\Exception $e) {
            $this->result = null;
            $this->errorCode = $e->getCode();
            $this->errorMessage = $e->getMessage();
            $this->error = true;
        } finally {
            return $this;
        }

    }

    /**
     * 获取调用成果
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * 获取调用成果，如果有异常或失败则抛出
     */
    public function getResultOrFail()
    {
        if ($this->isSuccess()) {
            return $this->getResult();
        }

        throw new \RuntimeException($this->getErrorMessage(), $this->getErrorCode());
    }

    public function getRaw()
    {
        return $this->resultRaw;
    }

    /**
     * 是否调用成功
     * @return bool
     */
    public function isSuccess()
    {
        return !$this->error;
    }

    /**
     * 返回 错误信息
     * @return mixed
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setMethod($name)
    {
        $this->method = $name;
        return $this;
    }

    public function setParam($param)
    {
        $this->param = $param;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setExt($value)
    {
        $this->ext = $value;
        return $this;
    }

    /**
     * 设置连接
     * @param $name
     * @return $this
     */
    public function setConnection($name = 'default')
    {
        $this->connection = $name;
        return $this;
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getVersion(): string
    {
        $version = Arr::get($this->getFunctions(), $this->name . '.version');
        if (empty($version)) {
            throw new \Exception('rpc service [' . $this->name . '] version not found');
        }
        return $version;
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getHost(): string
    {
        $conn = $this->getConnectionInfo();
        return Arr::get($conn, 'host') . ':' . Arr::get($conn, 'port');
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getClass(): string
    {
        $class = Arr::get($this->getFunctions(), $this->name . '.class');
        if (empty($class)) {
            throw new \Exception('rpc service [' . $this->name . '] class not found');
        }
        return $class;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    private function getConnectionInfo()
    {
        $conn = Arr::get($this->config, 'connection.' . $this->connection);
        if (empty($conn)) {
            throw new \Exception('connection information not found');
        }
        return $conn;
    }

    private function getFunctions(): array
    {
        return Arr::get($this->config, 'functions', []);
    }

    /**
     * 返回 连接超时时间
     * @return int
     * @throws \Exception
     */
    private function getTimeout(): int
    {
        return Arr::get($this->getConnectionInfo(), 'setting.timeout', 3);
    }

    private function getWriteTimeout(): int
    {
        return Arr::get($this->getConnectionInfo(), 'setting.write_timeout', 10);
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function request(): array
    {
        $host = trim($this->getHost());
        if (isset($this->poolConnection[$host])) {
            $fp = $this->poolConnection[$host];
        } else {
            $fp = $this->poolConnection[$host] = stream_socket_client($this->getHost(), $errNo, $errStr, $this->getTimeout());
        }

        if (!$fp) {
            throw new \Exception(sprintf("stream_socket_client fail errno=%s errstr=%s", $errNo, $errStr));
        }

        $req = [
            "jsonrpc" => '2.0',
            "method" => sprintf("%s::%s::%s", $this->getVersion(), $this->getClass(), $this->method),
            'params' => $this->param,
            'id' => $this->id,
            'ext' => $this->ext,
        ];

        $data = json_encode($req) . self::RPC_EOL;

        fwrite($fp, $data);

        //设置写超时
        stream_set_timeout($fp, $this->getWriteTimeout());

        $info = stream_get_meta_data($fp);
        if ($info['timed_out']) {
            throw new \Exception('write timeout');
        }

        $result = '';
        while (!feof($fp)) {
            $tmp = stream_socket_recvfrom($fp, 1024);

            if ($pos = strpos($tmp, self::RPC_EOL)) {
                $result .= substr($tmp, 0, $pos);
                break;
            } else {
                $result .= $tmp;
            }
        }

        return json_decode($result, true);
    }

    public function __destruct()
    {
        foreach ($this->poolConnection as $fp) {
            fclose($fp);
        }
    }
}