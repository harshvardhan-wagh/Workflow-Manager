<?php

namespace WorkflowManager\Entities;

use WorkflowManager\Entities\WorkflowInstanceStep;

class WorkflowInstance {
    public $workflow;
    public $workflow_instance_id_; 
    public $workflow_instance_name;
    public $workflow_instance_description;
    public $workflow_instance_stage = 2;  
    public $workflow_instance_steps_head_node = null;  
    public $revoked_stage = null; 
    public $created_by_user_id = null;

    public function __construct(
        $workflow, 
        $workflow_instance_id_, 
        $workflow_instance_name, 
        $created_by_user_id=null, 
        ) {
        $this->workflow = $workflow;
        $this->workflow_instance_id_ = $workflow_instance_id_;
        $this->workflow_instance_name = $workflow_instance_name;
        $this->created_by_user_id = $created_by_user_id;
    }

    public function acceptStep($nextStepEmployeeIds = null)
    {
        try {
            $currentStep = $this->getCurrentStep();
    
            if (!$currentStep) {
                return [
                    'status' => 'error',
                    'action' => 'none',
                    'message' => 'No current step found for this workflow instance.'
                ];
            }
    
            // If workflow was previously revoked and now resuming
            if ($this->revoked_stage) {
                $resumeStep = $this->revoked_stage;
                $this->workflow_instance_stage = $this->findStageByStepId($resumeStep);
                $this->revoked_stage = null;
    
                return [
                    'status' => 'success',
                    'action' => 'resumed',
                    'message' => 'Workflow resumed from revoked stage.'
                ];
            }
    
            if ($currentStep->workflow_instance_next_step) {

                $dynamicSteps = [];
                $nextStep = $currentStep->workflow_instance_next_step;

                $employeeIndex = 0;
               
                while ($nextStep && $nextStep->is_user_id_dynamic == "1") {
                    
                    $stepId = $nextStep->workflow_instance_step_id_;
                    if (!isset($nextStepEmployeeIds[$employeeIndex])) {
                        return [
                            'status' => 'error',
                            'action' => 'none',
                            'message' => "Dynamic user assignment required for step $stepId but no user ID was provided."
                        ];
                    }

                    $dynamicSteps[] = [
                        'step_id' => $stepId,
                        'user_id' =>$nextStepEmployeeIds[$employeeIndex]['user_id'],
                        'role' => $nextStepEmployeeIds[$employeeIndex]['role']
                    ];

                   
                    $nextStep->workflow_instance_step_user_id = $nextStepEmployeeIds[$employeeIndex]['user_id'];
                    $nextStep->is_user_id_dynamic = "0"; 
        
                    $employeeIndex++;
                    $nextStep = $nextStep->workflow_instance_next_step;
                }

                if($nextStep->requires_multiple_approvals == 1) {
                
                 // Getting Approver List
                   $approverList = $approverList ?? [];
                   $specificUserId = $specificUserId ?? '';
                   if (empty($approverList)) {
                        return [
                            'status' => 'error',
                            'action' => 'none',
                            'message' => 'No approvers provided for multiple-approvals step.'
                        ];
                    }

                    // Setting Parallel Group id 
                    $parallelGroupId = uniqid('pg_');
                    $approvalUserList = [];
					
					// Handle specific user case (approval_mode = specific-user)
                        if ($nextStep->approval_mode == 'specific-user') {
                            if (!isset($specificUserId)) {
                                return [
                                    'status' => 'error',
                                    'action' => 'none',
                                    'message' => "Specific user required but not provided for step {$nextStep->workflow_instance_step_id_}."
                                ];
                            }
							$nextStep->required_approver_user_id = $specificUserId;
                        }
                   
                    foreach ($approverList as $index => $approver) {
                        
                        // Handle execution_mode = sequential (store order using 'sequence' field)
                        $approvalUserList[] = [
                            'workflow_instance_step_id_' => $nextStep->workflow_instance_step_id_,
                            'workflow_instance_id_'      => $this->workflow_instance_id_,
                            'approver_user_id'           => $approver['user_id'],
                            'approver_role'              => $approver['role'] ?? null,
                            'approval_status'            => 'pending',
                            'approval_time'              => null,
                            'is_current'                 => ($nextStep->execution_mode === 'sequential') ? ($index === 0 ? 1 : 0) : 1,
                            'sequence'                   => ($nextStep->execution_mode === 'sequential') ? $index : null,
                            'comments'                   => null,
                            'parallel_group_id'          => $parallelGroupId,
                        ];
                    }

                     return [
                    'status' => 'success',
                    'action' => 'store_multiple_approvers',
                    'message' => 'Multiple approvers (parallel/sequential) to be stored.',
                    'approver_list' => $approvalUserList,
                    'step_id' => $nextStep->workflow_instance_step_id_
                ];


                }
        
                if (!empty($dynamicSteps)) {
                    return [
                        'status' => 'success',
                        'action' => 'assign_dynamic_user',
                        'message' => 'Dynamic user assignment required for multiple steps.',
                        'dynamic_steps' => $dynamicSteps
                    ];
                }
    
                // Advance to next stage
                $this->workflow_instance_stage++;
    
                return [
                    'status' => 'success',
                    'action' => 'moved_to_next_stage',
                    'message' => 'Moved to the next stage successfully.'
                ];
            }
    
            // No next step → Final approval
            return [
                'status' => 'success',
                'action' => 'final_stage_approved',
                'message' => 'Final stage reached. Workflow will be marked as completed.'
            ];
    
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'action' => 'none',
                'message' => 'Error occurred during approval: ' . $e->getMessage()
            ];
        }
    }
    

    public function rejectStep() 
    {
        try {
            $currentStep = $this->getCurrentStep();
    
            if (!$currentStep) {
                return [
                    'status' => 'error',
                    'action' => 'none',
                    'message' => 'No current step found. Cannot reject this step.'
                ];
            }
    
            return [
                'status' => 'success',
                'action' => 'halt_instance',
                'message' => 'Step has been rejected and the workflow instance will be halted.'
            ];
    
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'action' => 'none',
                'message' => 'Exception occurred while rejecting step: ' . $e->getMessage()
            ];
        }
    }
    

    public function revokeStep() 
    {
        try {
            $currentStep = $this->getCurrentStep();
    
            if (!$currentStep) {
                return [
                    'status' => 'error',
                    'message' => 'Current step not found. Cannot perform revocation.',
                    'action' => 'none'
                ];
            }
    
            $targetStepId = $currentStep->moveToRevokeTarget();
            $resumeStepId = $currentStep->findResumeStep();
    
            if (!$targetStepId) {
                return [
                    'status' => 'error',
                    'message' => 'No valid target step found for revocation.',
                    'action' => 'none'
                ];
            }
    
            if (!$resumeStepId) {
                return [
                    'status' => 'error',
                    'message' => 'No resume step defined after revocation.',
                    'action' => 'none'
                ];
            }
    
            // Update current instance stage & revoke info
            $this->workflow_instance_stage = $this->findStageByStepId($targetStepId);
            $this->revoked_stage = $resumeStepId;
    
            return [
                'status' => 'success',
                'action' => 'log_revocation',
                'message' => 'Revocation logic executed successfully.',
                'current_stage' => $this->workflow_instance_stage,
                'current_step_id' => $currentStep->workflow_instance_step_id_,
                'target_step' => $currentStep->workflow_instance_id_ . "-" . $targetStepId,
                'workflow_step_id' => $currentStep->workflow_step_id_,
            ];
    
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Exception during revocation: ' . $e->getMessage(),
                'action' => 'none'
            ];
        }
    }
    



    public function getCurrentStep()
    {
        $current  = $this->workflow_instance_steps_head_node;
        $stepCounter = 1;

        while ($current !== null && $stepCounter < $this->workflow_instance_stage) {
            $current = $current->workflow_instance_next_step;
            $stepCounter++;
        }

        return $current;
    }

    public function findStageByStepId($stepId) 
    {
        $current = $this->workflow_instance_steps_head_node;
        $stage = 1; 
        while ($current !== null) {
            if ($current->workflow_step_id_ === $stepId || $current->workflow_instance_step_id_ === $stepId) {
                return $stage; 
            }
            $current = $current->workflow_instance_next_step; 
            $stage++;
        }
        return null; 
    }


}
?>
