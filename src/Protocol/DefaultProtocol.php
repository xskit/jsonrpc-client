<?php
/**
 * Created by PhpStorm.
 * User: Xingshun <250915790@qq.com>
 * Date: 2020/4/12
 * Time: 16:23
 */

namespace XsKit\RpcClient\Protocol;


use XsKit\RpcClient\Contract\DataFormatterInterface;
use XsKit\RpcClient\Contract\PackerInterface;
use XsKit\RpcClient\Contract\TransporterInterface;

class DefaultProtocol
{

    public function getPacker(): PackerInterface
    {

    }

    public function getTransporter(): TransporterInterface
    {

    }

    public function getDataFormatter(): DataFormatterInterface
    {

    }
}