<?php

namespace WorkflowManager\Models;

require_once __DIR__ . '/../Config/db_conn.php';

use RedBeanPHP\R;

class ActionLogModel {

    public function logAction($instanceId, $currentStage, $userId, $role,$actionType, $details, $context = 'instance', $metadata = null) {
        $log = R::dispense('actionlog');
        $log->instanceId    = $instanceId;
        $log->currentStage  = $currentStage;
        $log->userId        = $userId;
        $log->user_role     = $role;
        $log->actionType    = $actionType;
        $log->details       = $details;
        $log->context       = $context;

        // Handle metadata as JSON
        if ($metadata && is_array($metadata)) {
            $log->metadata = json_encode($metadata);
        } else {
            $log->metadata = null;
        }

        $log->timestamp = date('Y-m-d H:i:s');
        $id =  R::store($log);
        if (!$id) {
            throw new \RuntimeException("Failed to log action for instanceId: {$instanceId}");
        }
        return $id;
    }

    public function getApprovedHistoryByRole($parent_workflow_id, $role, $employee_id) {
        if (empty($parent_workflow_id) || empty($role) || empty($employee_id)) {
            return [];
        }
        return R::findAll('actionlog', ' parent_workflow_id = ? AND user_role = ? AND user_id = ? ORDER BY timestamp DESC', 
            [$parent_workflow_id, $role, $employee_id]);
    }

    public function getLogs($instanceId = null) {
        if ($instanceId) {
            return R::findAll('actionlog', ' instance_id = ? ORDER BY timestamp DESC', [$instanceId]);
        }
        return R::findAll('actionlog', ' ORDER BY timestamp DESC');
    }
}
