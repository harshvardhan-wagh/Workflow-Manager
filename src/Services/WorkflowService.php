<?php

namespace WorkflowManager\Services;

use WorkflowManager\Entities\Workflow;
use WorkflowManager\Entities\RevokeCondition;

use WorkflowManager\Services\WorkflowRegistryService;

use WorkflowManager\Models\WorkflowModel;
use WorkflowManager\Models\WorkflowStepModel;
use WorkflowManager\Models\RevokeConditionModel;
use WorkflowManager\Models\ActionLogModel;


class WorkflowService
{
    protected $registry;
    private $workflowModel;
    private $workflowStepModel;
    private $revokeConditionModel;

    public function __construct(WorkflowRegistryService $registry)
    {
        $this->registry = $registry;
        $this->workflowModel = new WorkflowModel();
        $this->workflowStepModel = new workflowStepModel();
        $this->revokeConditionModel = new RevokeConditionModel();
    }

    /**
     * Get  workflow by ID from the database.
     */

    public function getWorkflow(string $workflow_id) 
    {
        $workflowBean = $this->workflowModel->getWorkflow($workflow_id);
        $workflow = [
            'workflow_id' => $workflowBean['workflow_id_'],
            'workflowName' => $workflowBean['workflow_name'],
            'step_count' => $workflowBean['workflow_step_len'],
            'created_by_user_id' => $workflowBean['created_by_user_id'],
        ];

        return $workflow;
    }

    /**
     * Get all workflows from the database.
     */
    public function getAllWorkflows() 
    {
        $workflows = $this->workflowModel->getAll();
        $workflowList = [];

        foreach ($workflows as $workflow) {
            $workflowList[] = [
                'workflow_id' => $workflow['workflow_id_'],
                'workflowName' => $workflow['workflow_name'],
                'step_count' => $workflow['workflow_step_len'],
                'created_by_user_id' => $workflow['created_by_user_id'],
            ];
        }

        return $workflowList;
    }

    /**
     * Create and save a new workflow with steps.
     */
    public function createWorkflow(array $data)
    {
        // Step 1: Convert array data to a Workflow Entity object
        $workflow = $this->buildWorkflowEntityFromArray($data);

        // Step 2: Versioning logic
        $registered = $this->initializeVersioning($workflow);
        if (!$registered) {
            return ['status' => 'error', 'message' => 'Workflow versioning failed.'];
        }

        // Step 3: Save to DB
        $saved = $this->saveWorkflow($workflow);
        if (!$saved) {
            return ['status' => 'error', 'message' => 'Failed to save workflow.'];
        }

        return [
            'status' => 'success',
            'workflow_id' => $workflow->workflow_id_,
            'version_id' => $workflow->workflow_version_id_,
        ];

    }

    public function buildWorkflowEntityFromArray(array $data): Workflow
    {
        $workflowId = 'Wf_' . $this->generateWorkflowId();
    
        $workflow = $this->createWorkflowEntity($data, $workflowId);
        
        $this->addStepsToWorkflowEntity($workflow, $data['workflow_steps']);
       
        return $workflow;
    }
    
    private function createWorkflowEntity(array $data, $workflow_id): Workflow
    {

        return new Workflow(
            $data['workflowName'],
            $workflow_id,
            $data['parentWorkflowId'] ?? '',
            $data['workflowDescription'] ?? '',
            $data['user']['employee_id'] ?? null,
        );
    }
    
    private function addStepsToWorkflowEntity(Workflow $workflow, array $steps): void
    {
        $stepIdPrefix = $workflow->workflow_id_ . "-step-";
    
        foreach ($steps as $stepData) {
            $stepId = $stepIdPrefix . $stepData['position'];
    
            $step = $workflow->addStep(
                $stepId,
                $stepData['step_user_role'],
                $stepData['requires_user_id'] == "true",
                $stepData['is_user_id_dynamic'] == "true",
                $stepData['stepDescription'] ?? ''
            );
    
            $stepPosition = $stepData['position'];
  
            if (!empty($stepData['targetStepPosition']) && !empty($stepData['resumeStepPosition'])) {
                $targetStepId = $stepIdPrefix . $stepData['targetStepPosition'];
                $resumeStepId = $stepIdPrefix . $stepData['resumeStepPosition'];
                $step->addRevokeCondition(new RevokeCondition($targetStepId, $resumeStepId));
            }

        }
    }

    public function createWorkflowObjectFromDB($workflow_id)
    {
        $workflowBean = $this->workflowModel->getWorkflow($workflow_id);
    
        $stepsBean = $this->workflowStepModel->getAllWorkflowSteps($workflow_id);

        $workflow = $this->reconstructWorkflowFromBean($workflowBean, $stepsBean);
 

        return $workflow;
    }
    

    public function reconstructWorkflowFromBean($workflowBean, $stepsBean): Workflow
    {
        
        // Converting Bean Data to Array or acceptable format
        $workflowData = $this->workflowBeanToArray($workflowBean);

        $stepsData = $this->stepsBeanToArray($stepsBean);

        $stepsData = $this->injectRevokeConditionsIntoSteps($stepsData, $stepsBean);

        $workflow = $this->createWorkflowEntity($workflowData, $workflowBean['workflow_id_']);

        $this->addStepsToWorkflowEntity($workflow, $stepsData);
        
        // var_dump($workflow);

        return $workflow;
    }

