<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use WorkflowManager\Services\WorkflowService;
use WorkflowManager\Services\WorkflowRegistryService;
use WorkflowManager\Services\WorkflowInstanceService;
use WorkflowManager\Controllers\WorkflowInstanceController;

// Create the required services first
$registryService = new WorkflowRegistryService();
$workflowService = new WorkflowService($registryService);
$workflowInstanceService = new WorkflowInstanceService($workflowService);


$json = file_get_contents(__DIR__ . '/workflow_instance_data.json');
$data = json_decode($json, true);

try {
   // Inject the service into the controller
    $controller = new WorkflowInstanceController($workflowInstanceService);
    $workflow = $controller->createWorkflowInstance($data);

    echo "✅ Workflow Instance created successfully:\n";
    print_r($workflow);
} catch (Exception $e) {
    echo "❌ Error creating workflow: " . $e->getMessage() . "\n";
}
