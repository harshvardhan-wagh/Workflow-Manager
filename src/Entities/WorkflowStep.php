<?php

namespace WorkflowManager\Entities;

use WorkflowManager\Entities\RevokeCondition;

class WorkflowStep {
    public $workflow_id_;
    public $step_id_;
    public $step_user_role;
    public $requires_user_id;  
    public $is_user_id_dynamic; 
    public $step_position;
    public $step_description;
    public $step_next_step = null;
    public $step_previous_step = null;
    public $revokeConditions = [];

    public function __construct(
        $workflow_id_, 
        $step_id_, 
        $step_user_role, 
        $requires_user_id, 
        $is_user_id_dynamic, 
        $step_position, 
        $step_description = ""
        ) {
        $this->workflow_id_ = $workflow_id_;
        $this->step_id_ = $step_id_;
        $this->step_user_role = $step_user_role;
        $this->requires_user_id = $requires_user_id;
        $this->is_user_id_dynamic = $is_user_id_dynamic;
        $this->step_position = $step_position;
        $this->step_description = $step_description;
    }

    public function addRevokeCondition(RevokeCondition $condition) {
        $this->revokeConditions[] = $condition;
    }

    public function moveToRevokeTarget() {
        // If there's any condition set, just use the first one (simplified logic)
        if (!empty($this->revokeConditions)) {
            return $this->revokeConditions[0]->getTargetStepId();
        }
        return null;
    }

    public function findResumeStep() {
        // echo "\nFind Resume Step ";
        // print_r($this);
        foreach ($this->revokeConditions as $condition) {
            // echo"\nReturn : ";
            // print_r($condition->getResumeStepId());
            // echo "\nReturn complete";
            return $condition->getResumeStepId();
        }
        return null;
    }



}
