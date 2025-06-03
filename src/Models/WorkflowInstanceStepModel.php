<?php

namespace WorkflowManager\Models;

require_once __DIR__ . '/../Config/db_conn.php';

use RedBeanPHP\R;


class WorkflowInstanceStepModel 
{

    private $tableName = 'workflowinstancestep';

    
    public function insert($WorkflowInstanceStep) 
    {

        $bean = R::dispense($this->tableName); 

        $bean->workflow_id_ = $WorkflowInstanceStep->workflow_id_;
        $bean->workflow_instance_id_ = $WorkflowInstanceStep->workflow_instance_id_;
        $bean->workflow_step_id_ = $WorkflowInstanceStep->workflow_step_id_;
        $bean->workflow_instance_step_id_ = $WorkflowInstanceStep->workflow_instance_step_id_ ?? uniqid('step_', true);   
        $bean->workflow_instance_step_position = $WorkflowInstanceStep->workflow_instance_step_position;
        $bean->is_user_id_dynamic = $WorkflowInstanceStep->is_user_id_dynamic;
        $bean->workflow_instance_step_user_role = $WorkflowInstanceStep->workflow_instance_step_user_role ?? null;
        $bean->workflow_instance_step_user_id = is_null($WorkflowInstanceStep->workflow_instance_step_user_id) ? $WorkflowInstanceStep->workflow_instance_step_user_role : $WorkflowInstanceStep->workflow_instance_step_user_id;
        $bean->workflow_instance_step_description = $WorkflowInstanceStep->Instance_step_description ?? null;
        
        // var_dump("saving bean :", $bean);
        $id =  R::store($bean);

        if (!$id) {
            throw new \RuntimeException("Failed to insert workflow instance step for workflow_instance_id: {$WorkflowInstanceStep->workflow_instance_id_}");
        }
        return $id;
    }
    

    public function get($id) 
    {
        $WorkflowInstanceStep = R::findOne($this->tableName, 'WorkflowInstance_step_id = ?', [$id]);
        return $WorkflowInstanceStep;
    }

    public function getWorkflowInstanceSteps($Workflow_instance_id_)
    {
        $instanceSteps = R::findAll('workflowinstancestep','workflow_instance_id_ = ?',[$Workflow_instance_id_],'ORDER BY step_position');
        if (!$instanceSteps) {
            throw new \RuntimeException("No steps found for workflow_instance_id: $Workflow_instance_id_");
        }
        return $instanceSteps;
    }

    

    public function getAllStepsByInstanceId($Workflow_instance_id_)
    {
        $instanceSteps = R::findAll('workflowinstancestep','workflow_instance_id_ = ?',[$Workflow_instance_id_],'ORDER BY step_position');
        return $instanceSteps;
    }

    public function update($WorkflowInstanceStep) 
    {
        $bean = R::findOne($this->tableName, 'WorkflowInstance_step_id = ?', [$WorkflowInstanceStep->WorkflowInstance_step_id]);
        if(is_null($bean)) {
            return;
        }

        $isModified = false;

        if ($bean->WorkflowInstance_id_ != $WorkflowInstanceStep->WorkflowInstance_id_) {
            $bean->WorkflowInstance_id_ = $WorkflowInstanceStep->WorkflowInstance_id_;
            $isModified = true;
        }
        if ($bean->WorkflowInstance_step_no != $WorkflowInstanceStep->WorkflowInstance_step_no) {
            $bean->WorkflowInstance_step_no = $WorkflowInstanceStep->WorkflowInstance_step_no;
            $isModified = true;
        }
        if ($bean->created_at != $WorkflowInstanceStep->created_at) {
            $bean->created_at = $WorkflowInstanceStep->created_at;
            $isModified = true;
        }
        if ($bean->updated_at != $WorkflowInstanceStep->updated_at) {
            $bean->updated_at = $WorkflowInstanceStep->updated_at;
            $isModified = true;
        }

        if ($isModified) {
            return R::store($bean);
        } else {
            return false; 
        }
    }

