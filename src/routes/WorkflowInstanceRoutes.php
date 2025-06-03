<?php

namespace WorkflowManager\Routes;

use WorkflowManager\ApiControllers\WorkflowInstanceController;
use WorkflowManager\Services\WorkflowService;
use WorkflowManager\Services\WorkflowRegistryService;
use WorkflowManager\Services\WorkflowInstanceService;
use WorkflowManager\Helpers\Request;
use WorkflowManager\Helpers\Response;
use WorkflowManager\Helpers\Logger;
use WorkflowManager\Middleware\AuthMiddleware;

class WorkflowInstanceRoutes
{
    public static function handle($uri, $method)
    {
        $input = Request::input();
        $user  = AuthMiddleware::user($input);
        $registryService = new WorkflowRegistryService();
        $workflowService = new WorkflowService($registryService);
        $workflowInstanceService = new WorkflowInstanceService($workflowService);
        $controller = new WorkflowInstanceController($workflowInstanceService);

        if ($uri === '/api/workflow-instance/create' && $method === 'POST') {

            AuthMiddleware::verify();
            try {
                $workflow = $controller->createWorkflowInstance($input);
                Response::success(['workflow' => $workflow]);
            } catch (\Throwable $e) {
                // catch anything unexpected
                Logger::error('Unhandled exception in create Workflow Instance', [
                    'user'      => $user,
                    'input'     => $input,
                    'exception' => [
                        'message' => $e->getMessage(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                        'trace'   => $e->getTraceAsString(),
                    ],
                ]);
                Response::error('An internal error occurred. Please try again later.', 500);
            }

            return true;
        }


        if ($uri === '/api/workflow-instance/action' && $method === 'POST') {
            try {
                $result = $controller->workflowInstanceProcessAction($input);
                Response::success(['result' => $result]);
            } catch (\Throwable $e) {
                // catch anything unexpected
                Logger::error('Unhandled exception in create Workflow Instance', [
                    'user'      => $user,
                    'input'     => $input,
                    'exception' => [
                        'message' => $e->getMessage(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                        'trace'   => $e->getTraceAsString(),
                    ],
                ]);
                Response::error('An internal error occurred. Please try again later.', 500);
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
        if ($uri === '/api/workflow-instance/getAllByUserId' && $method === 'POST') {
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
         * Nitesh added : Get workflowInstance by user and parent workflow id
         */
        if ($uri === '/api/workflow-instance/getAllByUserAndWorkflowId' && $method === 'POST') {
            // AuthMiddleware::verify();
            $input = Request::input();
            try {
                $workflowInstances = $controller->getWorkflowInstanceByUserAndWorkflowId($input);
                Response::json(['status' => 'success', 'result' => $workflowInstances]);
            } catch (\Exception $e) {
                Response::error($e->getMessage(), 400);
            }

            // return true;
        }

        /**
         * Nitesh added : Get workflowInstance History  by user and parent workflow id
         */
        if ($uri === '/api/workflow-instance/getInstanceHistory' && $method === 'POST') {
            // AuthMiddleware::verify();
            $input = Request::input();
            try {
                $workflowInstances = $controller->getWorkflowInstanceHistory($input);
                Response::json(['status' => 'success', 'result' => $workflowInstances]);
            } catch (\Exception $e) {
                Response::error($e->getMessage(), 400);
            }

            // return true;
        }

        /**
         * Nitesh added : Get workflowInstance by approver role and user id
         */
        if ($uri === '/api/workflow-instance/getAllByApproverIdRole' && $method === 'POST') {
            // AuthMiddleware::verify();
            $input = Request::input();

            try {
                $workflowInstances = $controller->getWorkflowInstanceByApproverIdRole($input);
                Response::json(['status' => 'success', 'result' => $workflowInstances]);
            } catch (\Exception $e) {
                Response::error($e->getMessage(), 400);
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
                Response::json(['status' => 'success', 'result' => $workflowInstances]);
            } catch (\Exception $e) {
                Response::error($e->getMessage(), 400);
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
