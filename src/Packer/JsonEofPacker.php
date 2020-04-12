<?php
declare(strict_types=1);

namespace XsKit\Swoft\Packer;

use XsKit\RpcClient\Contract\PackerInterface;

class JsonEofPacker implements PackerInterface
{
    /**
     * @var string
     */
    protected $eof;

    public function __construct(array $options = [])
    {
        $this->eof = $options['settings']['package_eof'] ?? "\r\n";
    }

    public function pack($data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE) . $this->eof;
    }

    public function unpack(string $data)
    {
        return json_decode($data, true);
    }
}