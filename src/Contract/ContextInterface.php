<?php
/**
 * Created by PhpStorm.
 * User: Xingshun <250915790@qq.com>
 * Date: 2020/4/12
 * Time: 16:42
 */

namespace XsKit\RpcClient\Contract;


interface ContextInterface
{
    public function getData(): array;

    public function setData(array $data): void;

}