<?php

namespace WorkflowManager\ApiControllers;

use WorkflowManager\Services\WorkflowService;
use WorkflowManager\Validators\WorkflowDataValidator;
use Exception;

class WorkflowController
{
    protected $workflowService;

    public function __construct(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    public function createWorkflow(array $data)
    {
        WorkflowDataValidator::validate($data);
        $clean = WorkflowDataValidator::normalize($data);

        return $this->workflowService->createWorkflow($clean);
    }
    public function getAllWorkflows()
    {
        return $this->workflowService->getAllWorkflows();
    }

    public function getWorkflow(array $data)
    {
        $workflowId = $data['workflow_id_'] ?? null;
        return $this->workflowService->getWorkflow($workflowId);
    }

     //Nitesh: Added to get all workflows by parent ID
     public function getWorkflowsByParentId(array $data)
     {
         $parentWorkflowId = $data['parent_workflow_id'] ?? null;
         return $this->workflowService->getWorkflowsByParentId($parentWorkflowId);
     }

     //Nitesh: Added to get latest workflow by parent ID
     public function getLatestWorkflowByParentId(array $data)
     {
         $parentWorkflowId = $data['parent_workflow_id'] ?? null;
         return $this->workflowService->getLatestWorkflowByParentId($parentWorkflowId);
     }
}
