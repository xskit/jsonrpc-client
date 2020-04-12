<?php
/**
 * Created by PhpStorm.
 * User: Xingshun <250915790@qq.com>
 * Date: 2020/3/10
 * Time: 19:49
 */

namespace XsKit\RpcClient\Contract;

/**
 * 数据运输接口
 * Interface TransporterInterface
 * @package XsKit\RpcClient\Contract
 */
interface TransporterInterface
{
    public function send(string $data);

    public function receive();

    public function getLoadBalancer(): ?LoadBalancerInterface;

    public function setLoadBalancer(LoadBalancerInterface $loadBalancer): TransporterInterface;
}