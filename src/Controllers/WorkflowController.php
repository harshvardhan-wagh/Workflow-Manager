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
        $result = WorkflowDataValidator::validate($data);
        if (!$result['status']) {
            throw new Exception($result['message']);
        }

        $data = WorkflowDataValidator::normalize($data);
      
        return $this->workflowService->createWorkflow($data);
    }

}
