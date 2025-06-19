<?php

namespace WorkflowManager\Entities;

use WorkflowManager\Entities\WorkflowStep;

class Workflow {
    public $workflow_name;
    public $workflow_id_; 
    public $parent_workflow_id_; 
    public $workflow_version_id_; 
    public $workflow_version; 
    public $workflow_head_node = null;
    public $workflow_step_len;
    public $workflow_description;
    public $created_by_user_id;
    public $version_timestamp;
    public $is_latest = false;
    public $is_active = true;

    public function __construct(
        $name,
        $workflow_id_ = "", 
        $parent_workflow_id_ = "",
        $workflow_description = "",
        $created_by_user_id = null
    ) {
        $this->workflow_name = $name;
        $this->workflow_id_ = $workflow_id_;
        $this->parent_workflow_id_ = $parent_workflow_id_;
        $this->workflow_description = $workflow_description;
        $this->created_by_user_id = $created_by_user_id;
        $this->workflow_step_len = 0;
        $this->version_timestamp = time();
    }

    public function addStep($step_id_, $step_user_role, $requires_user_id , $is_user_id_dynamic ,$stepDescription='',$requires_multiple_approvals=0, $approver_mode = "", $execution_mode = "", $approval_count_required = null) {
         
        $step_position = 1;
        if ($this->workflow_head_node !== null) {
            $current = $this->workflow_head_node;
            while ($current->step_next_step !== null) {
                $current = $current->step_next_step;
                $step_position++;
            }
            $step_position++;
        }
        $new_step = new WorkflowStep($this->workflow_id_, $step_id_, $step_user_role, $requires_user_id, $is_user_id_dynamic  ,$step_position,$stepDescription, $requires_multiple_approvals, $approver_mode, $execution_mode, $approval_count_required);
        if ($this->workflow_head_node === null) {
            $this->workflow_head_node = $new_step;
        } else {
            $current = $this->workflow_head_node;
            while ($current->step_next_step !== null) {
                $current = $current->step_next_step;
            }
            
            $current->step_next_step = $new_step;
            $new_step->step_previous_step = $current;
        }
        $this->workflow_step_len++;
        return $new_step;
    }


}
