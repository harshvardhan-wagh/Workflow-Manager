<?php

namespace WorkflowManager\Models;

require_once __DIR__ . '/../Config/db_conn.php';

use RedBeanPHP\R;

class RevocationLogModel {

    private $tableName = 'revokelog';

    public function __construct() {
        // No initialization needed yet
    }

    /**
     * Log a revoke action performed on a workflow instance.
     */
    public function logRevocation($workflowInstanceID, $fromStepID, $toStepID, $ruleAppliedID = null, $revokedByUserID = null, $remarks = null) {
        $log = R::dispense($this->tableName);

        $log->workflow_instance_id = $workflowInstanceID;
        $log->from_step_id = $fromStepID;
        $log->to_step_id = $toStepID;
        $log->rule_applied_id = $ruleAppliedID;
        $log->revoked_by_user_id = $revokedByUserID;
        $log->remarks = $remarks;
        $log->created_at = R::isoDateTime();

        $id =  R::store($log); // returns inserted ID

        if (!$id) {
            throw new \RuntimeException("Failed to log revocation for workflow instance ID: $workflowInstanceID");
        }

        return $id;
    }

    /**
     * Get all revoke logs for a specific workflow instance.
     */
    public function getLogsByInstance($workflowInstanceID) {
        return R::findAll($this->tableName, 'workflow_instance_id = ? ORDER BY created_at DESC', [$workflowInstanceID]);
    }

    /**
     * Get a specific revoke log by ID.
     */
    public function getLogById($logID) {
        return R::load($this->tableName, $logID);
    }

    public function getInstanceLatestRuleId($workflowInstanceID) {
        $result = R::getCell('SELECT rule_applied_id FROM revokelog WHERE workflow_instance_id = ? ORDER BY created_at DESC LIMIT 1', [$workflowInstanceID]);
        if(!$result) {
            throw new \RuntimeException("No revoke log found for workflow instance ID: $workflowInstanceID");
        }
        return $result;
    }
}
