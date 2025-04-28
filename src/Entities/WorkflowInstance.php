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

    public function acceptStep($nextStepEmployeeId = null)
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
                $nextStep = $currentStep->workflow_instance_next_step;
    
                // Handle dynamic assignment
                if ($nextStep->is_user_id_dynamic == "1") {
                    if (!$nextStepEmployeeId) {
                        return [
                            'status' => 'error',
                            'action' => 'none',
                            'message' => 'Next step requires dynamic user assignment but no user ID was provided.'
                        ];
                    }
    
                    return [
                        'status' => 'success',
                        'action' => 'assign_dynamic_user',
                        'message' => 'Dynamic user assignment required.',
                        'step_id' => $nextStep->workflow_instance_step_id_,
                        'user_id' => $nextStepEmployeeId
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
