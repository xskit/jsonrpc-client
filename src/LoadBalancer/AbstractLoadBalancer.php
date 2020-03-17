<?php
/**
 * Created by PhpStorm.
 * User: Xingshun <250915790@qq.com>
 * Date: 2020/3/10
 * Time: 20:38
 */

namespace XsKit\RpcClient\LoadBalancer;


use XsKit\RpcClient\Contract\LoadBalancerInterface;
use XsKit\RpcClient\Node;

abstract class AbstractLoadBalancer implements LoadBalancerInterface
{

    /**
     * @var Node []
     */
    protected $nodes;

    public function __construct(array $nodes = [])
    {
        $this->nodes = $nodes;
    }

    /**
     * @param array $nodes
     * @return $this|LoadBalancerInterface
     */
    public function setNodes(array $nodes)
    {
        $this->nodes = $nodes;
        return $this;
    }

    /**
     * @return array
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * Remove a node from the node list.
     */
    public function removeNode(Node $node): bool
    {
        foreach ($this->nodes as $key => $activeNode) {
            if ($activeNode === $node) {
                unset($this->nodes[$key]);
                return true;
            }
        }
        return false;
    }

}