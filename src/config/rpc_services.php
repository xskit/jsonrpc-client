<?php
/**
 * @since 2.0
 * @see json rpc
 */

return [
    'nodes' => [
        'default' => [
            [
                'host' => '127.0.0.1',
                'port' => 18307,
                'settings' => [
                    'connect_timeout' => 5.0,
                    'receive_timeout' => 5.0,
                    'eof' => "\r\n",
                ],
                // 默认协议
                'protocol' => \XsKit\RpcClient\Protocol\DefaultProtocol::class,

            ]
        ]
    ],

    'consumers' => [
        [
            'name' => 'FooService', //服务名
            'node' => 'default', //服务节点名
        ]
    ]

];