<?php

namespace Custom\ZKTConnect;

use Bitrix\Main\Localization\Loc;

class Menu
{

    public static function getItems(): array
    {
        return [
            [
                'ID' => 'group_zktconnect',
                'TEXT' => Loc::getMessage('MENU_ITEM_TRANSACTION_LIST'),
                'LINK' => "/zkt/transaction/list/",
                'SELECTED' => false,
                'ADDITIONAL_LINKS' => NULL,
                'ITEM_TYPE' => 'system_group',
                'PERMISSION' => 'R',
                'DELETE_PERM' => 'N',
                'PARAMS' =>
                    [
                        'menu_item_id' => 'group_zktconnect',
                        'parent_id' => NULL,
                    ],
            ]
        ];
    }
}
