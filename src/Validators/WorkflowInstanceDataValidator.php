<?php

namespace WorkflowManager\Validators;

class WorkflowInstanceDataValidator
{
    public static function validate(array $data): array
    {
        if (empty($data['workflow_id']) ) {
            return ['status' => false, 'message' => 'Missing required workflow Id'];
        }

        if (empty($data['user']) ) {
            return ['status' => false, 'message' => 'Missing required User Details'];
        }

        if (empty($data['user']['employee_id']) ) {
            return ['status' => false, 'message' => 'Missing required User Employee Id'];
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
