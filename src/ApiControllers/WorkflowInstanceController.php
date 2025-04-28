<?php

namespace WorkflowManager\ApiControllers;

use WorkflowManager\Services\WorkflowInstanceService;
use WorkflowManager\Validators\WorkflowInstanceDataValidator; 
use WorkflowManager\Validators\WorkflowInstanceActionDataValidator; 
use Exception;

class WorkflowInstanceController
{
    protected $workflowInstanceService;

    public function __construct(workflowInstanceService $workflowInstanceService)
    {
        // Directly assign the injected service
        $this->workflowInstanceService = $workflowInstanceService;
    }

    public function createWorkflowInstance(array $data)
    {
        $result = WorkflowInstanceDataValidator::validate($data);
        if (!$result['status']) {
            throw new Exception($result['message']);
        }

        $data = WorkflowInstanceDataValidator::normalize($data);
      
        return $this->workflowInstanceService->createWorkflowInstance($data);
    }

    public function workflowInstanceProcessAction(array $data)
    {
        $result = WorkflowInstanceActionDataValidator::validate($data);
        if (!$result['status']) {
            throw new Exception($result['message']);
        }

        $data = WorkflowInstanceActionDataValidator::normalize($data);
        
        return $this->workflowInstanceService->workflowInstanceProcessAction($data, 'api');
    }

}
