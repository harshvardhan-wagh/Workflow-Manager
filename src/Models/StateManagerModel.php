<?php

namespace WorkflowManager\Models;

require_once __DIR__ . '/../Config/db_conn.php';

use RedBeanPHP\R;

class StateManagerModel {

    private $tableName = 'workflowstates';

    public function __construct() {
        // Clean constructor
    }

    /**
     * Save or update the current state of a workflow instance.
     */
    public function saveCurrentState($workflowInstanceID, $currentState, $isHalted = false, $isRevoke = false, $isComplete = false) 
    {

        $state = R::findOne($this->tableName, 'workflow_instance_id = ?', [$workflowInstanceID]);

        if (!$state) {
            $state = R::dispense($this->tableName);
            $state->created_at = R::isoDateTime();
        }

        $state->workflow_instance_id = $workflowInstanceID;
        $state->current_state = $currentState;

        if ($isHalted === true) {
            $state->is_halted = true;
        } else {
            $state->is_halted = false;
        }

        if($isComplete == true) {
            $state->is_complete = true;
        } else {
            $state->is_complete = false;
        }
        
        if($isRevoke == true) {
            $state->is_revoke = true;
        } else {
            $state->is_revoke = false;
        }
        // $state->is_revoke = true;
        $state->updated_at = R::isoDateTime();

        $id =  R::store($state);

        if (!$id) {
            throw new \RuntimeException("Failed to store workflow state for instance ID: $workflowInstanceID");
        }

        return $id;
    
    }

    /**
     * Get the current state's label.
     */
    public function getCurrentState($workflowInstanceID) {
        $state = R::findOne($this->tableName, 'workflow_instance_id = ?', [$workflowInstanceID]);
        return $state ? $state->current_state : null;
    }

    /**
     * Check if the workflow is halted.
     */
    public function getCurrentHaltState($workflowInstanceID) {
        $state = R::findOne($this->tableName, 'workflow_instance_id = ?', [$workflowInstanceID]);
        return $state ? (bool) $state->is_halted : false;
    }

    /**
     * Mark the workflow instance as completed.
     */
    public function markAsCompleted($workflowInstanceID) {
        $state = R::findOne($this->tableName, 'workflow_instance_id = ?', [$workflowInstanceID]);
        
       if (!$state) {
            throw new \RuntimeException("No state found for workflow instance ID: $workflowInstanceID");
        }
            
        $state->is_complete = true;
        $state->updated_at = R::isoDateTime();
        
        $id = R::store($state);

        if (!$id) {
            throw new \RuntimeException("Failed to mark workflow instance as completed for ID: $workflowInstanceID");
        }

        return $id;
            
 
    }

    /**
     * Mark the workflow instance as rejected or halted.
     */

    public function markAsRejected($workflowInstanceID) {
        $state = R::findOne($this->tableName, 'workflow_instance_id = ?', [$workflowInstanceID]);
        
        if( !$state) {
            throw new \RuntimeException("No state found for workflow instance ID: $workflowInstanceID");
        }
            
        $state->updated_at = R::isoDateTime();
        $state->is_halted = true;
        $id =  R::store($state);
        if (!$id) {
            throw new \RuntimeException("Failed to mark workflow instance as rejected for ID: $workflowInstanceID");
        }
        return $id;
        
    }

    

    /**
     * Mark the workflow instance as revoked.
     */
    public function markAsRevoked($workflowInstanceID) {
        $state = R::findOne($this->tableName, 'workflow_instance_id = ?', [$workflowInstanceID]);
        
        if ($state) {
            var_dump("Marking as revoked: " . $workflowInstanceID);
           
            $state->is_revoke = true;
            $state->updated_at = R::isoDateTime();
            return R::store($state);
        }
        return null;
    }

    /**
     * Clear the revoked flag when resuming workflow.
     */
    public function clearRevokedState($workflowInstanceID) {
        $state = R::findOne($this->tableName, 'workflow_instance_id = ?', [$workflowInstanceID]);
        if ($state) {
            $state->is_revoke = false;
            $state->updated_at = R::isoDateTime();
            return R::store($state);
        }
        return null;
    }

    /**
     * Check if the workflow is currently revoked.
     */
    public function isRevoked($workflowInstanceID) {
        $state = R::findOne($this->tableName, 'workflow_instance_id = ?', [$workflowInstanceID]);
        return $state ? (bool) $state->is_revoke : false;
    }

    /**
     * Check if the workflow is completed.
     */
    public function isCompleted($workflowInstanceID) {
        $state = R::findOne($this->tableName, 'workflow_instance_id = ?', [$workflowInstanceID]);
        return $state ? (bool) $state->is_complete : false;
    }

}
