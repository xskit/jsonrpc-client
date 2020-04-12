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
use InvalidArgumentException;

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

    /**
     * 发送数据
     * @param $data
     * @return mixed
     */
    public function send($data)
    {
        if (!$this->packer) {
            throw new InvalidArgumentException('Packer missing');
        }
        if (!$this->transporter) {
            throw new InvalidArgumentException('Transporter missing');
        }

        $packer = $this->getPacker();
        $packedData = $packer->pack($data);
        $response = $this->getTransporter()->send($packedData);
        return $packer->unpack((string)$response);
    }

    /**
     * 接收数据
     * @return mixed
     */
    public function recv()
    {
        $response = $this->getTransporter()->receive();
        $packer = $this->getPacker();
        return $packer->unpack((string)$response);
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