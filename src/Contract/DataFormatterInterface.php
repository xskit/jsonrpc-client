<?php
/**
 * Created by PhpStorm.
 * User: Xingshun <250915790@qq.com>
 * Date: 2020/3/10
 * Time: 19:49
 */

namespace XsKit\RpcClient\Contract;

/**
 * 数据格式化
 * Interface DataFormatterInterface
 * @package XsKit\RpcClient\Contract
 */
interface DataFormatterInterface
{
    /**
     * @param array $data [$path, $params, $id]
     * @return array
     */
    public function formatRequest($data);

    /**
     * @param array $data [$id, $result]
     * @return array
     */
    public function formatResponse($data);

    /**
     * @param array $data [$id, $code, $message, $exception]
     * @return array
     */
    public function formatErrorResponse($data);
}