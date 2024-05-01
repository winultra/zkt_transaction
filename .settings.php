<?php

use Bitrix\Main\Config\Option;

return [
    'services' => [
        'value' => [
            'custom.zktconnect.client' => [
                'constructor' => static function () {
                    $host = Option::get('custom.zktconnect', 'api_host', '');
                    $username = Option::get('custom.zktconnect', 'api_username', '');
                    $password = Option::get('custom.zktconnect', 'api_password', '');

                    return new \Custom\ZKTConnect\Rest\Client($host, $username, $password);
                }
            ],
            'custom.zktconnect.service' => [
                'constructor' => static function () {
                    return new \Custom\ZKTConnect\Service();
                }
            ],
            'custom.zktconnect.accessManager' => [
                'constructor' => static function () {
                    $config = [
                        'roleGroupMap' => [
                            \Custom\ZKTConnect\UserRoles::ROLE_ADMIN => 1,
                        ]
                    ];

                    return new \Custom\ZKTConnect\AccessManager($config);
                }
            ],
        ]
    ]
];
