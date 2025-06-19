<?php

namespace WorkflowManager\Routes;

use WorkflowManager\ApiControllers\WorkflowController;
use WorkflowManager\Services\WorkflowRegistryService;
use WorkflowManager\Services\PermissionService;
use WorkflowManager\Services\WorkflowService;
use WorkflowManager\Helpers\Request;
use WorkflowManager\Helpers\Response;
use WorkflowManager\Helpers\Logger;
use WorkflowManager\Middleware\AuthMiddleware;

class WorkflowRoutes
{
    public static function handle($uri, $method)
    {

        if ($uri === '/api/workflow/create' && $method === 'POST') {
            AuthMiddleware::verify();
            $input = Request::input();
            $user  = AuthMiddleware::user($input);
        
            // permission check
            $permSvc = new PermissionService();
            if (! $permSvc->userHasPermission($user['employee_id'], 'create_workflow')) {
                Logger::error('Permission denied', ['user'=>$user, 'input'=>$input]);
                Response::error('You do not have rights to create workflows', 403);
            }
        
            try {
                $registryService  = new WorkflowRegistryService();
                $workflowService  = new WorkflowService($registryService);
                $controller       = new WorkflowController($workflowService);
                $result           = $controller->createWorkflow($input);
        
                Response::success([
                    'workflow_id'  => $result['workflow_id'],
                    'version_id'   => $result['version_id'],
                ], 201);
        
            } catch (\Throwable $e) {
                // catch anything unexpected
                Logger::error('Unhandled exception in createWorkflow', [
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
        

        if ($uri === '/api/workflow/getAll' && $method === 'GET') {
            
            AuthMiddleware::verify();

            $input = Request::input();
            $user  = AuthMiddleware::user($input);
        
            // permission check
            $permSvc = new PermissionService();
            if (! $permSvc->userHasPermission($user['employee_id'], 'create_workflow')) {
                Logger::error('Permission denied', ['user'=>$user, 'input'=>$input]);
                Response::error('You do not have rights to create workflows', 403);
            }

            try{
                $registryService = new WorkflowRegistryService();
                $workflowService = new WorkflowService($registryService);
                $controller = new WorkflowController($workflowService);
                $workflows = $controller->getAllWorkflows();

                Response::success([
                    'workflows' => $workflows
                ], 201);
            }catch (\Throwable $e) {
                // catch anything unexpected
                Logger::error('Unhandled exception in createWorkflow', [
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

        if($uri === '/api/workflow/get' && $method === 'POST'){
            AuthMiddleware::verify();

            $input = Request::input();
            $user  = AuthMiddleware::user($input);
        
            // permission check
            $permSvc = new PermissionService();
            if (! $permSvc->userHasPermission($user['employee_id'], 'create_workflow')) {
                Logger::error('Permission denied', ['user'=>$user, 'input'=>$input]);
                Response::error('You do not have rights to create workflows', 403);
            }
            try{
                $registryService = new WorkflowRegistryService();
                $workflowService = new WorkflowService($registryService);
                $controller = new WorkflowController($workflowService);
                $workflows = $controller->getWorkflow($input);

                Response::success([
                    'workflows' => $workflows
                ], 201);


            } catch (\Throwable $e) {
                // catch anything unexpected
                Logger::error('Unhandled exception in createWorkflow', [
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
