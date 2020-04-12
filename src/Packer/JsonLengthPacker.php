<?php

declare(strict_types=1);

namespace XsKit\Swoft\Packer;

use XsKit\RpcClient\Contract\PackerInterface;

class JsonLengthPacker implements PackerInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var int
     */
    protected $length;

    protected $defaultOptions = [
        'package_length_type' => 'N',
        'package_body_offset' => 4,
    ];

    public function __construct(array $options = [])
    {
        $options = array_merge($this->defaultOptions, $options['settings'] ?? []);

        $this->type = $options['package_length_type'];
        $this->length = $options['package_body_offset'];
    }

    public function pack($data): string
    {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        return pack($this->type, strlen($data)) . $data;
    }

    public function unpack(string $data)
    {
        $data = substr($data, $this->length);
        return json_decode($data, true);
    }
}
