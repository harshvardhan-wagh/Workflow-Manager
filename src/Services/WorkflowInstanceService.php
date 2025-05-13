<?php

namespace WorkflowManager\Services;

use WorkflowManager\Entities\WorkflowInstance;
use WorkflowManager\Entities\WorkflowInstanceStep;
use WorkflowManager\Entities\RevokeCondition;
use WorkflowManager\Services\WorkflowService;
use WorkflowManager\Models\WorkflowInstanceModel;
use WorkflowManager\Models\WorkflowInstanceStepModel;
use WorkflowManager\Models\StateManagerModel;
use WorkflowManager\Models\RevokeConditionModel;
use WorkflowManager\Models\RevocationLogModel;
use WorkflowManager\Models\ActionLogModel;


class WorkflowInstanceService
{
    protected $workflowService;
    private $workflowInstanceModel;
    private $workflowInstanceStepModel;
    private $stateManagerModel;
    private $revokeConditionModel;
    private $revocationLogModel;
    private $actionLogModel;
  

    public function __construct(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
        $this->workflowInstanceModel = new WorkflowInstanceModel(); 
        $this->workflowInstanceStepModel = new WorkflowInstanceStepModel();
        $this->stateManagerModel = new StateManagerModel(); 
        $this->revokeConditionModel = new RevokeConditionModel();
        $this->revocationLogModel = new RevocationLogModel();
        $this->actionLogModel = new ActionLogModel();
    }


    //?====================================== Workflow Instance Creation ====================================
   
    public function createWorkflowInstance(array $data)
    {

        // Getting Latest Workflow Version
        $latestWorkflowId =  $this->workflowService->getLatestWorkflowVersionId($data['workflow_id']);

        // Creating Workflow Latest Version Obj
        $workflow = $this->workflowService->createWorkflowObjectFromDB($latestWorkflowId);
       
        // Workflow Instance Creation Final
        $workflowInstance = $this->BuildWorkflowInstance($workflow, $data);
       
        // var_dump("workflow Instance : ",$workflowInstance);
        // Saving Workflow Instance
        $saved = $this->saveWorkflowInstance($workflowInstance);

        if (!$saved) {
            return ['status' => 'error', 'message' => 'Failed to save workflow Instance.'];
        }

        return [
            'status' => 'success',
            'workflow_instance_id' => $workflowInstance->workflow_instance_id_,
            'workflow_instance_name' => $workflowInstance->workflow_instance_name,
            'Workflow_id' => $workflow->workflow_id_,
        ];
        
    }

    public function saveWorkflowInstance(WorkflowInstance $workflowInstance): bool
    {
        try{

            $result = $this->workflowInstanceModel->insert($workflowInstance);

            $current = $workflowInstance->workflow_instance_steps_head_node;

            while($current !== null) {
                // var_dump("Saving Workflow Instance Step: ", $current->workflow_instance_step_id_);
                $this->workflowInstanceStepModel->insert($current);
                $current = $current->workflow_instance_next_step;
            }

            return true;

        } catch (\Exception $e) {
            error_log("Failed to save workflow: " . $e->getMessage());
            return false;
        }
        
    }

    public function BuildWorkflowInstance($workflow, $data): workflowInstance
     {

        // Creating Workflow Instance ID
        $workflowInstanceId = "Wfi_"  . $this->generateWorkflowInstanceId($workflow->workflow_id_);

        $dataBean = [
            'workflow_instance_id_' => $workflowInstanceId,
            'workflow_instance_name' => $workflow->workflow_name,
            'created_by_user_id' => $data['user']['employee_id'],
        ];
     
        $workflowInstance = $this->createWorkflowInstanceEntity($workflow ,$dataBean);

        // Making list stage that requires the EmployeeID
        $requireUserIdList = $this->requireUserIdFind($workflow, $data);

        $this->addStepToWorkflowInstanceEntity($workflow , $workflowInstance ,$requireUserIdList, null ,false);

        $this->saveWorkflowInstanceState($workflowInstance);
        
        return $workflowInstance;
    }


