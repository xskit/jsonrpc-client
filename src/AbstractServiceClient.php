<?php
/**
 * Created by PhpStorm.
 * User: Xingshun <250915790@qq.com>
 * Date: 2020/3/10
 * Time: 20:11
 */

namespace XsKit\RpcClient;

use XsKit\RpcClient\Contract\DataFormatterInterface;
use XsKit\RpcClient\Contract\LoadBalancerInterface;
use InvalidArgumentException;
use XsKit\RpcClient\Exception\RequestException;
use XsKit\RpcClient\LoadBalancer\RandomBalancer;

/**
 * 服务类的抽象
 * Class AbstractServiceClient
 * @package XsKit\RpcClient
 */
abstract class AbstractServiceClient
{
    /**
     * The service name of the target service.
     *
     * @var string
     */
    protected $serviceName = '';

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

    protected $id;

    /**
     * @var DataFormatterInterface
     */
    protected $dataFormatter;

    public function __construct()
    {
        // 设置包装器、运输机+负载均衡器、数据格式化
        $packer = null;
        $transporter = null;
        $this->dataFormatter = null;
        $this->client = (new Client())->setPacker($packer)->setTransporter($transporter);
    }

    /**
     * 生成请求ID
     * @return string
     */
    private function generatorRequestId()
    {
        try {
            return bin2hex(random_bytes(8));
        } catch (\Exception $e) {
            return '';
        }
    }


    /**
     * 获取请求ID
     * @return string
     */
    public function getRequestId()
    {
        return $this->id ?: $this->generatorRequestId();
    }

    /**
     * 设置请求ID
     * @param $id
     * @return $this
     */
    public function setRequestId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function __call(string $method, array $params)
    {
        return $this->__request($method, $params);
    }

    protected function __request(string $method, array $params, ?string $id = null)
    {
        if (!$id) {
            $id = $this->getRequestId();
        }

        $response = $this->client->send($this->__generateData($method, $params, $id));
        if (is_array($response)) {
            $response = $this->checkRequestIdAndTryAgain($response, $id);

            if (array_key_exists('result', $response)) {
                return $response['result'];
            }
            if (array_key_exists('error', $response)) {
                return $response['error'];
            }
        }
        throw new RequestException('Invalid response.');
    }

    protected function __generateData(string $methodName, array $params, ?string $id)
    {
        return $this->dataFormatter->formatRequest([$this->serviceName, $methodName, $params, $id]);
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

    protected function checkRequestIdAndTryAgain(array $response, $id, int $again = 1): array
    {
        if (is_null($id)) {
            // If the request id is null then do not check.
            return $response;
        }

        if (isset($response['id']) && $response['id'] === $id) {
            return $response;
        }

        if ($again <= 0) {
            throw new RequestException(sprintf(
                'Invalid response. Request id[%s] is not equal to response id[%s].',
                $id,
                $response['id'] ?? null
            ));
        }

        $response = $this->client->recv();
        --$again;

        return $this->checkRequestIdAndTryAgain($response, $id, $again);
    }

}