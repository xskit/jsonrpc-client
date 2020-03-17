<?php
/**
 * Created by PhpStorm.
 * User: Xingshun <250915790@qq.com>
 * Date: 2020/3/11
 * Time: 0:18
 */

namespace XsKit\Swoft\Packer;

use XsKit\RpcClient\Contract\PackerInterface;

class JsonPacker implements PackerInterface
{
    public function pack($data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function unpack(string $data)
    {
        return json_decode($data, true);
    }
}