<?php

namespace WorkflowManager\ApiControllers;

use WorkflowManager\Services\WorkflowInstanceService;
// use WorkflowManager\Services\WorkflowService;
// use WorkflowManager\Services\WorkflowRegistryService;
use WorkflowManager\Validators\WorkflowInstanceDataValidator; 
use WorkflowManager\Validators\WorkflowInstanceActionDataValidator; 
use Exception;

class WorkflowInstanceController
{
    protected $workflowInstanceService;
    // Nitesh added $workflowService;
    protected $workflowService;
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

    /**
     * Nitesh added : Get workflowInstance created by user for a active workflow using parent workflow id
     */
    public function getWorkflowInstanceByUserAndWorkflowId(array $data)
    {
        return $this->workflowInstanceService->getWorkflowInstanceByUserAndWorkflowId($data);
    }

    /**
     * Nitesh added : Get workflowInstance Creation History by user and parent workflow id
     */
    public function getWorkflowInstanceHistory(array $data)
    {
        return $this->workflowInstanceService->getWorkflowInstanceHistory($data);
    }

    /**
     * Nitesh added : Get workflowInstance by approver role and user id
     */
    public function getWorkflowInstanceByApproverIdRole(array $data)
    {

        //! Please make validator class for validation of this API
        // Don't Check in controller it self make new class
        $workflow_id =  $data['workflow_id'] ?? null;

        $role = isset($data['user']) && is_array($data['user']) 
                    ? ($data['user']['role'] ?? null) 
                    : null;

        $employee_id = isset($data['user']) && is_array($data['user']) 
                    ? ($data['user']['employee_id'] ?? null) 
                    : null;
        
        return $this->workflowInstanceService->getWorkflowInstanceByApproverIdRole($workflow_id, $role, $employee_id);

    }

     /**
     * HS added : Get workflowInstance by approver role
     */
    public function getWorkflowInstanceByApproverRole(array $data)
    {

        //! Please make validator class for validation of this API
        // Don't Check in controller it self make new class
        $workflow_id =  $data['workflow_id'] ?? null;

        $role = isset($data['user']) && is_array($data['user']) 
                    ? ($data['user']['role'] ?? null) 
                    : null;

        // Not need for employee id in this case
        // But for consistency we are keeping it here
        $employee_id = isset($data['user']) && is_array($data['user']) 
                    ? ($data['user']['employee_id'] ?? null) 
                    : null;
        
        return $this->workflowInstanceService->getWorkflowInstanceByApproverRole($workflow_id, $role, $employee_id);

    }

}