    private function createWorkflowInstanceEntity($workflow, $source): WorkflowInstance
    {
        return new WorkflowInstance(
            $workflow,
            $source['workflow_instance_id_'],
            $source['workflow_instance_name'],
            $source['created_by_user_id'],
        );
    }

    private function addStepToWorkflowInstanceEntity(
        $workflow,
        $workflowInstance,
        $requireUserIdList = null, 
        $requireUserIdListFromDB = null,
        bool $isFromDatabase = false
    ) {
        $currentStep = $workflow->workflow_head_node;
        $lastInstanceStep = null;
        $i = 0;

    
        while ($currentStep !== null) {

            if (!$isFromDatabase && $currentStep->requires_user_id == "1") {
                $instance_step_user_id = $this->findInstanceStepUserId($currentStep, $requireUserIdList);
            }else if ($isFromDatabase && $currentStep->requires_user_id == "1") {
                $instance_step_user_id = $requireUserIdListFromDB[$i]->workflow_instance_step_user_id ?? null;
            }else {
                $instance_step_user_id = null;
            }
    
            // Create the instance step
            $instanceStep = new WorkflowInstanceStep(
                $workflow->workflow_id_,
                $workflowInstance->workflow_instance_id_,
                $currentStep->step_id_,
                $workflowInstance->workflow_instance_id_ . '-' . $currentStep->step_id_,
                $currentStep->step_position,
                $currentStep->is_user_id_dynamic,
                $currentStep->step_user_role,
                $instance_step_user_id,
                $currentStep->step_description,
            );
    
            // Add revoke conditions
            foreach ($currentStep->revokeConditions as $condition) {
                $instanceStep->addRevokeCondition(
                    new RevokeCondition(
                        $condition->getTargetStepId(),
                        $condition->getResumeStepId()
                    )
                );
            }
    
            // Link into doubly-linked list
            if ($lastInstanceStep === null) {
                $workflowInstance->workflow_instance_steps_head_node = $instanceStep;
            } else {
                $lastInstanceStep->workflow_instance_next_step = $instanceStep;
                $instanceStep->workflow_instance_previous_step = $lastInstanceStep;
            }
    
            $lastInstanceStep = $instanceStep;
            $currentStep = $currentStep->step_next_step;
            $i++;
        }
    }
    


    public function findInstanceStepUserId($currentStep, $requireUserIdList) 
    {

        if ($currentStep->requires_user_id == "1") {
            $role = trim($currentStep->step_user_role);
            $stepKey = $currentStep->step_id_;
    
            if (isset($requireUserIdList[$stepKey]) && isset($requireUserIdList[$stepKey][$role])) {
                return $requireUserIdList[$stepKey][$role];
            }
        }
        return null;
    }
    

    public function generateWorkflowInstanceId($workflow_id): string
    {  
        return $this->workflowInstanceModel->createBlankWorkflowInstance($workflow_id);
    }

    public function requireUserIdFind($workflow, $data) 
    {
        $currentStep = $workflow->workflow_head_node;
        $roleUserIdMap = []; // Initialize an array to store role to user ID mappings
    
        while ($currentStep != null) {
            $roleUserIds = $this->assignUserId($currentStep, $data);
            if ($roleUserIds !== null) {
                // Store the mapping from role to user ID for this step
                $roleUserIdMap[$currentStep->step_id_] = $roleUserIds;
            }
            $currentStep = $currentStep->step_next_step; // Move to the next step
        }
        return $roleUserIdMap; 
    }
    
    public function assignUserId($step, $userData) {
        if ($step->requires_user_id == "1") {
            $roleKey = strtolower($step->step_user_role) ;
            if (isset($userData[$roleKey])) {
                // Check if the role key exists in the userData array              
                return [$step->step_user_role => $userData[$roleKey]]; // Return the role and user ID if role matches
            }
        }
        return null; 
    }

