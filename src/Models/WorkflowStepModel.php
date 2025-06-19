<?php
namespace WorkflowManager\Models;

require_once __DIR__ . '/../Config/db_conn.php';

use RedBeanPHP\R;

class WorkflowStepModel {
    
    public function insert($step, $nextStepId = null, $previousStepId = null)
    {
        $bean = R::dispense('step');

        $bean->workflow_id_ = $step->workflow_id_;
        $bean->step_id_ = $step->step_id_;
        $bean->step_user_role = $step->step_user_role;
        $bean->requires_user_id = $step->requires_user_id;
        $bean->is_user_id_dynamic = $step->is_user_id_dynamic;
        $bean->step_position = $step->step_position;
        $bean->step_description = $step->step_description;
        $bean->requires_multiple_approvals = $step->requires_multiple_approvals;
        $bean->approver_mode = $step->approver_mode;    
        $bean->execution_mode = $step->execution_mode;
        $bean->approval_count_required = !empty($step->approval_count_required) 
        ? (int)$step->approval_count_required 
        : 0; // or some other default value

        $bean->step_next_step = is_object($step->step_next_step)
            ? $step->step_next_step->step_id_
            : $nextStepId;

        $bean->step_previous_step = is_object($step->step_previous_step)
            ? $step->step_previous_step->step_id_
            : $previousStepId;

        $id = R::store($bean);

        if (!$id) {
            throw new \RuntimeException("Failed to insert step with step_id: {$step->step_id_}");
        }

        return $id;
    }

    
    public function getAllWorkflowSteps($workflow_id_) 
    {
        $steps = R::findAll('step', 'workflow_id_ = ?', [$workflow_id_],'ORDER BY step_position');
        
        if(!$steps){
            throw new \RuntimeException("No steps found for workflow_id: $workflow_id_");
        }
        
        return $steps;

    }

    //Nitesh added to get step position by role and workflow id
    public function getStepPositionByRole($workflow_id, $role) {
        $step = R::findOne('step', 'workflow_id_ = ? AND step_user_role = ?', [$workflow_id, $role]);
        return $step->step_position ?? null;
    }
    
}


?>
