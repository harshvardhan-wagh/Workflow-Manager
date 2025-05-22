<?php

namespace WorkflowManager\Models;

require_once __DIR__ . '/../Config/db_conn.php';

use RedBeanPHP\R;


class WorkflowInstanceModel{

    private $tableName = 'workflowinstance'; //table name should be always in small cases.
    public function __construct() {
     
    }

    public function insert($WorkflowInstance) 
    {
        if (empty($WorkflowInstance->workflow_instance_id_)) {
            $WorkflowInstance->workflow_instance_id_ = 'WI' . uniqid(); // Generate unique ID
        }

        $wfInstanceId = (int) preg_replace('/\D/', '', $WorkflowInstance->workflow_instance_id_);
        $bean = R::findOne($this->tableName, 'id = ?', [$wfInstanceId]);

        if (!$bean) {
            $bean = R::dispense($this->tableName); 
        }
        $workflow_id_ =  $WorkflowInstance->workflow->workflow_id_;

        $bean->workflow_instance_id_ = $WorkflowInstance->workflow_instance_id_;  
        $bean->setAttr('workflow_id_', $workflow_id_);
        $bean->workflow_instance_name = $WorkflowInstance->workflow_instance_name; 
        $bean->workflow_instance_description = $WorkflowInstance->workflow_instance_description;  
        $bean->workflow_instance_stage = $WorkflowInstance->workflow_instance_stage; 
        $bean->created_by_user_id = $WorkflowInstance->created_by_user_id;

  
        return R::store($bean);
    }


    public function getById($workflow_instance_id_) {
        return R::findOne($this->tableName,'workflow_instance_id_ = ?' ,[$workflow_instance_id_]);
    }

    public function getByRoleAndPosition($workflow_instance_id_, $stepPosition) {
        return R::findOne($this->tableName,'workflow_instance_id_ = ? AND workflow_instance_stage = ?' ,[$workflow_instance_id_, $stepPosition]);
    }

    public function getAll() {
        return R::findAll($this->tableName);
    }
    
    public function getAllByUserAndEmpId($userId,$workflowId){
       
        return R::findAll($this->tableName, 'created_by_user_id = ? AND workflow_id_ = ?', [$userId,$workflowId]);
    }

    
    public function update($WorkflowInstance) {
        $bean = R::findOne($this->tableName, 'workflow_instance_id_ = ?', [$WorkflowInstance['WorkflowInstance_id']]);
        if ($bean->id) {
            $bean->WorkflowInstance_name = $WorkflowInstance['WorkflowInstance_name'];
            $bean->WorkflowInstance_description = $WorkflowInstance['WorkflowInstance_description'];
            return R::store($bean);
        }
        return false;
    }

    public function delete($WorkflowInstance_id) {
        $bean = R::findOne($this->tableName, 'workflow_instance_id_ = ?', [$WorkflowInstance_id]);
        if ($bean->id) {
            R::trash($bean);
            return true;
        }
        return false;
    }

    public function createBlankWorkflowInstance($workflow_id_){
        
        $bean = R::dispense($this->tableName);
        $bean->workflow_instance_id_ =  substr(uniqid('wfi_', true), 0, 15);   //a unique num
        $bean->workflow_instance_name = '';
        $bean->workflow_instance_description = '';
        $bean->workflow_id_ = $workflow_id_;
        $id = R::store($bean);
        return $id;
    }

    public function saveCurrentStage($workflow_instance_id_, $workflow_instance_stage) {
        $bean = R::findOne($this->tableName, 'workflow_instance_id_ = ?', [$workflow_instance_id_]);
        if ($bean) {
            $bean->workflow_instance_stage = $workflow_instance_stage;
            return R::store($bean);
        }
        return false;
    }


    public function getAllByRole($workflow_id,$userStepPosition){
      
        return R::findAll($this->tableName, 'workflow_id_ = ? AND workflow_instance_stage = ?', [$workflow_id,$userStepPosition]);
        
    }

    /**
     * Nitesh added : Get workflowInstance by user id
     */
    public function getAllByUserId($employeeId){
       
        return R::findAll($this->tableName, 'created_by_user_id = ? ', [$employeeId]);
    }

    /**
     * Nitesh added : Get workflowInstance by user id
     */
    public function getAllByUserAndWorkflowId($employeeId, $workflowId){
       
        return R::findAll($this->tableName, 'created_by_user_id = ?  AND workflow_id_ = ?', [$employeeId,$workflowId]);
    }
}
