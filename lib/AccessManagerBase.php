<?php

namespace Custom\ZKTConnect;

abstract class AccessManagerBase
{
    protected $roleGroupMap = [];

    public function __construct(array $config)
    {
        if (array_key_exists('roleGroupMap', $config)) {
            $this->roleGroupMap = $config['roleGroupMap'];
        }
    }

    public function getRoleGroupMap(): array
    {
        return $this->roleGroupMap;
    }

    public function can(int $userId, string $action, $context = null)
    {
        $actionMethod = "can" . $action;
        if (!method_exists($this, $actionMethod)) {
            throw new \Exception('Action ' . $action . ' is unknown.');
        }

        return $this->$actionMethod($userId, $context);
    }
}
