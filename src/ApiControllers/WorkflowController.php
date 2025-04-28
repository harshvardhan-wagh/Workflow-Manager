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
        $result = WorkflowDataValidator::validate($data);
        if (!$result['status']) {
            throw new Exception($result['message']);
        }

        $data = WorkflowDataValidator::normalize($data);
        return $this->workflowService->createWorkflow($data);
    }
}
