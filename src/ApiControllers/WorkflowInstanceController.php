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

    /**
     * Nitesh added : Get all workflowInstance
     */
    public function getAllWorkflowInstances()
    {
        // $result = WorkflowInstanceActionDataValidator::validate($data);
        // if (!$result['status']) {
        //     throw new Exception($result['message']);
        // }

        // $data = WorkflowInstanceActionDataValidator::normalize($data);
        
        return $this->workflowInstanceService->getAllWorkflowInstances();
    }

    /**
     * Nitesh added : Get workflowInstance by id
     */
    public function getWorkflowInstanceById(array $data)
    {
        $workflowInstanceId = $data['workflow_instance_id'] ?? null;

        return $this->workflowInstanceService->getWorkflowInstanceById($workflowInstanceId);
    }

    /**
     * Nitesh added : Get workflowInstance by user id
     */
    public function getWorkflowInstanceByUserId(array $data)
    {
        $employeeId = isset($data['user']) && is_array($data['user']) 
                    ? ($data['user']['employee_id'] ?? null) 
                    : null;

        return $this->workflowInstanceService->getWorkflowInstanceByUserId($employeeId);
    }

}
