<?php

namespace WorkflowManager\Routes;

use WorkflowManager\ApiControllers\WorkflowInstanceController;
use WorkflowManager\Services\WorkflowService;
use WorkflowManager\Services\WorkflowRegistryService;
use WorkflowManager\Services\WorkflowInstanceService;
use WorkflowManager\Helpers\Request;
use WorkflowManager\Helpers\Response;
use WorkflowManager\Middleware\AuthMiddleware;

class WorkflowInstanceRoutes
{
    public static function handle($uri, $method)
    {
        $input = Request::input();
        $registryService = new WorkflowRegistryService();
        $workflowService = new WorkflowService($registryService);
        $workflowInstanceService = new WorkflowInstanceService($workflowService);
        $controller = new WorkflowInstanceController($workflowInstanceService);

        if ($uri === '/api/workflow-instance/create' && $method === 'POST') {
            try {
                $workflow = $controller->createWorkflowInstance($input);
                if (!$workflow) {
                    Response::error('Workflow instance creation failed.', [], 400);
                }
                Response::success('Workflow instance created successfully.', $workflow, 201);
            } catch (\Exception $e) {
                Response::error('Failed to create workflow instance: ' . $e->getMessage(), [], 400);
            }

            return true;
        }

        if ($uri === '/api/workflow-instance/action' && $method === 'POST') {
            try {
                $result = $controller->workflowInstanceProcessAction($input);
                if (!$result) {
                    Response::error('Workflow instance action processing failed.', [], 400);
                }
                Response::success('Workflow instance action processed successfully.', $result, 200);
            } catch (\Exception $e) {
                Response::error('Failed to process workflow instance action: ' . $e->getMessage(), [], 400);
            }

            return true;
        }

        /**
         * Nitesh added : Get all workflowInstance
         */
        if ($uri === '/api/workflow-instance/getAll' && $method === 'GET') {
            AuthMiddleware::verify();
            
            try {
                $workflowInstances = $controller->getAllWorkflowInstances();
                if (!$workflowInstances) {
                    Response::error('No workflow instances found.', [], 404);
                }
                Response::success('Workflow instances retrieved successfully.', $workflowInstances, 200);
            } catch (\Exception $e) {
                Response::error('Failed to retrieve workflow instances.', [], 400); 
            }

            // return true;
        }

        /**
         * Nitesh added : Get workflowInstance by id
         */
        if ($uri === '/api/workflow-instance/getByInstanceId' && $method === 'GET') {
            AuthMiddleware::verify();
            $input = Request::input();
            try {
                $workflowInstances = $controller->getWorkflowInstanceById($input);
                if (!$workflowInstances) {
                    Response::error('Workflow instance not found.', [], 404);
                }
                Response::success('Workflow instance retrieved successfully.', $workflowInstances, 200);
            } catch (\Exception $e) {
                Response::error('Failed to retrieve workflow instance by ID.', [], 400);   
            }

            // return true;
        }

        /**
         * Nitesh added : Get workflowInstance by user id
         */
        if ($uri === '/api/workflow-instance/getAllByUserId' && $method === 'POST') {
            AuthMiddleware::verify();
            $input = Request::input();
            try {
                $workflowInstances = $controller->getWorkflowInstanceByUserId($input);
                if (!$workflowInstances) {
                    Response::error('No workflow instances found for the user.', [], 404);
                }
                Response::success('Workflow instances retrieved successfully.', $workflowInstances, 200);
            } catch (\Exception $e) {
                Response::error('Failed to retrieve workflow instances by user ID.', [], 400);
            }

            // return true;
        }

        /**
         * Nitesh added : Get workflowInstance by user and parent workflow id
         */
        if ($uri === '/api/workflow-instance/getAllByUserAndWorkflowId' && $method === 'POST') {
            AuthMiddleware::verify();
            $input = Request::input();
            try {
                $workflowInstances = $controller->getWorkflowInstanceByUserAndWorkflowId($input);
                if (!$workflowInstances) {
                    Response::error('No workflow instances found for the user and workflow ID.', [], 404);
                }
                Response::success('Workflow instances retrieved successfully.', $workflowInstances, 200);
            } catch (\Exception $e) {
                Response::error('Failed to retrieve workflow instances by user and workflow ID.', [], 400);
            }

            // return true;
        }

        /**
         * Nitesh added : Get workflowInstance History  by user and parent workflow id
         */
        if ($uri === '/api/workflow-instance/getInstanceHistory' && $method === 'POST') {
            AuthMiddleware::verify();
            $input = Request::input();
            try {
                $workflowInstances = $controller->getWorkflowInstanceHistory($input);
                if (!$workflowInstances) {
                    Response::error('No workflow instance history found for the user and workflow ID.', [], 404);
                }
                Response::success('Workflow instance history retrieved successfully.', $workflowInstances, 200);
            } catch (\Exception $e) {
                Response::error('Failed to retrieve workflow instance history.', [], 400);
            }

            // return true;
        }

        /**
         * Nitesh added : Get workflowInstance by approver role and user id
         */
        if ($uri === '/api/workflow-instance/getAllByApproverIdRole' && $method === 'POST') {
            AuthMiddleware::verify();
            $input = Request::input();

            try {
                $workflowInstances = $controller->getWorkflowInstanceByApproverIdRole($input);
                if (!$workflowInstances) {
                    Response::error('No workflow instances found for the approver role and user ID.', [], 404);
                }
                Response::success('Workflow instances retrieved successfully.', $workflowInstances, 200);
            } catch (\Exception $e) {
                Response::error('Failed to retrieve workflow instances by approver role.', [], 400);
            }

            // return true;
        }

        /**
         * HS added : Get workflowInstance by approver role
         */
        if ($uri === '/api/workflow-instance/getAllByApproverRole' && $method === 'POST') {
            AuthMiddleware::verify();
            $input = Request::input();
            try {
                $workflowInstances = $controller->getWorkflowInstanceByApproverRole($input);
                if (!$workflowInstances) {
                    Response::error('No workflow instances found for the approver role.', [], 404);
                }
                Response::success('Workflow instances retrieved successfully.', $workflowInstances, 200);
            } catch (\Exception $e) {
                Response::error('Failed to retrieve workflow instances by approver role.', [], 400);
            }

            // return true;
        }

        /**
         * Nitesh added : Get history of approved workflowInstance by approver role
         */
        if ($uri === '/api/workflow-instance/getApprovedHistoryByRole' && $method === 'POST') {
            AuthMiddleware::verify();
            $input = Request::input();
            try {
                $workflowInstances = $controller->getApprovedHistoryByRole($input);
                if (!$workflowInstances) {
                    Response::error('No workflow instances found for the approver role.', [], 404);
                }
                Response::success('Workflow instances retrieved successfully.', $workflowInstances, 200);
            } catch (\Exception $e) {
                Response::error('Failed to retrieve workflow instances by approver role.', [], 400);
            }

            // return true;
        }
        return false;
    }
}
