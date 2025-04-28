<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use WorkflowManager\Services\WorkflowService;
use WorkflowManager\Services\WorkflowRegistryService;
use WorkflowManager\Controllers\WorkflowController;


// Create the required services first
$registryService = new WorkflowRegistryService();
$workflowService = new WorkflowService($registryService);


$json = file_get_contents(__DIR__ . '/workflow_data.json');
$data = json_decode($json, true);

try {
   // Inject the service into the controller
   $controller = new WorkflowController($workflowService);
    $workflow = $controller->createWorkflow($data);

    echo "✅ Workflow created successfully:\n";
    print_r($workflow);
} catch (Exception $e) {
    echo "❌ Error creating workflow: " . $e->getMessage() . "\n";
}
