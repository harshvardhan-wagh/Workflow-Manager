<?php
namespace WorkflowManager\Models;

require_once __DIR__ . '/../Config/db_conn.php';

use RedBeanPHP\R;

class WorkflowVersionModel
{
    /**
     * Insert a new workflow version
     * @param Workflow $workflow
     * @return int|false ID of the created record or false on failure
     */
    public function insert($workflow)
    {
        try {
            $bean = R::dispense('workflowversions');
            $bean->workflow_id = $workflow->workflow_id_;
            $bean->parent_workflow_id = $workflow->parent_workflow_id_;
            $bean->workflow_version_id = $workflow->workflow_version_id_;
            $bean->version_of_workflow = $workflow->workflow_version;
            $bean->workflow_name = $workflow->workflow_name;
            $bean->workflow_description = $workflow->workflow_description;
            $bean->created_by_user_id = $workflow->created_by_user_id;

            if (is_numeric($workflow->version_timestamp)) {
                $bean->version_timestamp = date('Y-m-d H:i:s', $workflow->version_timestamp);
            } else {
                $bean->version_timestamp = $workflow->version_timestamp;
            }

            $bean->is_latest = (bool)$workflow->is_latest;
            $bean->is_active = isset($workflow->is_active) ? (bool)$workflow->is_active : true;

            $id = R::store($bean);

            if (!$id) {
                error_log("❌ RedBean store returned falsy value");
                return ['success' => false, 'message' => 'DB insert failed'];
            }

            return ['success' => true, 'id' => $id];

        } catch (Exception $e) {
            error_log("❌ WorkflowVersionModel insert failed: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }


        /**
     * Get the latest version of a workflow
     * @param string $parent_workflow_id
     * @return array|null Latest version data or null if not found
     */
    public function getLatestVersion($parent_workflow_id)
    {
        // var_dump("getLatestVersion for this ", $parent_workflow_id);
        try {
            return R::findOne('workflowversions', 
                'parent_workflow_id = ? AND is_latest = 1 AND is_active = 1', 
                [$parent_workflow_id]
            );
        } catch (Exception $e) {
            error_log("WorkflowVersionModel getLatestVersion failed: " . $e->getMessage());
            return null;
        }
    }

    public function getLatestWorkflowVersionId($parent_workflow_id)
    {
        try {
            $latestVersion =  R::findOne('workflowversions', 
                'parent_workflow_id = ? AND is_latest = 1 AND is_active = 1', 
                [$parent_workflow_id]
            );
     
            return $latestVersion['workflow_id'] ?? null;
        } catch (Exception $e) {
            error_log("WorkflowVersionModel getLatestVersion failed: " . $e->getMessage());
            return null;
        }
    }


    /**
     * Update an existing workflow version
     * @param Workflow $workflow
     * @return bool True on success, false on failure
     */
    public function update($workflow)
    {
        try {
            $bean = R::findOne('workflow_versions', 'workflow_version_id = ?', [$workflow->workflow_version_id_]);
            if (!$bean) return false;

            $bean->workflow_name = $workflow->workflow_name;
            $bean->workflow_description = $workflow->workflow_description;
            $bean->created_by_user_id = $workflow->createdByUserId;
            $bean->version_timestamp = $workflow->version_timestamp;
            $bean->is_latest = $workflow->is_latest;
            $bean->is_active = $workflow->is_active ?? true;
            return R::store($bean) !== false;
        } catch (Exception $e) {
            error_log("WorkflowVersionModel update failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a workflow version
     * @param string $workflow_version_id
     * @return bool True on success, false on failure
     */
    public function delete($workflow_version_id)
    {
        try {
            $bean = R::findOne('workflow_versions', 'workflow_version_id = ?', [$workflow_version_id]);
            if (!$bean) return false;

            R::trash($bean);
            return true;
        } catch (Exception $e) {
            error_log("WorkflowVersionModel delete failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get a workflow version by ID
     * @param string $workflow_version_id
     * @return array|null Workflow version data or null if not found
     */
    public function get($workflow_version_id)
    {
        try {
            return R::findOne('workflow_versions', 'workflow_version_id = ?', [$workflow_version_id]);
        } catch (Exception $e) {
            error_log("WorkflowVersionModel get failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all workflow versions
     * @return array All workflow versions
     */
    public function getAll()
    {
        try {
            return R::findAll('workflow_versions');
        } catch (Exception $e) {
            error_log("WorkflowVersionModel getAll failed: " . $e->getMessage());
            return [];
        }
    }

 

    /**
     * Update which version is marked as latest
     * @param string $parent_workflow_id
     * @param string $new_version_id
     * @return bool True on success, false on failure
     */
    public function updateLatestVersion($parent_workflow_id,$new_version_id)
    {

        try {
            R::begin();
            
            // Mark all versions of this workflow as not latest
            R::exec('UPDATE workflowversions SET is_latest = 0 
                    WHERE parent_workflow_id = ?', [$parent_workflow_id]);
            
            // Mark the new version as latest
            R::exec('UPDATE workflowversions SET is_latest = 1 
                    WHERE workflow_version_id = ?', [$new_version_id]);
            
            R::commit();
            return true;
        } catch (Exception $e) {
            R::rollback();
            error_log("WorkflowVersionModel updateLatestVersion failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all versions of a parent workflow
     * @param string $parent_workflow_id
     * @return array
     */
    public function getVersionsByParent($parent_workflow_id)
    {
        try {
            return R::find('workflow_versions', 
                'parent_workflow_id = ? ORDER BY workflow_version DESC', 
                [$parent_workflow_id]
            );
        } catch (Exception $e) {
            error_log("WorkflowVersionModel getVersionsByParent failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     *Nitesh added: Get all workflow versions by parent_workflow_id 
     * @param string $parent_workflow_id
     * @return array
     */
    public function getWorkflowsByParentId($parent_workflow_id)
    {
        try {
            return R::find('workflowversions', 
                'parent_workflow_id = ?', 
                [$parent_workflow_id]
            );
        } catch (\Exception $e) {
            error_log("WorkflowVersionModel getWorkflowsByParentId failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Nitesh added: Get all workflow versions by parent_workflow_id 
     * @param string $parent_workflow_id
     * @return array
     */
    public function getLatestWorkflowByParentId($parent_workflow_id)
    {
        try {
            return R::findOne('workflowversions',
                'parent_workflow_id = ? AND is_latest = 1', 
                [$parent_workflow_id]
            );
        } catch (\Exception $e) {
            error_log("WorkflowVersionModel getLatestWorkflowByParentId failed: " . $e->getMessage());
            return null;
        }
    }
    
}