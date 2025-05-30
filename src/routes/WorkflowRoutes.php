<?php

namespace WorkflowManager\Routes;

use WorkflowManager\ApiControllers\WorkflowController;
use WorkflowManager\Services\WorkflowRegistryService;
use WorkflowManager\Services\PermissionService;
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
            $user = AuthMiddleware::user($input);

            $permissionService = new PermissionService();
            if (!$permissionService->userHasPermission($user['employee_id'], 'create_workflow')) {
                Response::error('Unauthorized: Permission denied', 403);
                return true;
            }

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

            return true; 
        }

        if ($uri === '/api/workflow/getAll' && $method === 'GET') {
            
            AuthMiddleware::verify();
            $permissionService = new PermissionService();
            if (!$permissionService->userHasPermission($user['employee_id'], 'create_workflow')) {
                Response::error('Unauthorized: Permission denied', 403);
                return true;
            }

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
            $user = AuthMiddleware::user($input);
            $permissionService = new PermissionService();
            if (!$permissionService->userHasPermission($user['employee_id'], 'create_workflow')) {
                Response::error('Unauthorized: Permission denied', 403);
                return true;
            }
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

        //Nitesh added : api to get all workflows by parent_id
        if($uri === '/api/workflow/getAllByParentId' && $method === 'GET'){
           
            AuthMiddleware::verify();

            $input = Request::input();
            $user = AuthMiddleware::user($input);
            $permissionService = new PermissionService();
            if (!$permissionService->userHasPermission($user['employee_id'], 'create_workflow')) {
                Response::error('Unauthorized: Permission denied', 403);
                return true;
            }
            try{
                $registryService = new WorkflowRegistryService();
                $workflowService = new WorkflowService($registryService);
                $controller = new WorkflowController($workflowService);
                $workflows = $controller->getWorkflowsByParentId($input);

                Response::json([
                    'status' => 'success',
                    'result' => $workflows
                ]);
            } catch (\Exception $e) {
                Response::error($e->getMessage(), 400);
            }
        }

        //Nitesh added : api to get latest workflow by parent_id
        if($uri === '/api/workflow/getLatestByParentId' && $method === 'GET'){
            AuthMiddleware::verify();

            $input = Request::input();
            $user = AuthMiddleware::user($input);
            $permissionService = new PermissionService();
            if (!$permissionService->userHasPermission($user['employee_id'], 'create_workflow')) {
                Response::error('Unauthorized: Permission denied', 403);
                return true;
            }
            try{
                $registryService = new WorkflowRegistryService();
                $workflowService = new WorkflowService($registryService);
                $controller = new WorkflowController($workflowService);
                $workflows = $controller->getLatestWorkflowByParentId($input);

                Response::json([
                    'status' => 'success',
                    'result' => $workflows
                ]);
            } catch (\Exception $e) {
                Response::error($e->getMessage(), 400);
            }
        }

        return false; // no match
    }
}