    private function injectRevokeConditionsIntoSteps(array $stepsData, array $stepsBean): array
    {
        $revokeConditionsMap = $this->getRevokeConditionsForWorkflow($stepsBean);
    
        foreach ($stepsData as &$step) {
            $stepPos = (int) $step['position'];
    
            if (!empty($revokeConditionsMap[$stepPos])) {
               
                $firstCondition = $revokeConditionsMap[$stepPos][0];
    
                $step['targetStepPosition'] = $firstCondition['target_step_position'];
                $step['resumeStepPosition'] = $firstCondition['resume_step_position'];
            }
        }
    
        return $stepsData;
    }
    


    private function getRevokeConditionsForWorkflow($steps): array
    {
        $map = [];

        foreach ($steps as $stepBean) {
            $revokeConditionsRaw = $this->revokeConditionModel->getRevokeConditionsForStep($stepBean->step_id_);

            foreach ($revokeConditionsRaw as $condition) {
                // Parse the current step ID to extract its position
                $stepId = $condition['step_id_']; // e.g., "Wf_275-step-3"
                $parts = explode('-step-', $stepId);
                $stepPos = isset($parts[1]) ? (int)$parts[1] : 0;

                // Parse the target and resume step positions from step IDs
                $targetParts = explode('-step-', $condition['target_step_id']);
                $resumeParts = explode('-step-', $condition['resume_step_id']);

                $targetStepPos = isset($targetParts[1]) ? (int)$targetParts[1] : 0;
                $resumeStepPos = isset($resumeParts[1]) ? (int)$resumeParts[1] : 0;

                $map[$stepPos][] = [
                    'target_step_position' => $targetStepPos,
                    'resume_step_position' => $resumeStepPos
                ];
            }
        }
        return $map;
    }




    function workflowBeanToArray($workflowBean): array {
        return [
            'workflow_id' => $workflowBean['workflow_id_'],
            'workflowName' =>$workflowBean['workflow_name'],
            'workflowDescription' => $workflowBean['workflow_description'],
            'step_count' => $workflowBean['workflow_step_len'],
            'created_by_user_id' => $workflowBean['created_by_user_id'],
        ];
    }

    function stepsBeanToArray($stepsBeans): array {
        $stepsArray = [];
    
        foreach ($stepsBeans as $stepBean) {
            $stepsArray[] = [
                'stepDescription' => $stepBean['step_description'],
                'step_user_role' => $stepBean['step_user_role'],
                'position' => (int) $stepBean['step_position'],
                'requires_user_id' => (bool) $stepBean['requires_user_id'],
                'is_user_id_dynamic' => (bool) $stepBean['is_user_id_dynamic'],
            
            ];
        }
    
        return $stepsArray;
    }
    
    

    public function initializeVersioning(Workflow $workflow): bool
    {
        if (empty($workflow->parent_workflow_id_)) {
            $workflow->workflow_version = 1;
            $workflow->parent_workflow_id_ = $workflow->workflow_id_;
            $workflow->workflow_version_id_ = $workflow->parent_workflow_id_ . "_v" . $workflow->workflow_version;
        } else {
            $latest = $this->registry->getLatestWorkflowVersion($workflow->parent_workflow_id_);
            if (!$latest) return false;

            
            $workflow->workflow_version = $latest['version_of_workflow'] + 1;
            $workflow->workflow_version_id_ = $workflow->parent_workflow_id_ . "_v" . $workflow->workflow_version;
        }

        return $this->registry->registerWorkflow($workflow)['success'] ?? false;
    }

    public function addStepToWorkflow(Workflow $workflow, array $stepData): Step
    {
        return $workflow->addStep(
            $stepData['step_id_'],
            $stepData['step_user_role'],
            $stepData['requires_user_id'],
            $stepData['is_user_id_dynamic'],
            $stepData['stepName'] ?? '',
            $stepData['stepDescription'] ?? ''
        );
    }

    public function generateWorkflowId(): string
    {  
        return $this->workflowModel->createBlankWorkflow();
    }

    public function saveWorkflow(Workflow $workflow): bool
    {
        try {
            $this->workflowModel->insert($workflow, $workflow->workflow_id_);
            $step = $workflow->workflow_head_node;
            while ($step !== null) {
                $this->saveStep($step);
                $this->saveRevokeCondition($step);
                $step = $step->step_next_step;
            }
            return true;

        } catch (\Exception $e) {
            error_log("Failed to save workflow: " . $e->getMessage());
            return false;
        }
    }


    public function saveStep($step) 
    { 
      return  $this->workflowStepModel->insert($step);
    }


    public function saveRevokeCondition($step) 
    {
        $workflowId = $step->workflow_id_ ?? null;
        $stepId = $step->step_id_ ?? null;
        $revokeConditions = $step->revokeConditions ?? [];
    
        if (empty($workflowId) || empty($stepId)) {
            error_log("Missing workflow_id or step_id in step object");
            return;
        }
    
        if (!is_array($revokeConditions)) {
            error_log("RevokeConditions must be an array. Found: " . gettype($revokeConditions));
            return;
        }
    
        foreach ($revokeConditions as $index => $revokeCondition) {
            if ($revokeCondition instanceof RevokeCondition) {
                try {
                    $this->revokeConditionModel->insert($workflowId, $stepId, $revokeCondition);
                } catch (\Exception $e) {
                    error_log("Failed to insert RevokeCondition at index $index for step $stepId: " . $e->getMessage());
                }
            } else {
                error_log("Invalid RevokeCondition object at index $index for step $stepId");
            }
        }
    }

    public function getLatestWorkflowVersion(string $parentId): ?Workflow
    {
        return $this->registry->getLatestWorkflowVersion($parentId);
    }

    public function getLatestWorkflowVersionId(string $parentId): ?string
    {
        return $this->registry->getLatestWorkflowVersionId($parentId);
    }


   
}
