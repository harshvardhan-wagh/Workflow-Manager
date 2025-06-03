<?php
namespace WorkflowManager\Models;

require_once __DIR__ . '/../Config/db_conn.php';

use RedBeanPHP\R;

class RevokeConditionModel {
   

    public function insert($workflowId, $stepId, $revokeCondition)
    {
        if (!$revokeCondition instanceof \WorkflowManager\Entities\RevokeCondition) {
            throw new \InvalidArgumentException("Expected instance of RevokeCondition");
        }
    
        $bean = R::dispense('revokeconditions');
        $bean->workflow_id_ = $workflowId;
        $bean->step_id_ = $stepId;
        $bean->target_step_id = $revokeCondition->getTargetStepId();
        $bean->resume_step_id = $revokeCondition->getResumeStepId();
    
        $id = R::store($bean);
    
        if (!$id) {
            throw new \RuntimeException("Failed to insert RevokeCondition for workflow_id: $workflowId, step_id: $stepId");
        }
    
        return $id;
    }
    

    public function getRevokeConditionIdByStepId($stepId) {
        $result = R::getCell('SELECT id FROM revokeconditions WHERE step_id_ = ?', [$stepId]);
        return $result;
    }
    

    public function getRevokeConditionsForStep($stepId) {
        $result = R::getAll('SELECT * FROM revokeconditions WHERE step_id_ = ?', [$stepId]);
        return $result;
    }
    

    public function getByWorkflowId($workflowId) {
        return R::findAll('revoke_conditions', 'workflow_id_ = ?', [$workflowId]);
    }

    public function deleteByStepId($stepId) {
        $conditions = R::findAll('revoke_conditions', 'step_id_ = ?', [$stepId]);
        foreach ($conditions as $condition) {
            R::trash($condition);
        }
    }

    public function getResumeStepIdById($id) {
        $result = R::getCell('SELECT resume_step_id FROM revokeconditions WHERE id = ?', [$id]);
        return $result;
    }
}
