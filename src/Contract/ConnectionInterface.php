<?php
/**
 * Created by PhpStorm.
 * User: Xingshun <250915790@qq.com>
 * Date: 2020/3/10
 * Time: 22:29
 */

namespace XsKit\RpcClient\Contract;

/**
 * 连接
 * Interface ConnectionInterface
 * @package XsKit\RpcClient\Contract
 */
interface ConnectionInterface
{
    /**
     * 重新连接
     */
    public function reconnect(): bool;

    /**
     * 获取一个连接
     * @return $this
     */
    public function getConnection();

    /**
     * 检查连接是否可用
     * @return bool
     */
    public function check(): bool;

    /**
     * 关闭连接
     */
    public function close();

    /**
     * 发布连接到连接池
     */
    public function release();


}