<?php

namespace WorkflowManager\Controllers;

use WorkflowManager\Services\WorkflowService;
use WorkflowManager\Validators\WorkflowDataValidator; 

class WorkflowController
{
    protected $workflowService;

    public function __construct(WorkflowService $workflowService)
    {
        // Directly assign the injected service
        $this->workflowService = $workflowService;
    }

    public function createWorkflow(array $data)
    {
       WorkflowDataValidator::validate($data);

        $data = WorkflowDataValidator::normalize($data);
      
        return $this->workflowService->createWorkflow($data);
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

    //Nitesh: Added to get all workflows by parent ID
    public function getLatestWorkflowByParentId(array $data)
    {
        $parentWorkflowId = $data['parent_workflow_id'] ?? null;
        return $this->workflowService->getLatestWorkflowByParentId($parentWorkflowId);
    }
}