    public function saveWorkflowInstanceState(WorkflowInstance $workflowInstance): void
    {
        $currentStep = $workflowInstance->getCurrentStep();

        if ($currentStep) {
            $isHalted = $this->stateManagerModel->getCurrentHaltState($workflowInstance->workflow_instance_id_);

            $isRevoked = $workflowInstance->revoked_stage !== null;

            $isCompleted = $this->stateManagerModel->isCompleted($workflowInstance->workflow_instance_id_);


            $this->stateManagerModel->saveCurrentState(
                $workflowInstance->workflow_instance_id_,
                $currentStep->workflow_instance_step_id_, 
                $isHalted,
                $isRevoked,
                $isCompleted
            );
        }
    }

    public function saveWorkflowInstanceStage($workflow_instance_id, $workflow_instance_stage): void
    {
        $this->workflowInstanceModel->saveCurrentStage($workflow_instance_id, $workflow_instance_stage);
    }

    public function findRuleAppliedId($workflow_step_id): ?int
    {
        $revokeConditionId = $this->revokeConditionModel->getRevokeConditionIdByStepId($workflow_step_id);
        return $revokeConditionId ? $revokeConditionId: null;
    }
     
    //?=====================================================================================================
    
    //?====================================== Workflow Instance Retrial ====================================

    public function createWorkflowInstanceFromDB($workflow_instance_id): WorkflowInstance
    {
        // Fetching Workflow Instance from Database
        $workflowInstanceBean = $this->workflowInstanceModel->get($workflow_instance_id);

        $workflowInstanceStepBean = $this->workflowInstanceStepModel->getWorkflowInstanceSteps($workflow_instance_id);
        
         // Creating Workflow Latest Version Obj
         $workflow = $this->workflowService->createWorkflowObjectFromDB($workflowInstanceBean['workflow_id_']);

         // Creating Workflow Instance Obj
         $workflowInstance = $this->BuildWorkflowInstanceFromDB($workflowInstanceBean, $workflow, $workflowInstanceStepBean);
        
         //set current stage
         $workflowInstance->workflow_instance_stage = $workflowInstanceBean['workflow_instance_stage'];

        
        return $workflowInstance;
        
    }

    public function BuildWorkflowInstanceFromDB($workflowInstanceBean, $workflow, $workflowInstanceStepBean): WorkflowInstance
    {
        $workflowInstance = $this->createWorkflowInstanceEntity($workflow, $workflowInstanceBean);

        $this->addStepToWorkflowInstanceEntity($workflow, $workflowInstance, null ,$workflowInstanceStepBean, true);

        $isRevoked = $this->stateManagerModel->isRevoked($workflowInstance->workflow_instance_id_);
        
        if ($isRevoked) {
            $workflowInstance->revoked_stage = $this->findResumeStep($workflowInstance->workflow_instance_id_);
        } 

        return $workflowInstance;

    }

    public function findResumeStep($workflow_instance_id)
    {
      //Fetch the rule id from revoke log for latest revoke action 
        $ruleId = $this->revocationLogModel->getInstanceLatestRuleId($workflow_instance_id);

      // use that rule id to find the resume step id from the revoke condition table
        $resumeStepId = $this->revokeConditionModel->getResumeStepIdById($ruleId);

        return $resumeStepId;

    }

    //?===========================================================================================================

    //?====================================== Workflow Instance Process Action ===================================
   
