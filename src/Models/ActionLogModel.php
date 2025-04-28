<?php

namespace WorkflowManager\Models;

require_once __DIR__ . '/../Config/db_conn.php';

use RedBeanPHP\R;

class ActionLogModel {

    public function logAction($instanceId, $currentStage, $userId, $actionType, $details, $context = 'instance', $metadata = null) {
        $log = R::dispense('actionlog');
        $log->instanceId    = $instanceId;
        $log->currentStage  = $currentStage;
        $log->userId        = $userId;
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
        return R::store($log);
    }

    public function getLogs($instanceId = null) {
        if ($instanceId) {
            return R::findAll('actionlog', ' instance_id = ? ORDER BY timestamp DESC', [$instanceId]);
        }
        return R::findAll('actionlog', ' ORDER BY timestamp DESC');
    }
}
