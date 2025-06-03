<?php

namespace WorkflowManager\Services;

use WorkflowManager\Models\WorkflowVersionModel;
use WorkflowManager\Entities\Workflow;

class WorkflowRegistryService
{
    protected $model;

    public function __construct()
    {
        $this->model = new WorkflowVersionModel();
    }

    public function registerWorkflow(Workflow $workflow)
    {
        if ($workflow->parent_workflow_id_ === $workflow->workflow_id_) {
            $workflow->is_latest = true;
            return $this->model->insert($workflow);
        }
        return $this->createNewVersion($workflow);
    }

    public function createNewVersion(Workflow $workflow)
    {
        $latestVersion = $this->model->getLatestVersion($workflow->parent_workflow_id_);
        if (!$latestVersion || !isset($latestVersion['version_of_workflow'])) {
            $workflow->is_latest = true;
            return $this->model->insert($workflow);
        }

       
        $workflow->workflow_version = $latestVersion['version_of_workflow'] + 1;
        $workflow->workflow_version_id_ = $workflow->parent_workflow_id_ . "_v" . $workflow->workflow_version;
        $workflow->is_latest = true;

        $this->model->insert($workflow);
    
        $this->model->updateLatestVersion($workflow->parent_workflow_id_, $workflow->workflow_version_id_);
    }

    public function getLatestWorkflowVersion(string $parentWorkflowId)
    {
        return $this->model->getLatestVersion($parentWorkflowId);
    }


    public function updateWorkflowActiveState(Workflow $workflow): bool
    {
        $result = $this->model->update($workflow);
        return $result['success'];
    }

    public function getLatestWorkflowVersionId(string $workflowId): ?string
    {
        return $this->model->getLatestWorkflowVersionId($workflowId);
    }
}
