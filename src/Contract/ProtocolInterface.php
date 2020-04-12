<?php
/**
 * Created by PhpStorm.
 * User: Xingshun <250915790@qq.com>
 * Date: 2020/4/12
 * Time: 16:51
 */

namespace XsKit\RpcClient\Contract;


interface ProtocolInterface
{
    public function getPacker(): PackerInterface;


    public function getTransporter(): TransporterInterface;


    public function getDataFormatter(): DataFormatterInterface;
}