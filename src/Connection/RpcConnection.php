<?php
/**
 * Created by PhpStorm.
 * User: Xingshun <250915790@qq.com>
 * Date: 2020/3/10
 * Time: 22:29
 */

namespace XsKit\RpcClient\Connection;


use XsKit\RpcClient\Node;
use XsKit\RpcClient\Contract\ConnectionInterface;

class RpcConnection implements ConnectionInterface
{

    private $fp;

    /**
     * @var Node
     */
    private $node;

    /**
     * @var float
     */
    private $connectTimeout = 5;

    public $errCode = 0;

    public $errMessage = '';

    private $config = [
        'connect_timeout' => 5.0,
        'eof' => "\r\n"
    ];

    /**
     * StreamConnection constructor.
     * @param Node $node
     * @param array $config
     */
    public function __construct(Node $node, $config = [])
    {
        $this->node = $node;
        $this->config = array_replace($this->config, $config);
        $this->connectTimeout = $this->config['connect_timeout'] ?? 5.0;
    }

    /**
     * 发布连接
     */
    public function release()
    {
    }

    /**
     * 获取一个连接
     * @return $this
     * @throws \Exception
     */
    public function getConnection()
    {
        if ($this->check()) {
            return $this;
        }

        if (!$this->reconnect()) {
            throw new \Exception(sprintf("stream_socket_client fail errno=%s errstr=%s", $this->errCode, $this->errMessage));
        }
        return $this;
    }

    /**
     * 检查连接是否可用
     * @return bool
     */
    public function check(): bool
    {
        return boolval($this->fp);
    }

    /**
     * 关闭连接
     */
    public function close()
    {
        fclose($this->fp);
    }

    /**
     * 重新连接
     */
    public function reconnect(): bool
    {
        $this->fp = stream_socket_client($this->node->host . ':' . $this->node->port, $errNo, $errStr, $this->connectTimeout);
        if ($this->fp === false) {
            $this->errCode = $errNo;
            $this->errMessage = $errStr;
            return false;
        }
        return true;
    }

    /**
     * @param $time
     * @param $timeout
     * @return bool
     */
    private function checkTimeout($time, $timeout)
    {
        $res = (microtime(true) - $time) <= $timeout;
        if (!$res) {
            // 超时
            trigger_error('RPC invoke timeout');
        }
        return $res;
    }

    /**
     * @param string $data
     * @return bool|int
     * @throws \Exception
     */
    public function send(string $data)
    {
        $this->getConnection();
        return fwrite($this->fp, $data . $this->getEof());
    }

    public function recv($timeout)
    {
        $result = '';
        // 记录开始时间,判断是否超时，避免 feof() 陷入无限循环
        $start = microtime(true);
        while (!feof($this->fp) && $this->checkTimeout($start, $timeout)) {
            $tmp = stream_socket_recvfrom($this->fp, 1024);
            $start = microtime(true);

            if ($pos = strpos($tmp, $this->getEof())) {
                $result .= substr($tmp, 0, $pos);
                break;
            } else {
                $result .= $tmp;
            }
        }
        if (empty($result)) {
            return $result;
        }

        return json_decode($result, true);
    }

    private function getEof()
    {
        return $this->config['eof'] ?? "\r\n";
    }


}