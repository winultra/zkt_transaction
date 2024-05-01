<?php

namespace Custom\ZKTConnect;

use Bitrix\Main\Config\Option;
use Bitrix\Main\UserGroupTable;
use Bitrix\Main\Web\Json;
use Throwable;

class AccessManager extends AccessManagerBase
{

    public function getUserRoles(int $userId): UserRoles
    {
        return new UserRoles($userId, $this->roleGroupMap);
    }

    protected function canUpdateTransactions(int $userId): bool
    {
        $groups = Option::get('custom.zktconnect', 'can_update_groups', []);

        try {
            $groups = Json::decode($groups);
        } catch (Throwable $e) {
            $groups = [];
        }

        $userGroup = UserGroupTable::getRow([
            'filter' => [
                '=USER_ID' => $userId,
                '=GROUP_ID' => $groups,
            ],
            'select' => [
                'GROUP_ID'
            ]
        ]);

        return !empty($userGroup);
    }
}
