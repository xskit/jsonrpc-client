<?php
/**
 * Created by PhpStorm.
 * User: Xingshun <250915790@qq.com>
 * Date: 2019/9/27
 * Time: 20:05
 */

namespace XsKit\RpcClient;


use XsKit\RpcClient\Contract\PackerInterface;
use XsKit\RpcClient\Contract\TransporterInterface;

/**
 * RPC 服务客户端
 * Class Client
 * @see  jsonrpc
 * @since 2.0
 */
class Client
{
    /**
     * @var PackerInterface
     */
    private $packer;

    /**
     * @var TransporterInterface
     */
    private $transporter;

    private $id;

    /**
     * 生成请求ID
     * @return string
     */
    private function generatorRequestId()
    {
        try {
            return bin2hex(random_bytes(8));
        } catch (\Exception $e) {
            return '';
        }
    }


    /**
     * 获取请求ID
     * @return string
     */
    public function getRequestId()
    {
        return $this->id ?: $this->generatorRequestId();
    }

    /**
     * 设置请求ID
     * @param $id
     * @return $this
     */
    public function setRequestId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getPacker(): PackerInterface
    {
        return $this->packer;
    }

    public function setPacker(PackerInterface $packer): self
    {
        $this->packer = $packer;
        return $this;
    }

    public function getTransporter(): TransporterInterface
    {
        return $this->transporter;
    }

    public function setTransporter(TransporterInterface $transporter): self
    {
        $this->transporter = $transporter;
        return $this;
    }
}