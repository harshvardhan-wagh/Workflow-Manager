<?php

namespace WorkflowManager\ApiControllers;

use WorkflowManager\Services\WorkflowInstanceService;
use WorkflowManager\Services\WorkflowService;
use WorkflowManager\Services\WorkflowRegistryService;
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
        $parent_workflow_id = $data['parent_workflow_id'] ?? null;
        $registryService = new WorkflowRegistryService();
        $workflowService = new workflowService($registryService);

        $active_workflow =  $workflowService->getLatestWorkflowByParentId($parent_workflow_id);
        $workflow_id =  $active_workflow['workflow']['workflow_id'] ?? null;

        $employeeId = isset($data['user']) && is_array($data['user']) 
                    ? ($data['user']['employee_id'] ?? null) 
                    : null;

        return $this->workflowInstanceService->getWorkflowInstanceByUserAndWorkflowId($employeeId, $workflow_id);
    }

    /**
     * Nitesh added : Get workflowInstance Creation History by user and parent workflow id
     */
    public function getWorkflowInstanceHistory(array $data)
    {
        $parent_workflow_id = $data['parent_workflow_id'] ?? null;
        $registryService = new WorkflowRegistryService();
        $workflowService = new workflowService($registryService);

        // Fetch workflows from the service
        $response = $workflowService->getWorkflowsByParentId($parent_workflow_id);
      
        // Check if we have workflows
        if ($response['status'] === 'success' && isset($response['workflows'])) {
            $workflows = $response['workflows'];
        } else {
            return []; 
        }
       
        foreach ($workflows as $workflow) {
        
            $workflow_id =  $workflow['workflow_id'] ?? null;
            
            $employeeId = isset($data['user']) && is_array($data['user']) 
                    ? ($data['user']['employee_id'] ?? null) 
                    : null;
            
            return $this->workflowInstanceService->getWorkflowInstanceByUserAndWorkflowId($employeeId, $workflow_id);
        }

    }

    /**
     * Nitesh added : Get workflowInstance by approver role
     */
    public function getWorkflowInstanceByApproverRole(array $data)
    {
        $workflow_id =  $data['workflow_id'] ?? null;

        $role = isset($data['user']) && is_array($data['user']) 
                    ? ($data['user']['role'] ?? null) 
                    : null;

        $employee_id = isset($data['user']) && is_array($data['user']) 
                    ? ($data['user']['employee_id'] ?? null) 
                    : null;
        
        return $this->workflowInstanceService->getWorkflowInstanceByApproverRole($workflow_id, $role, $employee_id);

    }

}
