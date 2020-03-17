<?php
/**
 * Created by PhpStorm.
 * User: Xingshun <250915790@qq.com>
 * Date: 2020/3/10
 * Time: 21:08
 */

namespace XsKit\RpcClient\Transporter;


use XsKit\RpcClient\Contract\LoadBalancerInterface;
use XsKit\RpcClient\Contract\TransporterInterface;
use XsKit\RpcClient\Exception\ReceiveException;
use XsKit\RpcClient\Node;
use XsKit\RpcClient\Connection\RpcConnection;

class JsonRpcTransporter implements TransporterInterface
{
    /**
     * 如果没有设置均衡器，随机获取一个节点
     * @var array Node
     */
    private $nodes = [];

    /**
     * @var null|LoadBalancerInterface
     */
    private $loadBalancer;

    /**
     * @var float
     */
    private $connectTimeout = 5;

    /**
     * @var float
     */
    private $receiveTimeout = 5;

    private $config = [
        'connect_timeout' => 5.0,
        'receive_timeout' => 5.0,
    ];

    private $connection;

    public function __construct(array $config = [])
    {
        $this->config = array_replace_recursive($this->config, $config);

        $this->connectTimeout = $this->config['connect_timeout'] ?? 5.0;
        $this->receiveTimeout = $this->config['receive_timeout'] ?? 5.0;
    }


    /**
     * @param string $data
     * @return mixed
     * @throws \Exception
     */
    public function send(string $data)
    {
        $conn = $this->getConnect();

        if ($conn->send($data) === false) {
            throw new \RuntimeException('Connect to server failed.');
        }

        return $this->receiveAndCheck($conn, $this->receiveTimeout);
    }

    public function receive()
    {
        $conn = $this->getConnect();

        return $this->receiveAndCheck($conn, $this->receiveTimeout);
    }

    public function getConnect()
    {
        if (is_null($this->connection)) {
            $this->connection = new RpcConnection($this->getNode(), $this->config);
        }
        return $this->connection;
    }


    public function getLoadBalancer(): ?LoadBalancerInterface
    {
        return $this->loadBalancer;
    }

    public function setLoadBalancer(LoadBalancerInterface $loadBalancer): TransporterInterface
    {
        $this->loadBalancer = $loadBalancer;
        return $this;
    }

    private function getNode(): Node
    {
        if ($this->loadBalancer instanceof LoadBalancerInterface) {
            return $this->loadBalancer->select();
        }
        return $this->nodes[array_rand($this->nodes)];
    }

    /**
     * @param RpcConnection $conn
     * @param float $timeout
     * @return mixed
     */
    public function receiveAndCheck($conn, $timeout)
    {
        $data = $conn->recv((float)$timeout);
        if ($data === '') {
            $conn->close();
            throw new ReceiveException('Connection is closed.');
        }
        if ($data === false) {
            throw new ReceiveException('Error receiving data, errno=' . $conn->errCode);
        }

        return $data;
    }
}