<?php

namespace WorkflowManager\Validators;

class WorkflowDataValidator
{
    public static function validate(array $data): array
    {
        if (empty($data['workflowName']) || empty($data['workflow_steps'])) {
            return ['status' => false, 'message' => 'Missing required workflow name or steps'];
        }
        if (empty($data['user'])) {
            return ['status' => false, 'message' => 'User Details is Missing'];
        }
        if (empty($data['user']['employee_id'])) {
            return ['status' => false, 'message' => 'User Employee Id is Missing'];
        }

        foreach ($data['workflow_steps'] as $index => $step) {
            if (!isset($step['position'])) {
                return ['status' => false, 'message' => "Step $index is missing a position"];
            }
            if (!isset($step['step_user_role'])) {
                return ['status' => false, 'message' => "Step $index is missing a user role"];
            }
            if (!isset($step['requires_user_id'])) {
                return ['status' => false, 'message' => "Step $index is missing a requires user ID flag"];
            }
            if (!isset($step['is_user_id_dynamic'])) {
                return ['status' => false, 'message' => "Step $index is missing a dynamic user ID flag"];
            }
                  
        }

        return ['status' => true];
    }

    public static function normalize(array $data): array
    {
        // foreach ($data['workflow_steps'] as &$step) {
        //     $step['requiresUserId'] = filter_var($step['requiresUserId'], FILTER_VALIDATE_BOOLEAN);
        //     $step['isUserIdDynamic'] = filter_var($step['isUserIdDynamic'], FILTER_VALIDATE_BOOLEAN);
        // }
        return $data;
    }
}
