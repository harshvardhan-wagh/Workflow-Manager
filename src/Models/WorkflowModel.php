<?php
namespace WorkflowManager\Models;

require_once __DIR__ . '/../Config/db_conn.php';

use RedBeanPHP\R;

class WorkflowModel
{

   public function insert($workflow, $workflow_id_, $image)
{    
    // Extract numeric ID from workflow_id_
    $workflowIdNumber = (int) preg_replace('/\D/', '', $workflow->workflow_id_);
    
    $bean = R::findOne('workflow', 'id = ?', [$workflowIdNumber]);

    if (!$bean) {
        throw new \RuntimeException("No existing workflow found with ID: $workflowIdNumber");
    }

    $bean->workflow_id_ = $workflow->workflow_id_;
    $bean->workflow_name = $workflow->workflow_name;
    $bean->workflow_description = $workflow->workflow_description;
    $bean->workflow_step_len = $workflow->workflow_step_len;
    $bean->created_by_user_id = $workflow->created_by_user_id;
    $bean->image = $image;

    $id = R::store($bean);

    if (!$id) {
        throw new \RuntimeException("Failed to insert/update workflow for workflow_id: {$workflow->workflow_id_}");
    }

    return $id;
}

 

    public function update($workflow) {
        $bean = R::findOne('workflow', 'workflow_id_ = ?', [$workflow->workflow_id_]);
        
        
        if ($bean->id) {
            $isModified = false;
    
            if ($bean->workflow_name != $workflow->workflow_name) {
                $bean->workflow_name = $workflow->workflow_name;
                $isModified = true;
            }
            if ($bean->workflow_description != $workflow->workflow_description) {
                $bean->workflow_description = $workflow->workflow_description;
                $isModified = true;
            }
            if ($bean->workflow_step_len != $workflow->workflow_step_len) {
                $bean->workflow_step_len = $workflow->workflow_step_len;
                $isModified = true;
            }
            
    
            
            if ($isModified) {
                return R::store($bean);
            } else {
                return false; 
            }
        }
        return false; 
    }


    public function createBlankWorkflow()
    {
        $bean = R::dispense('workflow');
        $bean->workflow_id_ =  substr(uniqid('wf_', true), 0, 15);   //a unique num
        $bean->workflow_name = '';
        $bean->workflow_description = '';
        $bean->workflow_step_len = '0';    //initially set to 0
        $id = R::store($bean);

        if (!$id) {
            throw new WorkflowPersistenceException("Failed to create blank workflow record");
        }
        return $id;



    }
    

    public function delete($workflow_id_)
    {
        $bean = R::findone('workflow','workflow_id_ = ?',[$workflow_id_]);
        if ($bean->id) {
            R::trash($bean);
            return true;
        }
        return false;
    }

    public function getWorkflow($workflow_id_)
    {
        $workflow = R::findOne('workflow', 'workflow_id_ = ?', [$workflow_id_]);

        if (!$workflow) {
            throw new \RuntimeException("Workflow not found for ID: $workflow_id_");
        }

        return $workflow;
    }

    public function getAll()
    {
        return R::findAll('workflow');
    }

    public function getNextId()
    {
        $maxId = R::getCell('SELECT MAX(id) FROM workflow');
        if ($maxId === null) {
            return 1;
        }
        return $maxId + 1;
    }
}
?>
