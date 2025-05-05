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

                Response::json([
                    'status' => 'success',
                    'workflow' => $workflow
                ]);
            } catch (\Exception $e) {
                Response::error($e->getMessage(), 400);
            }

            return true; // route matched
        }

        //TODo Workflow Basic Api
        //-- Get Workflow By ID
        //-- Update Workflow By ID (If Needed)

        if ($uri === '/api/workflow/getAll' && $method === 'GET') {
            AuthMiddleware::verify();

            try{
                $registryService = new WorkflowRegistryService();
                $workflowService = new WorkflowService($registryService);
                $controller = new WorkflowController($workflowService);
                $workflows = $controller->getAllWorkflows();

                Response::json([
                    'status' => 'success',
                    'workflows' => $workflows
                ]);
            } catch (\Exception $e) {
                Response::error($e->getMessage(), 400);
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

                Response::json([
                    'status' => 'success',
                    'workflows' => $workflows
                ]);
            } catch (\Exception $e) {
                Response::error($e->getMessage(), 400);
            }
        }

        return false; // no match
    }
}
