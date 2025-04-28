<?php

namespace WorkflowManager\Services;

class PermissionService
{
    public static function hasPermission($userId, $permissionName)
    {
        $perm = PermissionModel::getPermissionByName($permissionName);
        if (!$perm) return false;

        if (PermissionModel::hasUserPermission($userId, $perm->id)) {
            return true;
        }

        $roles = PermissionModel::getUserRoleIds($userId);
        return PermissionModel::hasRolePermission($roles, $perm->id);
    }
}
