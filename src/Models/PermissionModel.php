<?php

namespace WorkflowManager\Models;

require_once __DIR__ . '/../Config/db_conn.php';

use RedBeanPHP\R;

class PermissionModel
{
    public static function getPermissionByName($name)
    {
        return R::findOne('permissions', 'name = ?', [$name]);
    }

    public static function getUserRoleIds($userId)
    {
        return R::getCol('SELECT role_id FROM user_roles WHERE user_id = ?', [$userId]);
    }

    public static function hasUserPermission($userId, $permissionId)
    {
        return R::findOne('user_permissions', 'user_id = ? AND permission_id = ?', [$userId, $permissionId]);
    }

    public static function hasRolePermission($roleIds, $permissionId)
    {
        if (empty($roleIds)) {
            return false;
        }

        $permCount = R::getCell(
            'SELECT COUNT(*) FROM role_permissions WHERE permission_id = ? AND role_id IN (' . R::genSlots($roleIds) . ')',
            array_merge([$permissionId], $roleIds)
        );

        return $permCount > 0;
    }
}
