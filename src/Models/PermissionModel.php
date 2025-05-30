<?php
namespace WorkflowManager\Models;

require_once __DIR__ . '/../Config/db_conn.php';

use RedBeanPHP\R;

class PermissionModel
{
    public function getAllPermissions()
    {
        return R::findAll('permissions');
    }

    public function getPermissionByName($name)
    {
        return R::findOne('permissions', 'name = ?', [$name]);
    }

    public function getPermissionById($id)
    {
        return R::load('permissions', $id);
    }

    public function createPermission($name)
    {
        $bean = R::dispense('permissions');
        $bean->name = $name;
        return R::store($bean);
    }

    public function deletePermission($id)
    {
        $bean = R::load('permissions', $id);
        if ($bean->id) {
            R::trash($bean);
            return true;
        }
        return false;
    }

    public function assignPermissionToUser($user_id, $permission_id)
    {
        $bean = R::dispense('user_permissions');
        $bean->user_id = $user_id;
        $bean->permission_id = $permission_id;
        return R::store($bean);
    }

    public function assignPermissionToRole($role_id, $permission_id)
    {
        $bean = R::dispense('role_permissions');
        $bean->role_id = $role_id;
        $bean->permission_id = $permission_id;
        return R::store($bean);
    }

    public function getUserPermissions($user_id)
    {
        return R::getCol('
            SELECT p.name 
            FROM permissions p 
            INNER JOIN user_permissions up ON up.permission_id = p.id 
            WHERE up.user_id = ?', [$user_id]);
    }

    public function getRolePermissions($role_id)
    {
        return R::getCol('
            SELECT p.name 
            FROM permissions p 
            INNER JOIN role_permissions rp ON rp.permission_id = p.id 
            WHERE rp.role_id = ?', [$role_id]);
    }

    public function getUserRoles($user_id)
    {
        return R::getCol('
            SELECT role_id 
            FROM user_roles 
            WHERE user_id = ?', [$user_id]);
    }

    public function userHasPermission($user_id, $permission_name)
    {
        // Direct user permissions
        $userPermissions = $this->getUserPermissions($user_id);
        if (in_array($permission_name, $userPermissions)) {
            return true;
        }

        // Role-based permissions
        $roles = $this->getUserRoles($user_id);
        foreach ($roles as $role_id) {
            $rolePermissions = $this->getRolePermissions($role_id);
            if (in_array($permission_name, $rolePermissions)) {
                return true;
            }
        }

        return false;
    }
}
