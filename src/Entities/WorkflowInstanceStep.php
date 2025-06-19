<?php

namespace WorkflowManager\Entities;

class WorkflowInstanceStep {
    public $workflow_id_;
    public $workflow_step_id_;
    public $workflow_instance_step_id_;
    public $workflow_instance_id_;
    public $workflow_instance_step_position; 
    public $is_user_id_dynamic;
    public $workflow_instance_step_user_role;
    public $workflow_instance_step_user_id;    
    public $workflow_instance_step_description;
    public $requires_multiple_approvals;         // true if approvers > 1
    public $approval_mode;                  // 'all', 'any', 'n-of-m', 'specific-user'
    public $execution_mode;                 // 'parallel' or 'sequential'
    public $approver_count_required;        // for n-of-m
    public $required_approver_user_id;      // specific-user
    public $approval_sequence;              // ['user1', 'user2'] for sequential
    public $parallel_group_id;              // for grouping parallel steps
    public $workflow_instance_next_step = null;
    public $workflow_instance_previous_step = null;
    public $workflow_instance_step_revoke_target = null;  
    public $revokeConditions = []; 

    public function __construct(
        $workflow_id_,
        $workflow_instance_id_,
        $workflow_step_id_,
        $workflow_instance_step_id_,
        $workflow_instance_step_position,
        $is_user_id_dynamic = null,
        $workflow_instance_step_user_role = null,
        $workflow_instance_step_user_id = null,
        $workflow_instance_step_description = "",
        $requires_multiple_approvals = false,
        $approval_mode = "",
        $execution_mode = "",
        $approver_count_required = null,
        $required_approver_user_id = null,
        $approval_sequence = [],
        $parallel_group_id = null,
        $workflow_instance_next_step = null,
        $workflow_instance_previous_step = null,
        $workflow_instance_step_revoke_target = null
    ) {
        $this->workflow_id_ = $workflow_id_;
        $this->workflow_instance_id_ = $workflow_instance_id_;
        $this->workflow_step_id_ = $workflow_step_id_;
        $this->workflow_instance_step_id_ = $workflow_instance_step_id_;
        $this->workflow_instance_step_position = $workflow_instance_step_position;
        $this->is_user_id_dynamic = $is_user_id_dynamic;
        $this->workflow_instance_step_user_role = $workflow_instance_step_user_role;
        $this->workflow_instance_step_user_id = $workflow_instance_step_user_id;
        $this->workflow_instance_step_description = $workflow_instance_step_description;
        $this->requires_multiple_approvals = $requires_multiple_approvals; // true if approvers > 1
        $this->approval_mode = $approval_mode; // 'all', 'any', 'n-of-m', 'specific-user'
        $this->execution_mode = $execution_mode; // 'parallel' or 'sequential'
        $this->approver_count_required = $approver_count_required; // for n-of-m
        $this->required_approver_user_id = $required_approver_user_id; // specific-user
        $this->approval_sequence = $approval_sequence; // ['user1', 'user2'] for sequential
        $this->parallel_group_id = $parallel_group_id; // for grouping parallel steps
        $this->workflow_instance_next_step = $workflow_instance_next_step;
        $this->workflow_instance_previous_step = $workflow_instance_previous_step;
        $this->workflow_instance_step_revoke_target = $workflow_instance_step_revoke_target;
    }

    public function addRevokeCondition(RevokeCondition $condition) {
        $this->revokeConditions[] = $condition;
    }

    public function findResumeStep() {
        foreach ($this->revokeConditions as $condition) {
            return $condition->getResumeStepId();
        }
        return null;
    }

    public function moveToRevokeTarget() {
        print_r($this->revokeConditions);
        if (!empty($this->revokeConditions)) {
            return $this->revokeConditions[0]->getTargetStepId();
        }
        return null;
    }


    public function moveToNextStep() {
        return $this->workflow_instance_next_step;
    }

    public function moveToPreviousStep() {
        return $this->workflow_instance_previous_step;
    }
   

    public function getCurrentUserRole() {
        // You might need to fetch this from session or a user object associated with the step
        return $this->currentUserRole;  // Assuming this property is set somewhere
    }

    public function getLastAction() {
        // This method should return the last action performed; perhaps tracked via state management
        return $this->lastAction;  // Assuming this property is tracked
    }
}

?>
