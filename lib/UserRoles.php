<?php

namespace Custom\ZKTConnect;

use Bitrix\Main\UserGroupTable;

class UserRoles
{
    const ROLE_ADMIN = 'admin';

    private array $roles = [];

    public function __construct(int $userId, array $roleGroupMap)
    {
        $userRoles = UserGroupTable::getList([
            'filter' => [
                '=USER_ID' => $userId,
                '=GROUP_ID' => $roleGroupMap
            ]
        ]);

        foreach ($userRoles as $userRole) {
            $role = array_search($userRole['GROUP_ID'], $roleGroupMap);

            if ($role) {
                $this->roles[] = $role;
            }
        }

    }

    public function has(string $role): bool
    {
        return in_array($role, $this->roles);
    }
}
