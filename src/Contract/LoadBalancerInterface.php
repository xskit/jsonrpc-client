<?php
/**
 * Created by PhpStorm.
 * User: Xingshun <250915790@qq.com>
 * Date: 2020/3/10
 * Time: 20:19
 */

namespace XsKit\RpcClient\Contract;

use XsKit\RpcClient\Node;

/**
 * 负载平衡器
 * Interface LoadBalancerInterface
 * @package XsKit\RpcClient\Contract
 */
interface LoadBalancerInterface
{
    /**
     * Select an item via the load balancer.
     */
    public function select(array ...$parameters): Node;

    /**
     * @param Node[] $nodes
     * @return $this
     */
    public function setNodes(array $nodes);

    /**
     * @return Node[] $nodes
     */
    public function getNodes(): array;
}