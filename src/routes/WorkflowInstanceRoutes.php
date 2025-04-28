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

        return false;
    }
}
