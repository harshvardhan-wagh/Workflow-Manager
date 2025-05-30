<?php 

namespace WorkflowManager\Services;

use WorkflowManager\Models\PermissionModel;

class PermissionService
{
    protected $permissionModel;

    public function __construct()
    {
        $this->permissionModel = new PermissionModel();
    }

    public function userHasPermission($userId, $permissionName)
    {
        // Check user-level permissions
        if ($this->permissionModel->userHasPermission($userId, $permissionName)) {
            return true;
        }
        // Check role-level permissions
        $roles = $this->permissionModel->getUserRoles($userId);
        foreach ($roles as $roleId) {
            if ($this->permissionModel->roleHasPermission($roleId, $permissionName)) {
                return true;
            }
        }

        return false;
    }
}
