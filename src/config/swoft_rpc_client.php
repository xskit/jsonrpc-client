<?php
/**
 * @since 2.0
 * @see json rpc
 */

return [
    'connection' => [
        'default' => [
            'host' => '127.0.0.1',
            'port' => 18307,
            'setting' => [
                'timeout' => 2,
                'write_timeout' => 10,
            ]
        ]
    ],

    'services' => [
        'foo' => [
            'class' => '', //接口类名
            'version' => '1.0'
        ]
    ]

];