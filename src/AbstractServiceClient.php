<?php
/**
 * Created by PhpStorm.
 * User: Xingshun <250915790@qq.com>
 * Date: 2020/3/10
 * Time: 20:11
 */

namespace XsKit\RpcClient;

use XsKit\RpcClient\Contract\LoadBalancerInterface;
use InvalidArgumentException;
use XsKit\RpcClient\LoadBalancer\RandomBalancer;

/**
 * 服务类的抽象
 * Class AbstractServiceClient
 * @package XsKit\RpcClient
 */
abstract class AbstractServiceClient
{
    /**
     * @var string RPC 同配置文件中配置的服务接口名相同
     */
    protected $serviceInterface = '';

    /**
     * @var LoadBalancerInterface 均衡器
     */
    protected $loadBalancer = RandomBalancer::class;

    /**
     * @var Client
     */
    protected $client;

    public function __construct()
    {
        $this->client = (new Client())->setPacker()->setTransporter();
    }

    protected function __request(string $method, array $params, array $ext = [], ?string $id = null)
    {

    }

    protected function getPackage()
    {


    }

    protected function getDataFormat()
    {

    }

    /**
     * @return array
     */
    protected function getConsumerConfig(): array
    {
        if (!function_exists('config')) {
            throw new \RuntimeException('method config() missing.');
        }
        $consumers = config('rpc_services.consumers', []);
        $config = [];
        foreach ($consumers as $consumer) {
            if (isset($consumer['name']) && $consumer['name'] === $this->serviceInterface) {
                $config = $consumer;
                break;
            }
        }

        return $config;
    }

    protected function getNodesConfig($name): array
    {
        if (!function_exists('config')) {
            throw new \RuntimeException('method config() missing.');
        }
        return config('rpc_services.nodes.' . $name, []);
    }

    /**
     * @return array XsKit\RpcClient\XsKit\RpcClient
     */
    protected function createNodes(): array
    {
        $consumer = $this->getConsumerConfig();
        if (isset($consumer['node'])) {
            $nodes = [];
            foreach ($this->getNodesConfig($consumer['node']) ?? [] as $item) {
                if (isset($item['host'], $item['port'])) {
                    if (!is_int($item['port'])) {
                        throw new InvalidArgumentException(sprintf('Invalid node config [%s], the port option has to a integer.', implode(':', $item)));
                    }
                    $nodes[] = new Node($item['host'], $item['port']);
                }
            }
            return $nodes;
        }

        throw new InvalidArgumentException('Config of registry or nodes missing.');

    }

}