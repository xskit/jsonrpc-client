<?php
/**
 * Created by PhpStorm.
 * User: Xingshun <250915790@qq.com>
 * Date: 2020/3/10
 * Time: 20:20
 */

namespace XsKit\RpcClient;


class Node
{
    /**
     * @var int
     */
    public $weight;

    /**
     * @var string
     */
    public $host;

    /**
     * @var int
     */
    public $port;

    /**
     * Node constructor.
     * @param string $host
     * @param int $port
     * @param int $weight
     */
    public function __construct(string $host, int $port, int $weight = 0)
    {
        $this->host = $host;
        $this->port = $port;
        $this->weight = $weight;
    }
}