    public function workflowInstanceProcessAction(array $data, $context)
    {
        $workflowInstance = $this->createWorkflowInstanceFromDB($data['workflow_instance_id']);
        $action = $data['action'] ?? null;
        $nextStepEmployeeId = $data['nextStepEmployeeId'] ?? null;

        $currentStage = $workflowInstance->workflow_instance_stage;

        // Future: $this->verifyUserHasPermission($data['user'], $workflowInstance);

        if ($this->stateManagerModel->getCurrentHaltState($workflowInstance->workflow_instance_id_)) {
            return $this->processActionErrorResponse('Current workflow instance is halted.');
        }

        if ($this->stateManagerModel->isCompleted($workflowInstance->workflow_instance_id_)) {
            return $this->processActionErrorResponse('Current workflow instance is already completed.');
        }

        $response = match ($action) {
            'approve' => $this->processApproveAction($workflowInstance, $nextStepEmployeeId),
            'reject'  => $this->processRejectAction($workflowInstance),
            'revoke'  => $this->processRevokeAction($workflowInstance, $data['user']),
            default   => $this->processActionErrorResponse('Invalid action provided.'),
        };

        if ($response['status'] === 'error') {
            return $response;
        }

        $this->saveWorkflowInstanceState($workflowInstance);

        $this->saveWorkflowInstanceStage($workflowInstance->workflow_instance_id_, $workflowInstance->workflow_instance_stage);

         $this->actionLogModel->logAction(
            $workflowInstance->workflow_instance_id_,
            $currentStage,
            $data['user']['employee_id'],
            $data['action'],
            $details = null ,
            $context,
            $metadata = null,
            $data
        );

        return array_merge(
            $response,
            [
              'workflow_instance_id'   => $workflowInstance->workflow_instance_id_,
              'workflow_id'            => $workflowInstance->workflow->workflow_id_,
              'workflow_instance_name' => $workflowInstance->workflow_instance_name,
              'currentStage'           => $workflowInstance->workflow_instance_stage,
            ]
          );
    }

    private function processApproveAction($workflowInstance, $nextStepEmployeeId)
    {
        $response = $workflowInstance->acceptStep($nextStepEmployeeId);

        if ($response['status'] === 'error') return $response;

        return match ($response['action']) {
            'assign_dynamic_user' => $this->handleAssignDynamicUser($workflowInstance, $response),
            'moved_to_next_stage', 'resumed' => $this->processActionSuccessResponse('Stage moved/resumed successfully.'),
            'final_stage_approved' => $this->handleFinalApproval($workflowInstance),
            default => $this->processActionErrorResponse('Unknown result from acceptStep.'),
        };
    }

    private function handleAssignDynamicUser($workflowInstance, $response)
    {
        $this->workflowInstanceStepModel->updateStepUserId($response['step_id'], $response['user_id']);
        $workflowInstance->workflow_instance_stage++;
        return $this->processActionSuccessResponse('Dynamic user assigned and stage advanced.');
    }

    private function handleFinalApproval($workflowInstance)
    {
        $this->stateManagerModel->markAsCompleted($workflowInstance->workflow_instance_id_);
        return $this->processActionSuccessResponse('Workflow marked as completed.');
    }

    private function processRejectAction($workflowInstance)
    {
        $response = $workflowInstance->rejectStep();

        if ($response['status'] === 'error') return $response;

        if ($response['action'] === 'halt_instance') {
            $this->stateManagerModel->markAsRejected($workflowInstance->workflow_instance_id_);
        }

        return $this->processActionSuccessResponse('Workflow rejected and halted.');
    }

    private function processRevokeAction($workflowInstance, $user)
    {
        $response = $workflowInstance->revokeStep();

        if ($response['status'] === 'error') return $response;

        if ($response['action'] === 'log_revocation') {
            $ruleAppliedId = $this->findRuleAppliedId($response['workflow_step_id']);
            $this->revocationLogModel->logRevocation(
                $workflowInstance->workflow_instance_id_,
                $response['current_step_id'],
                $response['target_step'],
                $ruleAppliedId,
                $user['employee_id']
            );
        }

        return $this->processActionSuccessResponse('Workflow revocation logged.');
    }


    private function processActionSuccessResponse($message, $workflowInstance = null)
    {
        return [
            'status' => 'success',
            'message' => $message,
            'workflow_id' => $workflowInstance?->workflow->workflow_id_ ?? null,
            'workflow_instance_id' => $workflowInstance?->workflow_instance_id_ ?? null,
            'workflow_instance_name' => $workflowInstance?->workflow_instance_name ?? null,
        ];
    }

