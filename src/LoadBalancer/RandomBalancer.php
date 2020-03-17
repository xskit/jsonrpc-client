<?php
/**
 * Created by PhpStorm.
 * User: Xingshun <250915790@qq.com>
 * Date: 2020/3/10
 * Time: 20:32
 */

namespace XsKit\RpcClient\LoadBalancer;


use XsKit\RpcClient\Contract\LoadBalancerInterface;
use XsKit\RpcClient\Node;

class RandomBalancer extends AbstractLoadBalancer implements LoadBalancerInterface
{
    /**
     * Select an item via the load balancer.
     */
    public function select(array ...$parameters): Node
    {
        if (empty($this->nodes)) {
            throw new \RuntimeException('Cannot select any node from load balancer.');
        }
        $key = array_rand($this->nodes);
        return $this->nodes[$key];
    }

}