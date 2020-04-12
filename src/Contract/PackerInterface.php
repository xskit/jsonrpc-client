<?php
/**
 * Created by PhpStorm.
 * User: Xingshun <250915790@qq.com>
 * Date: 2020/3/10
 * Time: 20:02
 */

namespace XsKit\RpcClient\Contract;

/**
 * 数据封包拆包
 * Interface PackerInterface
 * @package XsKit\RpcClient\Contract
 */
interface PackerInterface
{
    public function pack($data): string;

    public function unpack(string $data);
}