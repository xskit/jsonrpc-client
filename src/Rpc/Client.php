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

    private $error;
    private $result;

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
            $this->error = null;
            $this->result = Arr::get($this->request(), 'result');
        } catch (\Exception $e) {
            $this->result = null;
            $this->error = $e->getMessage();
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
     * 是否调用成功
     * @return bool
     */
    public function isSuccess()
    {
        return empty($this->error);
    }

    /**
     * 返回 错误信息
     * @return mixed
     */
    public function getErrorMessage()
    {
        return $this->error;
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
            throw new \Exception("stream_socket_client fail errno={$errNo} errstr={$errStr}");
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