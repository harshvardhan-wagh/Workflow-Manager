<?php
namespace WorkflowManager\Entities;

class WorkflowInstanceStepApprover {
    public $workflow_instance_step_id_;
    public $workflow_instance_id_;
    public $approver_user_id;
    public $approver_role;
    public $approval_status;   // 'pending', 'approved', 'rejected'
    public $approval_time;
    public $is_current;
    public $comments;

    public function __construct(
        $workflow_instance_step_id_,
        $workflow_instance_id_,
        $approver_user_id,
        $approver_role = null,
        $approval_status = 'pending',
        $is_current = true,
        $approval_time = null,
        $comments = ''
    ) {
        $this->workflow_instance_step_id_ = $workflow_instance_step_id_;
        $this->workflow_instance_id_ = $workflow_instance_id_;
        $this->approver_user_id = $approver_user_id;
        $this->approver_role = $approver_role;
        $this->approval_status = $approval_status;
        $this->is_current = $is_current;
        $this->approval_time = $approval_time;
        $this->comments = $comments;
    }
}