    private function processActionErrorResponse($message)
    {
        return [
            'status' => 'error',
            'message' => $message
        ];
    }

     //?==========================================================================================================

    /**
     *Nitesh added: Get all workflow instances from the database
     * @return array
     */
    public function getAllWorkflowInstances() 
    {
        try {
            $workflowInstances = $this->workflowInstanceModel->getAll();
            // Edge Case: No data found
            if (empty($workflowInstances)) {
                return [
                    'status' => 'success',
                    'workflow_instances' => [],
                    'message' => 'No workflow instances found.'
                ];
            }

            $workflowInstanceList = [];

            foreach ($workflowInstances as $instance) {
                $workflowInstanceList[] = [
                    'workflow_instance_id' => $instance['workflow_instance_id_'],
                    'workflow_instance_name' => $instance['workflow_instance_name'],
                    'workflow_id' => $instance['workflow_id_'],
                    'workflow_instance_description' => $instance['workflow_instance_description'],
                    'workflow_instance_stage' => $instance['workflow_instance_stage'],
                    'created_by_user_id' => $instance['created_by_user_id'],
                ];
            }

            return [
                'status' => 'success',
                'workflow_instances' => $workflowInstanceList
            ];

        } catch (\Exception $e) {
            error_log("WorkflowService getAllWorkflowInstances failed: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve workflow instances.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     *Nitesh added: Get a workflow instance by its workflow_instance_id.
     *
     * @param string $workflowInstanceId
     * @return array
     */
    public function getWorkflowInstanceById(string $workflowInstanceId)
    {
        // Edge case: empty ID input
        if (trim($workflowInstanceId) === '') {
            return [
                'status' => 'error',
                'message' => 'workflowInstanceId cannot be empty.'
            ];
        }

        try {
            $instance = $this->workflowInstanceModel->getById($workflowInstanceId);

            // Edge case: not found
            if (!$instance) {
                return [
                    'status' => 'error',
                    'message' => 'Workflow instance not found for ID: ' . $workflowInstanceId
                ];
            }

            $workflowInstance = [
                'workflow_instance_id' => $instance['workflow_instance_id_'],
                'workflow_instance_name' => $instance['workflow_instance_name'],
                'workflow_id' => $instance['workflow_id_'],
                'workflow_instance_description' => $instance['workflow_instance_description'],
                'workflow_instance_stage' => $instance['workflow_instance_stage'],
                'created_by_user_id' => $instance['created_by_user_id'],
            ];

            return [
                'status' => 'success',
                'workflow_instance' => $workflowInstance
            ];

        } catch (\Exception $e) {
            error_log("WorkflowService getWorkflowInstanceById failed: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Failed to fetch workflow instance.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     *Nitesh added: Get all workflow instances created by user from the database
     * @return array
     */
    public function getWorkflowInstanceByUserId(string $employeeId) 
    {
        try {
            $workflowInstances = $this->workflowInstanceModel->getAllByUserId($employeeId);
            // Edge Case: No data found
            if (empty($workflowInstances)) {
                return [
                    'status' => 'success',
                    'workflow_instances' => [],
                    'message' => 'No workflow instances found.'
                ];
            }

            $workflowInstanceList = [];

            foreach ($workflowInstances as $instance) {
                $workflowInstanceList[] = [
                    'workflow_instance_id' => $instance['workflow_instance_id_'],
                    'workflow_instance_name' => $instance['workflow_instance_name'],
                    'workflow_id' => $instance['workflow_id_'],
                    'workflow_instance_description' => $instance['workflow_instance_description'],
                    'workflow_instance_stage' => $instance['workflow_instance_stage'],
                    'created_by_user_id' => $instance['created_by_user_id'],
                ];
            }

            return [
                'status' => 'success',
                'workflow_instances' => $workflowInstanceList
            ];

        } catch (\Exception $e) {
            error_log("WorkflowService getAllWorkflowInstances failed: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve workflow instances.',
                'error' => $e->getMessage()
            ];
        }
    }

}
