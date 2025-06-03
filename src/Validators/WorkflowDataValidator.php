<?php

namespace WorkflowManager\Validators;

class WorkflowDataValidator
{
        public static function validate(array $data): void
    {
        if (empty($data['workflowName'])) {
            throw new \InvalidArgumentException('Missing required workflow name ');
        }
        if (empty($data['workflow_steps'])) {
            throw new \InvalidArgumentException('Missing required workflow Steps ');
        }
        if (empty($data['user'])) {
            throw new \InvalidArgumentException('User Details is Missing');
        }
        if (empty($data['user']['employee_id'])) {
            throw new \InvalidArgumentException('User Employee Id is Missing');
        }

        foreach ($data['workflow_steps'] as $index => $step) {
            if (!isset($step['position'])) {
                throw new \InvalidArgumentException("Step $index is missing a position");
            }
            if (!isset($step['step_user_role'])) {
                throw new \InvalidArgumentException("Step $index is missing a user role");
            }
            if (!isset($step['requires_user_id'])) {
                throw new \InvalidArgumentException("Step $index is missing a requires user ID flag");
            }
            if (!isset($step['is_user_id_dynamic'])) {
                throw new \InvalidArgumentException("Step $index is missing a dynamic user ID flag");
            }
        }
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
