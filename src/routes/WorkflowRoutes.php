<?php

namespace WorkflowManager\Routes;

use WorkflowManager\ApiControllers\WorkflowController;
use WorkflowManager\Services\WorkflowRegistryService;
use WorkflowManager\Services\WorkflowService;
use WorkflowManager\Helpers\Request;
use WorkflowManager\Helpers\Response;
use WorkflowManager\Middleware\AuthMiddleware;

class WorkflowRoutes
{
    public static function handle($uri, $method)
    {
        if ($uri === '/api/workflow/create' && $method === 'POST') {
            AuthMiddleware::verify(); 
            $input = Request::input();

            try {
                $registryService = new WorkflowRegistryService();
                $workflowService = new WorkflowService($registryService);
                $controller = new WorkflowController($workflowService);
                $workflow = $controller->createWorkflow($input);
                if (!$workflow) {
                    Response::error('Workflow creation failed.', [], 400);
                }
                Response::success('Workflow created successfully.', $workflow, 200);
            } catch (\Exception $e) {
                // Response::error($e->getMessage(), 400);
                Response::error('Failed to create workflow: ' . $e->getMessage(), [], 400);
            }

            return true; // route matched
        }

        //TODo Workflow Basic Api
        //-- Get Workflow By ID
        //-- Update Workflow By ID (If Needed)

        if ($uri === '/api/workflow/getAll' && $method === 'GET') {
            // AuthMiddleware::verify();

            try{
                $registryService = new WorkflowRegistryService();
                $workflowService = new WorkflowService($registryService);
                $controller = new WorkflowController($workflowService);
                $workflows = $controller->getAllWorkflows();
                if (!$workflows) {
                    Response::error('No workflows found.', [], 404);
                }
                Response::success('Workflows retrieved successfully.', $workflows, 200);
            } catch (\Exception $e) {
                // Response::error($e->getMessage(), 400);
                Response::error('Failed to retrieve workflows: ' . $e->getMessage(), [], 400);
            }
        }

        if($uri === '/api/workflow/get' && $method === 'GET'){
            AuthMiddleware::verify();

            $input = Request::input();
            try{
                $registryService = new WorkflowRegistryService();
                $workflowService = new WorkflowService($registryService);
                $controller = new WorkflowController($workflowService);
                $workflows = $controller->getWorkflow($input);

                if (!$workflows) {
                    Response::error('Workflow not found.', [], 404);
                }
                Response::success('Workflow retrieved successfully.', $workflows, 200);
            } catch (\Exception $e) {
                // Response::error($e->getMessage(), 400);
                Response::error('Failed to retrieve workflow: ' . $e->getMessage(), [], 400);
            }
        }

        //Nitesh added : api to get all workflows by parent_id
        if($uri === '/api/workflow/getAllByParentId' && $method === 'GET'){
            AuthMiddleware::verify();

            $input = Request::input();
            try{
                $registryService = new WorkflowRegistryService();
                $workflowService = new WorkflowService($registryService);
                $controller = new WorkflowController($workflowService);
                $workflows = $controller->getWorkflowsByParentId($input);
                if (!$workflows) {
                    Response::error('No workflows found for the given parent ID.', [], 404);
                }
                Response::success('Workflows by parent ID retrieved successfully.', $workflows, 200);
            } catch (\Exception $e) {
                // Response::error($e->getMessage(), 400);
                Response::error('Failed to retrieve workflows by parent ID: ' . $e->getMessage(), [], 400);
            }
        }

        //Nitesh added : api to get latest workflow by parent_id
        if($uri === '/api/workflow/getLatestByParentId' && $method === 'GET'){
            AuthMiddleware::verify();

            $input = Request::input();
            try{
                $registryService = new WorkflowRegistryService();
                $workflowService = new WorkflowService($registryService);
                $controller = new WorkflowController($workflowService);
                $workflows = $controller->getLatestWorkflowByParentId($input);

                if (!$workflows) {
                    Response::error('No latest workflow found for the given parent ID.', [], 404);
                }
                Response::success('Latest workflow by parent ID retrieved successfully.', $workflows, 200);
            } catch (\Exception $e) {
                // Response::error($e->getMessage(), 400);
                Response::error('Failed to retrieve latest workflow by parent ID: ' . $e->getMessage(), [], 400);
            }
        }

        return false; // no match
    }
}