    public function updateStepUserId($workflow_instance_step_id_, $workflow_instance_step_user_id) 
    {

        $bean = R::findOne('workflowinstancestep', 'workflow_instance_step_id_ = ?', [$workflow_instance_step_id_]);

        if (is_null($bean)) {
            throw new \RuntimeException("Step not found for ID: $workflow_instance_step_id_");
        }

        $isModified = false;


        if (  $bean->workflow_instance_step_user_id != $workflow_instance_step_user_id) {

            $bean->workflow_instance_step_user_id = $workflow_instance_step_user_id;
            $isModified = true;
        }
        if ($isModified) {
            $id = R::store($bean);
            if (!$id) {
                throw new \RuntimeException("Failed to update dynamic status for step ID: $workflow_instance_step_id_");
            }
            return $id;
        } else {
            return false; 
        }

    }

    public function updateStepDynamicStatus($workflow_instance_step_id_, $status) 
    {
        $bean = R::findOne('workflowinstancestep', 'workflow_instance_step_id_ = ?', [$workflow_instance_step_id_]);

        if (is_null($bean)) {
            throw new \RuntimeException("Step not found for ID: $workflow_instance_step_id_");
        }

        $isModified = false;

        if ($bean->is_user_id_dynamic != $status) {
            $bean->is_user_id_dynamic = $status; // Update the dynamic status
            $isModified = true;
        }

        if ($isModified) {
            $id = R::store($bean);
            if (!$id) {
                throw new \RuntimeException("Failed to update dynamic status for step ID: $workflow_instance_step_id_");
            }
            return $id;
        } else {
            return false; // No changes were made
        }
    }

    public function delete($WorkflowInstanceStep) {
        $bean = R::findOne($this->tableName, 'WorkflowInstance_step_id = ?', [$WorkflowInstanceStep->WorkflowInstance_step_id]);
        if ($bean->id) {
            R::trash($bean);
            return true;
        }
        return false;
    }

    public function getAll() {
        $WorkflowInstanceSteps = R::findAll($this->tableName);
        return $WorkflowInstanceSteps;
    }

    public function getAllByWorkflowInstanceId($WorkflowInstance_id_) {
        $WorkflowInstanceSteps = R::findAll($this->tableName, 'WorkflowInstance_id_ = ?', [$WorkflowInstance_id_]);
        return $WorkflowInstanceSteps;
    }

    // Nitesh added: to get all the workflowInstanceStep created for a pending at particular user role
    public function getAllWorkflowStepsByIdRole($workflow_id, $role, $employee_id) {
        $WorkflowInstanceSteps = R::findAll($this->tableName, 'workflow_id_ = ? AND workflow_instance_step_user_role = ? AND workflow_instance_step_user_id = ? ', [$workflow_id, $role, $employee_id]);
        return $WorkflowInstanceSteps;
    }

    public function getAllWorkflowStepsByRole($workflow_id, $role) {
        $WorkflowInstanceSteps = R::findAll($this->tableName, 'workflow_id_ = ? AND workflow_instance_step_user_role = ? ', [$workflow_id, $role]);
        return $WorkflowInstanceSteps;
    }



    // Nitesh added: to delete all the workflowInstanceStep created for a workflowInstance
    public function deleteAllbyInstanceId($WorkflowInstance_id_) {
        // Start the transaction
        R::begin();
    
        try {
            $workflowInstanceSteps = R::findAll($this->tableName, 'workflow_instance_id_ = ?', [$WorkflowInstance_id_]);
            if ($workflowInstanceSteps) {

                foreach ($workflowInstanceSteps as $bean) {
                    R::trash($bean); 
                }
    
                // Commit the transaction if all deletions are successful
                R::commit();
                return true; 
            } else {
                // If no steps are found, return false
                R::rollback(); // Rollback the transaction
                return false; 
            }
        } catch (Exception $e) {

            R::rollback();
            return false; 
        }
    }
    
    
}

?>
