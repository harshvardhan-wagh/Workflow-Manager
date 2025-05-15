<?php

namespace WorkflowManager\Routes;

use WorkflowManager\ApiControllers\WorkflowInstanceController;
use WorkflowManager\Services\WorkflowService;
use WorkflowManager\Services\WorkflowRegistryService;
use WorkflowManager\Services\WorkflowInstanceService;
use WorkflowManager\Helpers\Request;
use WorkflowManager\Helpers\Response;

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
                Response::json(['status' => 'success', 'workflow' => $workflow]);
            } catch (\Exception $e) {
                Response::error($e->getMessage(), 400);
            }

            return true;
        }

        if ($uri === '/api/workflow-instance/action' && $method === 'POST') {
            try {
                $result = $controller->workflowInstanceProcessAction($input);
                Response::json(['status' => 'success', 'result' => $result]);
            } catch (\Exception $e) {
                Response::error($e->getMessage(), 400);
            }

            return true;
        }

        /**
         * Nitesh added : Get all workflowInstance
         */
        if ($uri === '/api/workflow-instance/getAll' && $method === 'GET') {
            // AuthMiddleware::verify();
            
            try {
                $workflowInstances = $controller->getAllWorkflowInstances();
                Response::json(['status' => 'success', 'result' => $workflowInstances]);
            } catch (\Exception $e) {
                Response::error($e->getMessage(), 400);
            }

            // return true;
        }

        /**
         * Nitesh added : Get workflowInstance by id
         */
        if ($uri === '/api/workflow-instance/getByInstanceId' && $method === 'GET') {
            // AuthMiddleware::verify();
            $input = Request::input();
            try {
                $workflowInstances = $controller->getWorkflowInstanceById($input);
                Response::json(['status' => 'success', 'result' => $workflowInstances]);
            } catch (\Exception $e) {
                Response::error($e->getMessage(), 400);
            }

            // return true;
        }

        /**
         * Nitesh added : Get workflowInstance by user id
         */
        if ($uri === '/api/workflow-instance/getAllByUserId' && $method === 'GET') {
            // AuthMiddleware::verify();
            $input = Request::input();
            try {
                $workflowInstances = $controller->getWorkflowInstanceByUserId($input);
                Response::json(['status' => 'success', 'result' => $workflowInstances]);
            } catch (\Exception $e) {
                Response::error($e->getMessage(), 400);
            }

            // return true;
        }

        /**
         * Nitesh added : Get workflowInstance by approver role
         */
        if ($uri === '/api/workflow-instance/getAllByApproverRole' && $method === 'GET') {
            // AuthMiddleware::verify();
            $input = Request::input();
            try {
                $workflowInstances = $controller->getWorkflowInstanceByApproverRole($input);
                Response::json(['status' => 'success', 'result' => $workflowInstances]);
            } catch (\Exception $e) {
                Response::error($e->getMessage(), 400);
            }

            // return true;
        }

        return false;
    }
}
